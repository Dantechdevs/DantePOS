<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemPurchaseHistoryController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck = checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /*===================================================*/
    public function index()
    {
        $this->authenticateRole($module_page = 'reports');
        $products = Product::get();
        $suppliers = DB::table('suppliers')->get();
        return view('reports.purchase.items_history_report', compact('products','suppliers'));
    }
    /***************************************************************/
     public function getPurchaseItemsData(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $supplierId = $request->supplier_id;
            $productId = $request->product_id;
            $status = $request->status;

            // Get purchases with serialized items_addon data
            $purchases = DB::table('purchases as p')
                ->join('suppliers as s', 'p.supplier_id', '=', 's.id')
                ->select(
                    'p.id',
                    'p.purchase_no',
                    'p.date',
                    'p.supplier_id',
                    's.name as supplier_name',
                    'p.items_addon',
                    'p.status',
                    'p.total_qty',
                    'p.sub_total',
                    'p.other_charges',
                    'p.discount_type',
                    'p.discount',
                    'p.discount_amount'
                )
                ->whereBetween('p.date', [$startDate, $endDate]);

            if ($supplierId !== 'all') {
                $purchases->where('p.supplier_id', $supplierId);
            }

            if ($status !== 'all') {
                $purchases->where('p.status', $status);
            }

            $purchases = $purchases->orderBy('p.date', 'desc')
                                  ->get();

            $reportData = [];
            $processedItems = [];

            foreach ($purchases as $purchase) {
                // Unserialize the items_addon data
                $itemsAddon = unserialize($purchase->items_addon);

                if (is_array($itemsAddon)) {
                    foreach ($itemsAddon as $item) {
                        // Skip if product_id doesn't match filter
                        if ($productId !== 'all' && $item['product_id'] != $productId) {
                            continue;
                        }

                        $itemData = [
                            'purchase_id' => $purchase->id,
                            'purchase_no' => $purchase->purchase_no,
                            'purchase_date' => $purchase->date,
                            'supplier_name' => $purchase->supplier_name,
                            'product_name' => $item['productName'] ?? 'N/A',
                            'product_id' => $item['product_id'] ?? null,
                            'quantity' => $item['quantity'] ?? 0,
                            'unit' => $item['unit'] ?? 'N/A',
                            'unit_cost' => $item['price'] ?? 0,
                            'total_cost' => $item['amount'] ?? 0,
                            'discount_amount' => $this->calculateItemDiscount($purchase, $item),
                            'net_amount' => $this->calculateNetAmount($item, $purchase),
                            'status' => $purchase->status
                        ];

                        $reportData[] = $itemData;
                        $processedItems[] = $itemData;
                    }
                }
            }

            // Calculate summary
            $summary = $this->calculateSummary($processedItems);

            return response()->json([
                'success' => true,
                'data' => $reportData,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateItemDiscount($purchase, $item)
    {
        if ($purchase->discount_type === 'percentage' && $purchase->discount > 0) {
            $itemPercentage = ($item['amount'] / $purchase->sub_total) * 100;
            return ($itemPercentage * $purchase->discount_amount) / 100;
        } elseif ($purchase->discount_type === 'fixed' && $purchase->discount_amount > 0) {
            $itemPercentage = ($item['amount'] / $purchase->sub_total) * 100;
            return ($itemPercentage * $purchase->discount_amount) / 100;
        }

        return 0;
    }

    private function calculateNetAmount($item, $purchase)
    {
        $totalCost = $item['amount'] ?? 0;
        $discount = $this->calculateItemDiscount($purchase, $item);
        return $totalCost - $discount;
    }

    private function calculateSummary($data)
    {
        $totalQuantity = 0;
        $totalAmount = 0;
        $totalDiscount = 0;
        $netAmount = 0;

        foreach ($data as $item) {
            $totalQuantity += floatval($item['quantity']);
            $totalAmount += floatval($item['total_cost']);
            $totalDiscount += floatval($item['discount_amount']);
            $netAmount += floatval($item['net_amount']);
        }

        $averageCost = $totalQuantity > 0 ? $totalAmount / $totalQuantity : 0;

        return [
            'totalPurchases' => count(array_unique(array_column($data, 'purchase_id'))),
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'totalDiscount' => $totalDiscount,
            'netAmount' => $netAmount,
            'averageCost' => $averageCost
        ];
    }
}
