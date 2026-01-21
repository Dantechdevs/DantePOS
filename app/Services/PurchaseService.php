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
use Illuminate\Support\Facades\Log;

class PurchaseService
{
    /*********************************************************************/
    public function makeItemsArr($validatedData)
    {
        return collect($validatedData['product_id'])->map(function ($productId, $index) use ($validatedData) {
            $unit = Unit::find($validatedData['unit'][$index]);
            return [
                'productName' => $validatedData['productName'][$index],
                'product_id' => $productId,
                'unit_id' => $unit->id,
                'unit' => $unit->name,
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

        // Handle attachment
        $this->handleAttachment($purchase, $validatedData);
        // Assign the data to the purchase model
        $purchase->fill($validatedData);

        // Save the purchase (the trait handles the date and createdBy)
        $purchase->save();
        return $purchase;
    }

    /**
     * Handle attachment professionally
     */
    /**
 * Handle attachment professionally with error handling
 */
protected function handleAttachment(Purchase $purchase, array &$data): void
{
    // Check if remove_attachment checkbox is checked
    $removeAttachment = isset($data['remove_attachment']) && $data['remove_attachment'] == 'on';

    try {
        // If new file uploaded
        if (isset($data['attachment']) && $data['attachment'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['attachment'];

            // Delete old attachment if exists
            $this->deleteAttachmentFile($purchase->attachment);

            // Generate professional filename
            $filename = $this->generateAttachmentFilename($file, $purchase->id);

            // Ensure directory exists
            $storagePath = public_path('purchase_attachments');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            // Move file to public directory
            $file->move($storagePath, $filename);

            // Store only the filename in attachment column
            $data['attachment'] = $filename;
        }

        // If updating and no new file, keep existing attachment
        elseif ($purchase->exists && !isset($data['attachment'])) {
            $data['attachment'] = $purchase->attachment;
        }

        // Remove the remove_attachment checkbox value from data as it's not needed in database
        unset($data['remove_attachment']);

    } catch (\Exception $e) {
        // Log the error but don't stop the purchase process
        Log::error('Attachment handling error: ' . $e->getMessage());
        // Keep existing attachment if there's an error with new file
        if ($purchase->exists) {
            $data['attachment'] = $purchase->attachment;
        }
    }
}

/**
 * Delete attachment file safely
 */
protected function deleteAttachmentFile(?string $filename): void
{
    if ($filename) {
        $filePath = public_path('purchase_attachments/' . $filename);
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }
}

    /**
     * Generate professional filename
     */
    protected function generateAttachmentFilename($file, ?int $purchaseId = null): string
    {
        $timestamp = now()->format('Ymd_His');
        $extension = $file->getClientOriginalExtension();

        $prefix = $purchaseId ? "purchase_{$purchaseId}" : "purchase_new";

        return "{$prefix}_{$timestamp}.{$extension}";
    }
    /*********************************************************************/
    //     public function updateStock(array $productIds, array $quantities, array $unitIds, $operation = 'decrease')
    //     {
    //         $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

    //         foreach ($productIds as $index => $productId) {
    //             $product = $products->get($productId);

    //             if ($product) {
    //                 // Get unit info from product's unit_info column
    //                 $unitInfo = $product->unit_info;
    //                 $unit = collect($unitInfo)->firstWhere('unit_id', $unitIds[$index]);

    //                 if ($unit) {
    //                     // Convert the purchased quantity to grams
    //                     $quantityInGrams = $quantities[$index] * $unit['conversion'];

    //                     $newQuantity = $operation === 'decrease'
    //                         ? max(0, $product->quantity - $quantityInGrams)
    //                         : $product->quantity + $quantityInGrams;
    // echo "<pre>"; print_r($newQuantity); "</pre>"; exit;
    //                     $product->update(['quantity' => $newQuantity]);
    //                 }
    //             }
    //         }
    //     }
    public function updateStock(array $productIds, array $quantities, array $unitIds, $operation = 'decrease')
    {
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($productIds as $index => $productId) {
            $product = $products->get($productId);

            if ($product) {
                // Get unit info from product's unit_info column
                $unitInfo = $product->unit_info;
                $unit = collect($unitInfo)->firstWhere('unit_id', $unitIds[$index]);

                if ($unit) {
                    // Convert the purchased quantity to grams
                    $quantityInGrams = $quantities[$index] * $unit['conversion'];

                    if ($operation === 'decrease') {
                        // Prevent negative stock
                        // if ($product->quantity < $quantityInGrams) {
                        //     throw new \Exception("Insufficient stock for product ID: {$productId}");
                        // Or alternatively, you could set to 0:
                        // $newQuantity = 0;
                        // } else {
                        $newQuantity = $product->quantity - $quantityInGrams;
                        // }
                    } else {
                        $newQuantity = $product->quantity + $quantityInGrams;
                    }

                    $product->update(['quantity' => $newQuantity]);
                }
            }
        }
    }
    /*********************************************************************/
    public function stockOut(Purchase $purchase)
    {
        $items = unserialize($purchase->items_addon);

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if ($product && isset($item['unit_id'])) {
                // Get unit info from product's unit_info column
                $unitInfo = $product->unit_info;
                $unit = collect($unitInfo)->firstWhere('unit_id', $item['unit_id']);

                if ($unit) {
                    // Convert the quantity to grams using the unit conversion
                    $quantityInGrams = $item['quantity'] * $unit['conversion'];

                    // Decrease the stock (stock out)
                    $newQuantity = $product->quantity - $quantityInGrams;
                    // $newQuantity = max(0, $product->quantity - $quantityInGrams);
                    $product->update(['quantity' => $newQuantity]);
                }
            } elseif ($product) {
                // Fallback for items without unit info (simple quantity subtraction)
                // $newQuantity = max(0, $product->quantity - $item['quantity']);
                $newQuantity = $product->quantity - $item['quantity'];
                $product->update(['quantity' => $newQuantity]);
            }
        }
    }
    public function stockOutxxxx(Purchase $purchase)
    {
        $items = unserialize($purchase->items_addon);
        $unitIds = array_column($items, 'unit_id');
        $units = Unit::whereIn('id', $unitIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $unit = $units->get($item['unit_id']);

            if ($product && $unit) {
                $quantityInGrams = $item['quantity'] * $unit->conversion;
                $product->update(['quantity' => $product->quantity - $quantityInGrams]);
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
