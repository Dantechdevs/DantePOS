<?php

namespace App\Services;

use Session;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\SupplierPayment;
use App\Models\Unit;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


class PurchaseService
{

    /*********************************************************************/
    public function makeItemsArr($validatedData)
    {
        return collect($validatedData['product_id'])->map(function ($productId, $index) use ($validatedData) {
            return [
                'productName' => $validatedData['productName'][$index],
                'product_id' => $productId,
                'unit' => Unit::find($validatedData['unit'][$index])->name,
                'price' => $validatedData['price'][$index],
                'quantity' => $validatedData['quantity'][$index],
                'amount' => $validatedData['amount'][$index],
            ];
        })->toArray();
    }
    /*********************************************************************/
    public function savePurchase(array $validatedData, ?int $purchaseId = null)
    {
        $items = $this->makeItemsArr($validatedData);

        // Prepare additional fields
        $validatedData['items_addon'] = serialize($items);
        $validatedData['total_qty'] = array_sum(array_column($items, 'quantity'));

        $purchase = $purchaseId ? Purchase::findOrFail($purchaseId) : new Purchase;

        // Assign the data to the purchase model
        $purchase->fill($validatedData);

        // Save the purchase (the trait handles the date and createdBy)
        $purchase->save();
        return $purchase;
    }
    /*********************************************************************/
    public function updateStock(array $productIds, array $quantities, $operation = 'decrease')
    {

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        foreach ($productIds as $index => $productId) {
            $product = $products->get($productId);
            if ($product) {
                $newQuantity = $operation === 'decrease'
                    ? max(0, $product->quantity - ($quantities[$index] * $product->qtyPerUnit))
                    : $product->quantity + ($quantities[$index] * $product->qtyPerUnit);

                $product->update(['quantity' => $newQuantity]);
            }
        }
    }
    /*********************************************************************/
    public function stockOut(Purchase $purchase)
    {
        $items = unserialize($purchase->items_addon);

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->update(['quantity' => $product->quantity - ($item['quantity'] * $product->qtyPerUnit)]);
            }
        }
    }
    /*********************************************************************/

    public function findPurchaseInvoiceWithDetails($id)
    {
        $invoice = Purchase::with('supplier')->findOrFail($id);

        return [
            'purchaseInvoice' => $invoice,
            'purchaseitmesAddons' => unserialize($invoice->items_addon),
            'totalAmount' => $this->calculateTotalAmount($invoice->supplier_id),
            'supplierPayment' => $this->supplierPayment($invoice->supplier_id),
        ];
    }
    /*********************************************************************/
    public function calculateTotalAmount($supplier_id)
    {
        return Purchase::where('supplier_id', $supplier_id)
            ->where('status', 'received')
            ->sum('grand_total') ?? 0;
    }
    /*********************************************************************/
    public function supplierPayment($supplier_id)
    {
        return SupplierPayment::where('supplier_id', $supplier_id)
            ->sum('amount') ?? 0;
    }
}
