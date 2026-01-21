<?php

namespace App\Services;

use App\CustomerDiscount;
use App\CustomerPayment;
use App\Models\Customer;
use App\Models\CustomerOpeningBalance;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class SaleService
{
    public function makeItemsArr($validatedData)
    {
        return collect($validatedData['product_id'])->map(function ($productId, $index) use ($validatedData) {
            $unit = Unit::find($validatedData['unit'][$index]);
            return [
                'productName' => $validatedData['productName'][$index],
                'product_id' => $productId,
                'unit_id' => $unit->id,
                'unit' => $unit->name,
                'cost' => $validatedData['cost'][$index],
                'calculatedCost' => $validatedData['calculatedCost'][$index],
                'selling_price' => $validatedData['selling_price'][$index],
                'quantity' => $validatedData['quantity'][$index],
                'amount' => $validatedData['amount'][$index],
                // 'oldquanity' => $validatedData['productOldQty'][$index],
            ];
        })->toArray();
    }

    // public function saveSale(array $validatedData)
    // {
    //     $items = $this->makeItemsArr($validatedData);
    //     $validatedData['items_addon'] = serialize($items);
    //     $validatedData['total_qty'] = array_sum(array_column($items, 'quantity'));
    //     $validatedData['date'] = date('Y-m-d', strtotime($validatedData['date']));
    //     $validatedData['createdBy'] = Auth::id();

    //     return Sale::create($validatedData);
    // }

    public function saveSale(array $validatedData, ?int $saleId = null)
    {
        $items = $this->makeItemsArr($validatedData);
        // Prepare additional fields
        $validatedData['items_addon'] = serialize($items);
        $validatedData['total_qty'] = array_sum(array_column($items, 'quantity'));
        // $validatedData['payment_type'] = $validatedData['status'] == 1 ? $validatedData['payment_type'] : null;
        $paymentStatus = $validatedData['paid_amount'] <= 0 ? 'unpaid' : ($validatedData['paid_amount'] >= $validatedData['grand_total'] ? 'paid' : 'partial');
        $validatedData['paid_amount'] = $validatedData['paid_amount'] > $validatedData['grand_total'] ? $validatedData['grand_total'] : $validatedData['paid_amount'];
        $validatedData['change_amount'] = 0;

        // Check if updating or creating a new sale
        $validatedData['due_date'] = date('Y-m-d', strtotime($validatedData['due_date'])) > date('Y-m-d') ? date('Y-m-d', strtotime($validatedData['due_date'])) : null;
        $validatedData['payment_status'] = $paymentStatus;
        if ($saleId) {
            $sale = Sale::findOrFail($saleId);
            $sale->update($validatedData);
            return $sale; // Return the updated sale
        }

        return Sale::create($validatedData); // Create and return the new sale
    }


    // public function checkStock(array $validatedData)
    // {
    //     $products = Product::whereIn('id', $validatedData['product_id'])->get()->keyBy('id');
    //     // echo "<pre>"; print_r($products); exit;
    //     foreach ($validatedData['product_id'] as $index => $productId) {
    //         $product = $products->get($productId);
    //         if (!$product) {
    //             throw new \Exception("Product {$validatedData['productName'][$index]} not found.");
    //         }

    //         if ($product->quantity < $validatedData['quantity'][$index]) {
    //             throw new \Exception("{$validatedData['productName'][$index]} has only {$product->quantity} in stock!");
    //         }
    //     }
    // }

    public function checkStock(array $validatedData)
    {
        $products = Product::whereIn('id', $validatedData['product_id'])->get()->keyBy('id');
        $stockErrors = []; // Array to collect stock errors

        foreach ($validatedData['product_id'] as $index => $productId) {
            $product = $products->get($productId);

            if (!$product) {
                $stockErrors[] = "Product {$validatedData['productName'][$index]} not found.";
                continue; // Skip to the next product
            }

            if ($product->quantity < $validatedData['quantity'][$index]) {
                $stockErrors[] = "{$validatedData['productName'][$index]} has only {$product->quantity} in stock!";
            }
        }

        // If there are errors, throw an exception with all errors
        if (!empty($stockErrors)) {
            throw new \Exception(implode('<br>', $stockErrors)); // Use <br> for multi-line error display
        }
    }


    public function updateStock(array $productIds, array $quantities, $operation = 'decrease')
    {
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        foreach ($productIds as $index => $productId) {
            $product = $products->get($productId);
            if ($product) {
                $newQuantity = $operation === 'decrease'
                    ? max(0, $product->quantity - $quantities[$index])
                    : $product->quantity + $quantities[$index];

                $product->update(['quantity' => $newQuantity]);
            }
        }
    }

    public function restoreStockxxx(Sale $sale)
    {
        $items = unserialize($sale->items_addon);

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->update(['quantity' => $product->quantity + $item['quantity']]);
            }
        }
    }
    public function restoreStock(Sale $sale)
    {
        $items = unserialize($sale->items_addon);

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if ($product && isset($item['unit_id'])) {
                // Get unit info from product's unit_info column
                $unitInfo = $product->unit_info;
                $unit = collect($unitInfo)->firstWhere('unit_id', $item['unit_id']);

                if ($unit) {
                    // Convert the quantity to grams using the unit conversion
                    $quantityInGrams = $item['quantity'] * $unit['conversion'];

                    // Increase the stock (restore)
                    $product->update(['quantity' => $product->quantity + $quantityInGrams]);
                }
            } elseif ($product) {
                // Fallback for items without unit info (simple quantity addition)
                $product->update(['quantity' => $product->quantity + $item['quantity']]);
            }
        }
    }
    /*********************************************************************/

    public function findInvoiceWithDetails($id)
    {
        $saleInvoice = Sale::with('customer')->findOrFail($id);

        return [
            'saleInvoice' => $saleInvoice,
            'saleitmesAddons' => unserialize($saleInvoice->items_addon),
            'totalAmount' => $this->calculateTotalAmount($saleInvoice->customer_id),
            'debitAmount' => $this->calculateCustomerBalance($saleInvoice->customer_id, 'debit'),
            'creditAmount' => $this->calculateCustomerBalance($saleInvoice->customer_id, 'credit'),
            'openingBalance' => Customer::where('id', $saleInvoice->customer_id)
                ->value('opening_balance') ?? 0,
            'paidAmount' => $this->invoicePaidAmount($saleInvoice->customer_id),
            'partialPaidAmount' => $this->partialPaidAmount($saleInvoice->customer_id),
        ];
    }
    /*********************************************************************/
    public function calculateCustomerBalance($customer_id, $type)
    {
        return CustomerOpeningBalance::where('customer_id', $customer_id)
            ->where('type', $type)
            ->sum('amount') ?? 0;
    }
    /*********************************************************************/
    public function customerTotalBalance($customer_id)
    {
        $totalAmount = $this->calculateTotalAmount($customer_id);
        $openingBalance = Customer::where('id', $customer_id)
            ->value('opening_balance') ?? 0;

        $partialOP_Balance = $this->partialPaidCustomerOP_Balance($customer_id);


        $paidAmount = $this->invoicePaidAmount($customer_id);

        $partialPaidAmount = intval($this->partialPaidAmount($customer_id));
        //  dd($paidAmount);
        $updatedPaidAmount = $paidAmount > $totalAmount ? $totalAmount : $paidAmount;

        $customerDiscountedAmount = CustomerDiscount::where('customer_id', $customer_id)
            ->sum('discount_amount') ?? 0;
        // Apply discount to total amount
        $discountedTotalAmount = $totalAmount - $customerDiscountedAmount;
        // dd($openingBalance);
        // $totalbalance =  ($totalAmount + $openingBalance) - ($updatedPaidAmount + $partialPaidAmount + $partialOP_Balance);
        //  dd($totalbalance);

        $totalbalance = ($discountedTotalAmount + $openingBalance) - ($updatedPaidAmount + $partialPaidAmount + $partialOP_Balance);
        return round($totalbalance);
        // (1266.64 + 1000) - (1500 + 100 + 0)
    }
    /*********************************************************************/
    public function calculateTotalAmount($customer_id)
    {
        return Sale::where('customer_id', $customer_id)
            ->where('status', 1)
            // ->where('payment_type', 'credit')
            ->sum('grand_total') ?? 0;
    }
    /*********************************************************************/
    public function partialPaidCustomerOP_Balance($customer_id)
    {
        return CustomerOpeningBalance::where('customer_id', $customer_id)
            ->sum('amount') ?? 0;
    }
    /*********************************************************************/
    public function invoicePaidAmount($customer_id)
    {
        return Sale::where('customer_id', $customer_id)
            ->where('status', 1)
            ->sum('paid_amount') ?? 0;

        //         $sale = Sale::where('customer_id', $customer_id)
        //     ->where('status', 1)
        //     ->selectRaw('COALESCE(SUM(paid_amount), 0) as total_paid, COALESCE(SUM(change_amount), 0) as total_change')
        //     ->first();

        // return ($sale->total_paid ?? 0) - ($sale->total_change ?? 0);
    }
    /*********************************************************************/
    public function partialPaidAmount($customer_id)
    {
        // Get only the sale IDs for the customer
        $saleIds = Sale::where('customer_id', $customer_id)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        // Return sum of payments for these sale IDs
        return CustomerPayment::whereIn('sale_id', $saleIds)
            ->sum('amount') ?? 0;
    }











    /*********************customer balance excluded current invoice start*************************/
    public function customerTotalBalanceExcludedInvoice($customer_id, $saleId)
    {
        $sale = Sale::find($saleId);
        if (!$sale) {
            return 0;
        }

        // Get initial balance
        $initialBalance = Customer::where('id', $customer_id)
            ->value('opening_balance') ?? 0;
        // dd($initialBalance);
        // Get total sales amount excluding current invoice
        $totalSales = Sale::where('customer_id', $customer_id)
            ->where('id', '!=', $saleId)
            ->where('status', 1)
            ->sum('grand_total') ?? 0;

        // Get total payments made excluding payments for current invoice
        $totalPayments = $this->getTotalPaymentsExcludedInvoice($customer_id, $saleId);

        // Calculate balance: initial + sales - payments
        $balance = ($initialBalance + $totalSales) - $totalPayments;

        return round($balance);
    }

    /**
     * Get total payments excluding those related to a specific invoice
     */
    private function getTotalPaymentsExcludedInvoice($customer_id, $excludedSaleId)
    {
        // Get payments from sales (paid_amount) excluding the specified sale
        $salesPayments = Sale::where('customer_id', $customer_id)
            ->where('id', '!=', $excludedSaleId)
            ->where('status', 1)
            ->sum('paid_amount') ?? 0;

        // Get partial payments excluding those for the specified sale using join
        $partialPayments = CustomerPayment::join('sales', 'customer_payments.sale_id', '=', 'sales.id')
            ->where('sales.customer_id', $customer_id)
            ->where('sales.id', '!=', $excludedSaleId)
            ->where('sales.status', 1)
            ->sum('customer_payments.amount') ?? 0;

        return $salesPayments + $partialPayments;
    }

    /*********************************************************************/
    public function calculateTotalAmountBySaleId($saleId)
    {
        return Sale::where('id', '!=', $saleId)
            ->where('status', 1)
            // ->where('payment_type', 'credit')
            ->sum('grand_total') ?? 0;
    }

    /*********************************************************************/
    public function partialPaidByExcludedSaleId($saleId)
    {
        // Return sum of payments for these sale IDs
        return CustomerPayment::where('sale_id', '!=', $saleId)
            ->sum('amount') ?? 0;
    }
    /*********************************************************************/
    public function invoicePaidByExcludedSaleId($saleId)
    {
        // dd($saleId);
        return Sale::where('id', '!=', $saleId)
            ->where('status', 1)
            ->sum('paid_amount') ?? 0;
    }
    /*********************customer balance excluded current invoice end*************************/

    /*********************************************************************/
    public function partialPaidBySaleId($saleId)
    {
        // Return sum of payments for these sale IDs
        return CustomerPayment::where('sale_id', $saleId)
            ->sum('amount') ?? 0;
    }
    /*********************************************************************/
    public function invoicePaidBySaleId($saleId)
    {
        // dd($saleId);
        return Sale::where('id', $saleId)
            // ->where('status', 1)
            ->sum('paid_amount') ?? 0;
    }


    public function invoicePaymentHistory($invoice)
    {
        $invoicePayments = CustomerPayment::select('id', 'sale_id', 'payment_date', 'amount', 'notes')->where('sale_id', $invoice->id)->get();

        // Prepare merged payments array
        $mergedPayments = [];

        // Add the main invoice payment if paid_amount > 0
        if ($invoice->paid_amount > 0) {
            $mergedPayments[] = [
                'id' => null, // No ID for the main invoice payment
                'sale_id' => $invoice->id,
                'payment_date' => $invoice->date, // Using invoice date
                'amount' => $invoice->paid_amount,
                'notes' => 'Initial Payment' // Optional: Add a note for clarity
            ];
        }

        // Add additional payments from CustomerPayment
        foreach ($invoicePayments as $payment) {
            $mergedPayments[] = [
                'id' => $payment->id,
                'sale_id' => $payment->sale_id,
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'notes' => $payment->notes ?? null // Include notes if available
            ];
        }

        // Sort payments by date (newest first)
        usort($mergedPayments, function ($a, $b) {
            return strtotime($b['payment_date']) - strtotime($a['payment_date']);
        });

        return $mergedPayments;
    }
}
