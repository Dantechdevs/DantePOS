<?php

namespace App\Imports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;
use App\Models\Customer;
use App\Models\Area;
use App\Models\Product;
use App\Rules\ExistsInProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BulkSaleImport implements
    ToCollection,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    SkipsOnFailure,
    WithChunkReading
{
    use Importable, SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            $this->validateStockLevels($rows);
            // Validate and process each row
            foreach ($rows as $index => $row) {
                $this->validateRow($row, $index);
                // Validate stock levels for all rows

                $this->processRow($row, $index);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateStockLevels($rows)
    {
        $totalQuantities = [];

        // Start a database transaction to ensure consistency
        DB::beginTransaction();

        try {
            // First pass: accumulate quantities for each SKU across all rows
            foreach ($rows as $index => $row) {
                // Get the product quantities from the row
                $items = explode(',', $row['sku'] ?? '');
                $quantities = explode(',', $row['quantity'] ?? '');

                // Ensure SKU and Quantity lists have the same length
                if (count($items) !== count($quantities)) {
                    throw new \Exception("Row: " . ($index + 2) . " - SKU and Quantity lists do not match.");
                }

                // Update the global quantity tracker for each product
                foreach ($items as $i => $itemCode) {
                    $itemCode = trim($itemCode); // Trim SKU
                    $quantity = (int) trim($quantities[$i]); // Get corresponding quantity

                    // Initialize the global quantity tracker for each product if not already set
                    if (!isset($totalQuantities[$itemCode])) {
                        $totalQuantities[$itemCode] = 0;
                    }

                    // Increment the global quantity tracker
                    $totalQuantities[$itemCode] += $quantity;
                }
            }

            // Fetch all products with row-level locking to prevent changes during validation
            $products = Product::whereIn('product_code', array_keys($totalQuantities))
                ->lockForUpdate() // Prevents other processes from modifying these rows
                ->get()
                ->keyBy('product_code');

            // Log the fetched stock levels for debugging
            $originalStockLevels = [];
            foreach ($products as $productCode => $product) {
                $originalStockLevels[$productCode] = $product->quantity; // Store original stock
                Log::info("Fetched Product: {$productCode}, Stock: {$product->quantity}");
            }

            // Second pass: validate stock levels for each SKU
            foreach ($totalQuantities as $itemCode => $uploadedQuantity) {
                // Check if SKU exists
                if (!isset($originalStockLevels[$itemCode])) {
                    $availableSkus = implode('| ', array_keys($originalStockLevels));
                    throw new \Exception("SKU '{$itemCode}' does not exist. Available SKUs: {$availableSkus}");
                }

                // Get the original stock quantity from the copy
                $originalStock = $originalStockLevels[$itemCode];

                // Log quantities for debugging
                Log::info("Validating Product: {$itemCode}, Uploaded Quantity: {$uploadedQuantity} | Original Stock: {$originalStock}");

                // Check against the stock and include row information in the error message
                if ($originalStock < $uploadedQuantity) {
                    throw new \Exception("You are uploading total quantities: {$uploadedQuantity} of this SKU '{$products[$itemCode]->product_code}'. But stock is: {$originalStock}.");
                }
            }

            // If validation passes, commit the transaction
            DB::commit();
            Log::info("Stock validation passed successfully for all SKUs.");
        } catch (\Exception $e) {
            DB::rollBack(); // Revert changes if validation fails
            throw $e;
        }
    }

    private function validateRow($row, $index)
    {
        $validator = Validator::make($row->toArray(), $this->rules(), $this->customValidationMessages());
        if ($validator->fails()) {
            throw new ValidationException($validator, null, $validator->errors()->all());
        }

        // Custom validation for payment_type
        if ($row['status'] == 'billed' && !in_array($row['payment_type'], ['cash', 'credit'])) {
            throw new \Exception('Payment type must be either cash or credit when status is billed.');
        }
    }

    private function processRow($row, $index)
    {

        $mobile = $row['customer_mobile'] ?? null;
        $mobile = '0'. $mobile;

        $customerName = $row['customer_name'] ?? null;
        $customerAddress = $row['customer_address'] ?? null;
        $customerArea = $row['customer_area'] ?? 'Pakistan';
        $items = explode(',', $row['sku'] ?? '');
        $quantities = explode(',', $row['quantity'] ?? '');
        $prices = explode(',', $row['price'] ?? '');
        $discountType = strtolower($row['discount_type'] ?? '');
        $discount = $row['discount'] ?? 0;
        $statusRow = $row['status'] ?? null;
        $payment_type = $statusRow == 'billed' ? strtolower($row['payment_type']) : null;
        $otherCharges = $row['other_charges'] ?? 0;
        $status = $statusRow == 'billed' ? 1 : ($statusRow == 'pending' ? 2 : 0);
        $date = $row['date'] ?? date('Y-m-d');

        // Validate items, quantities, and prices count
        if (count($items) !== count($quantities) || count($items) !== count($prices)) {
            throw new \Exception('Mismatch in counts of sku | quantities and prices.');
        }

        // Find or create the area
        $area = Area::firstOrCreate(['name' => $customerArea]);

        // Find or create customer
        $customer = Customer::firstOrCreate(
            ['mobile' => $mobile],
            ['name' => $customerName, 'area_id' => $area->id, 'address' => $customerAddress]
        );

        // Fetch product details
        $products = Product::whereIn('product_code', $items)->with('units:id,name')->get()->keyBy('product_code');
        $saleItems = $this->prepareSaleItems($items, $quantities, $prices, $products, $index, $statusRow);

        if (empty($saleItems)) {
            return;
        }

        // Calculate totals
        $subtotal = array_sum(array_column($saleItems, 'amount'));
        $subtotalPlusOtherCharges = $subtotal + $otherCharges;
        $discountAmount = $this->calculateDiscount($discountType, $discount, $subtotalPlusOtherCharges);
        $grandTotal = ($subtotalPlusOtherCharges - $discountAmount);

        // Create sale
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


    private function calculateDiscount($discountType, $discount, $subtotal)
    {
        return match ($discountType) {
            'percentage' => ($subtotal * $discount) / 100,
            'fixed' => $discount,
            default => 0,
        };
    }

    private function prepareSaleItems($items, $quantities, $prices, $products, $index, $statusRow)
    {
        $saleItems = [];
        foreach ($items as $i => $itemCode) {
            $product = $products[$itemCode] ?? null;

            if (!$product) {
                $this->logError($index, "Product with SKU {$itemCode} not found.");
                continue;
            }

            $quantity = $quantities[$i];
            $price = $prices[$i];

            if ($product->quantity < $quantity) {
                $this->logError($index, "Insufficient stock for product '{$product->name}'.");
                continue;
            }

            $amount = $price * $quantity;
            $saleItems[] = [
                'productName' => "{$product->product_code} - {$product->name}",
                'product_id' => $product->id,
                'unit' => $product->units->name,
                'cost' => $product->item_cost,
                'calculatedCost' => $product->item_cost * $quantity,
                'selling_price' => $price,
                'quantity' => $quantity,
                'amount' => $amount,
                'oldquanity' => $product->quantity,
            ];

            if ($statusRow === 'billed') {
                $product->update(['quantity' => $product->quantity - $quantity]);
            }
        }
        return $saleItems;
    }

    private function logError($rowIndex, $error)
    {
        $this->errors[] = [
            'row' => $rowIndex + 2,
            'error' => $error,
        ];
    }

    public function rules(): array
    {
        return [
            'customer_mobile' => 'required',
            'customer_name' => 'required|max:255',
            'customer_address' => 'nullable|max:255',
            'customer_area' => 'nullable|max:255',
            'sku' => function ($attribute, $value, $onFailure) {
                // Convert SKU string into an array
                $skus = explode(',', $value);

                // Count matching SKUs in the database
                $existingSKUs = Product::whereIn('product_code', $skus)->pluck('product_code')->toArray();

                // Check if there are any SKUs that don't exist in the database
                $missingSKUs = array_diff($skus, $existingSKUs);

                if (!empty($missingSKUs)) {
                    $onFailure("The following SKUs are invalid: " . implode(', ', $missingSKUs));
                }
            },
            'quantity' => 'required',
            'price' => 'required',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'status' => 'required|in:billed,pending',
            'payment_type' => 'string|required_if:status,billed|in:cash,credit',
            'date' => 'nullable|date_format:d-m-Y',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'customer_mobile.required' => 'Customer mobile number is required.',
            'customer_name.required' => 'Customer name is required.',
            'sku.required' => 'Product SKU is required.',
            'quantity.required' => 'Quantity is required.',
            'price.required' => 'Price is required.',
            'discount_type.in' => 'Discount type must be either percentage or fixed.',
            'discount.numeric' => 'Discount must be a valid number.',
            'discount.min' => 'Discount must be at least 0.',
            'other_charges.numeric' => 'Other charges must be a valid number.',
            'other_charges.min' => 'Other charges must be at least 0.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either billed or pending.',
            'payment_type.required_if' => 'Payment type is required when status is billed.',
            'payment_type.in' => 'Payment type must be either cash or credit.',
            'date.date_format' => 'Date format must be in d-m-Y.',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onError(Throwable $error) {}
}
