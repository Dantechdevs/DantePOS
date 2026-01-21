<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Area;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BulkSalesImport implements ToCollection, WithHeadingRow
{
    public $errors = []; // Collect errors for validation and processing issues

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {

                $rowNumber = $index + 2; // Excel row number (1-based index + header row)

                // Validate basic required fields
                $validator = Validator::make($row->toArray(), [
                    'customer_mobile' => 'required',
                    'customer_name' => 'required|max:255',
                    'customer_address' => 'nullable|max:255',
                    'customer_area' => 'nullable|max:255',
                    'sku' => 'required', // Comma-separated product codes
                    'quantity' => 'required', // Comma-separated quantities
                    'price' => 'required', // Comma-separated prices
                    'discount_type' => 'nullable|in:percentage,fixed',
                    'discount' => 'nullable|numeric|min:0',
                    'other_charges' => 'nullable|numeric|min:0',
                    'status' => 'required|in:billed,pending',
                    'payment_type' => 'required_if:status,billed|in:cash,credit',
                    'date' => 'nullable|date_format:d-m-Y',
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        $this->errors[] = [
                            'row' => $rowNumber,
                            'error' => $error,
                        ];
                    }
                    continue;
                }

                // echo "<pre>"; print_r($row); exit;
                $mobile = $row['customer_mobile'] ?? null;
                $customerName = $row['customer_name'] ?? null;
                $customerAddress = $row['customer_address'] ?? null;
                $customerArea = $row['customer_area'] ?? 'Dhaka';
                $items = explode(',', $row['sku'] ?? '');
                $quantities = explode(',', $row['quantity'] ?? '');
                $prices = explode(',', $row['price'] ?? '');
                $discountType = strtolower($row['discount_type'] ?? '');
                $discount = $row['discount'] ?? 0;
                $statusRow = $row['status'] ?? null;
                $payment_type = $statusRow == 'billed'  ? strtolower($row['payment_type'])  : null;

                $otherCharges = $row['other_charges'] ?? 0;
                $status = $statusRow == 'billed' ? 1 : ($statusRow == 'pending' ? 2 : 0);
                $date = $row['date'] ?? date('Y-m-d');

                // dd($saleDate);

                // Validate items, quantities, and prices count
                if (count($items) !== count($quantities) || count($items) !== count($prices)) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => 'Mismatch in counts of sku, quantities, and prices.',
                    ];
                    continue;
                }

                // Find or create the area
                $area = Area::firstOrCreate(['name' => $customerArea]);

                // Find or create customer
                $customer = Customer::firstOrCreate(
                    ['mobile' => $mobile],
                    ['name' => $customerName, 'area_id' => $area->id, 'address' => $customerAddress]
                );

                // Process items
                $saleItems = [];
                $subtotal = 0;

                foreach ($items as $i => $itemCode) {
                    $product = Product::with(['units:id,name'])->where('product_code', (int)$itemCode)->first();

                    if (!$product) {
                        $this->errors[] = [
                            'row' => $index + 2,
                            'item_code' => $itemCode,
                            'error' => 'Product not found.',
                        ];
                        continue;
                    }

                    $quantity = $quantities[$i];
                    $price = $prices[$i];

                    // Check stock availability
                    if ($product->quantity < $quantity) {
                        $this->errors[] = [
                            'row' => $index + 2,
                            'item_code' => $itemCode,
                            'error' => "Insufficient stock for product '{$product->name}'.",
                        ];
                        continue;
                    }

                    $amount = $price * $quantity;
                    $subtotal += $amount;

                    $saleItems[] = [
                        'productName' => $product->product_code . ' - ' . $product->name,
                        'product_id' => $product->id,
                        'unit' => $product->units->name,
                        'cost' => $product->item_cost,
                        'calculatedCost' => $product->item_cost * $quantity,
                        'selling_price' => $price,
                        'quantity' => $quantity,
                        'amount' => $amount,
                        'oldquanity' => $product->quantity,
                    ];
                    if ($statusRow == 'billed') {
                        // Deduct stock
                        $product->decrement('quantity', $quantity);
                    }
                }

                // Calculate discount
                $discountAmount = 0;
                if ($discountType === 'percentage') {
                    $discountAmount = ($subtotal * $discount) / 100;
                } elseif ($discountType === 'fixed') {
                    $discountAmount = $discount;
                }

                // Calculate final total
                $totalAfterDiscount = $subtotal - $discountAmount;
                $grandTotal = $totalAfterDiscount + $otherCharges;

                // // Generate invoice_no
                // $lastInvoice = Sale::orderBy('id', 'desc')->first();
                // $invoiceNo = $lastInvoice ? $lastInvoice->invoice_no + 1 : 1;

                // Create sale
                if (!empty($saleItems)) {
                    Sale::create([
                        'date' => $date,
                        'sale_type' => 'bulk',
                        'customer_id' => $customer->id,
                        'area_id' => $customer->area_id,
                        'items_addon' => serialize($saleItems),
                        'total_qty' => array_sum(array_column($saleItems, 'quantity')),
                        'sub_total' => $subtotal,
                        'discount_type' => $discountType,
                        'discount' => $discount,
                        'discount_amount' => $discountAmount,
                        'other_charges' => $otherCharges,
                        'grand_total' => $grandTotal,
                        'status' => $status,
                        'payment_type' => $payment_type,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
