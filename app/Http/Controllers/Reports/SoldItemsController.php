<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use App\SiteSetting;
use App\User;
use PDF;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SoldItemsController extends Controller
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
        Session::put('page', 'soldItems');
        $getProducts = Product::get();
        return view('reports.sold_items.index', compact('getProducts'));
    }
    /*===================================================================*/
    public function getSoldProducts(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $data = $request->all();
        $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
        $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

        // Get currency setting once
        $currency = optional(SiteSetting::where('key', 'currency')->first())->value ?? '';

        // Base query with only needed columns
        $query = Sale::with(['customers' => function ($q) {
            $q->select('id', 'name');
        }])->where('status', 1)
            ->whereBetween('date', [$startDate, $endDate]);

        // Common HTML headers
        $html = [
            'thsource' => '<th>#</th>
                      <th>Invoice#</th>
                      <th>Date</th>
                      <th>Customer</th>
                      <th>Product Name</th>
                      <th>Unit</th>
                      <th>Quantity</th>
                      <th>Unit Price</th>
                      <th>Amount</th>',
            'tdsource' => '',
            'tfootsource' => ''
        ];

        if ($data['product_id'] == 'all') {
            // For all products - simplified approach without unit breakdown
            $sales = $query->get(['id', 'invoice_no', 'date', 'customer_id', 'items_addon']);

            $totalAmount = 0;
            $counter = 0;
            $productCache = [];

            foreach ($sales as $sale) {
                $variations = unserialize($sale->items_addon);

                foreach ($variations as $variation) {
                    // Cache product data to avoid repeated queries
                    if (!isset($productCache[$variation['product_id']])) {
                        $productCache[$variation['product_id']] = Product::select('id', 'product_code')
                            ->find($variation['product_id']);
                    }
                    $product = $productCache[$variation['product_id']];

                    $totalAmount += $variation['amount'];
                    $counter++;

                    $html['tdsource'] .= sprintf(
                        '<tr>
                        <td>%d</td>
                        <td><a target="_blank" href="%s">%s</a></td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s - %s</td>
                        <td>%s</td>
                        <td style="text-align: center;">%d</td>
                        <td style="text-align: right;">%s</td>
                        <td style="text-align: right;">%.2f</td>
                    </tr>',
                        $counter,
                        url("edit-sale/{$sale->id}"),
                        $sale->invoice_no,
                        date('d M Y', strtotime($sale->date)),
                        $sale->customers->name,
                        $product->product_code ?? '',
                        $variation['productName'],
                        $variation['unit'] ?? 'Unit',
                        $variation['quantity'],
                        number_format($variation['selling_price'], 2),
                        $variation['amount']
                    );
                }
            }

            // Simplified footer without unit breakdown for "all products"
            $html['tfootsource'] = sprintf(
                '<tr>
                <td colspan="8" style="background: gray; font-weight: bold; color:white;">Total</td>
                <td style="text-align: right; background: gray; font-weight: bold; color:white;">%s %.2f</td>
            </tr>',
                $currency,
                $totalAmount
            );
        } else {
            // For specific product with unit conversion
            $product = Product::select('id', 'product_code', 'unit_id', 'unit_info')
                ->find($data['product_id']);

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            // Get all available units for this product from unit_info
            $productUnits = $this->getProductUnitsFromInfo($product);

            // If no units found in unit_info, create a default unit
            if (empty($productUnits)) {
                $defaultUnit = [
                    'name' => 'Unit',
                    'conversion' => 1,
                    'is_default' => true
                ];
                $productUnits[] = $defaultUnit;
            }

            $sales = $query->get(['id', 'invoice_no', 'date', 'customer_id', 'items_addon']);

            $totalAmount = 0;
            $totalBaseUnits = 0;
            $counter = 0;
            $unitTotals = [];

            foreach ($sales as $sale) {
                $variations = unserialize($sale->items_addon);

                foreach ($variations as $variation) {
                    if ($variation['product_id'] == $data['product_id']) {
                        $unitId = $variation['unit_id'] ?? $product->unit_id ?? 0;
                        $unitInfo = $productUnits[$unitId] ?? current($productUnits);

                        // Convert to base units
                        $baseUnits = $variation['quantity'] * $unitInfo['conversion'];
                        $totalBaseUnits += $baseUnits;

                        // Track by unit
                        if (!isset($unitTotals[$unitId])) {
                            $unitTotals[$unitId] = [
                                'name' => $unitInfo['name'],
                                'quantity' => 0,
                                'amount' => 0
                            ];
                        }
                        $unitTotals[$unitId]['quantity'] += $variation['quantity'];
                        $unitTotals[$unitId]['amount'] += $variation['amount'];

                        $totalAmount += $variation['amount'];
                        $counter++;

                        $html['tdsource'] .= sprintf(
                            '<tr>
                            <td>%d</td>
                            <td><a target="_blank" href="%s">%s</a></td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s - %s</td>
                            <td>%s</td>
                            <td style="text-align: center;">%d</td>
                            <td style="text-align: right;">%s</td>
                            <td style="text-align: right;">%.2f</td>
                        </tr>',
                            $counter,
                            url("edit-sale/{$sale->id}"),
                            $sale->invoice_no,
                            date('d M Y', strtotime($sale->date)),
                            $sale->customers->name,
                            $product->product_code,
                            $variation['productName'],
                            $unitInfo['name'],
                            $variation['quantity'],
                            number_format($variation['selling_price'], 2),
                            $variation['amount']
                        );
                    }
                }
            }

            // Calculate unit breakdown from total base units
            $convertedBreakdown = $this->calculateUnitBreakdown($totalBaseUnits, $productUnits);

            // Generate footer with unit breakdown
            $footerUnits = '';
            foreach ($convertedBreakdown as $unit) {
                $footerUnits .= sprintf('%d %s, ', $unit['quantity'], $unit['name']);
            }

            $html['tfootsource'] = sprintf(
                '<tr>
                <td colspan="7" style="background: gray; font-weight: bold; color:white;">Total (%s)</td>
                <td colspan="2" style="text-align: right; background: gray; font-weight: bold; color:white;">%s %.2f</td>
            </tr>',
                rtrim($footerUnits, ', '),
                $currency,
                $totalAmount
            );
        }

        return response()->json($html);
    }

    /**
     * Get product units from unit_info JSON column
     */
    private function getProductUnitsFromInfo($product)
    {
        $productUnits = [];

        if (!empty($product->unit_info)) {
            try {
                // Handle both array and JSON string formats
                $unitInfo = is_array($product->unit_info) ? $product->unit_info : $product->unit_info;

                if (is_array($unitInfo)) {
                    foreach ($unitInfo as $unit) {
                        if (isset($unit['unit_id'], $unit['unit'], $unit['conversion'])) {
                            $productUnits[$unit['unit_id']] = [
                                'name' => $unit['unit'],
                                'conversion' => (float)$unit['conversion'],
                                'is_default' => $unit['is_default'] ?? false,
                                'purchase_price' => $unit['purchase_price'] ?? null,
                                'selling_price' => $unit['selling_price'] ?? null
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Invalid unit_info for product {$product->id}: " . $e->getMessage());
            }
        }

        return $productUnits;
    }

    /**
     * Calculate unit breakdown from total base units
     */
    private function calculateUnitBreakdown($totalBaseUnits, $productUnits)
    {
        if (empty($productUnits)) {
            return [['name' => 'Unit', 'quantity' => $totalBaseUnits, 'total_base' => $totalBaseUnits]];
        }

        // Sort units by conversion factor (highest first)
        uasort($productUnits, function ($a, $b) {
            return $b['conversion'] <=> $a['conversion'];
        });

        $remaining = $totalBaseUnits;
        $breakdown = [];

        foreach ($productUnits as $unitId => $unit) {
            if ($remaining >= $unit['conversion']) {
                $count = floor($remaining / $unit['conversion']);
                $breakdown[] = [
                    'name' => $unit['name'],
                    'quantity' => $count,
                    'total_base' => $count * $unit['conversion']
                ];
                $remaining -= $count * $unit['conversion'];
            }
        }

        // Add remaining as the smallest unit
        if ($remaining > 0) {
            $smallestUnit = end($productUnits);
            $breakdown[] = [
                'name' => $smallestUnit['name'],
                'quantity' => $remaining,
                'total_base' => $remaining
            ];
        }

        return $breakdown;
    }
    public function getSoldProductsxxxx(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));
            $settings = SiteSetting::pluck('value', 'key');
            // echo "<pre>"; print_r($data); exit();
            if ($data['product_id'] == 'all') {
                $sales = Sale::with('customers')->where('status', 1)->whereBetween('date', [$startDate, $endDate])->get()->toArray();

                // echo "<pre>"; print_r($sales); exit();
                $html['thsource'] = '<th>#</th>';
                $html['thsource'] .= '<th>Invoice#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Customer</th>';
                $html['thsource'] .= '<th>Product Name</th>';
                $html['thsource'] .= '<th>Selling Price</th>';
                $html['thsource'] .= '<th>Item Sales Count</th>';
                $html['thsource'] .= '<th>Sales Amount</th>';

                $html['tdsource'] = null;
                $totalAmount = 0;
                $returnTotalAmount = 0;
                $counter = 0;
                foreach ($sales as $productKey => $sale) {
                    $saleID = $sale['id'];
                    $invoice_no = $sale['invoice_no'];
                    $customerName = $sale['customers']['name'];
                    $date = date('d M Y', strtotime($sale['date']));
                    $variations = unserialize($sale['items_addon']);

                    foreach ($variations as $key => $variation) {
                        $product_id = $variation['product_id'];
                        $productName = $variation['productName'];
                        $unit = $variation['unit'];
                        $selling_price = $variation['selling_price'];
                        $quantity = $variation['quantity'];
                        $amount = $variation['amount'];
                        $singleProduct = Product::select('id', 'qtyPerUnit', 'product_code')->find($product_id);
                        $totalPiecesSold = $variation['quantity']; // Number of pieces sold
                        $piecesPerBox = $singleProduct['qtyPerUnit']; // Number of pieces per box

                        // Call the helper function to calculate sold boxes and remaining pieces
                        $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining($totalPiecesSold, $piecesPerBox);

                        $totalAmount = $variation['amount'] + $totalAmount;

                        $soldValue = $quantity * $selling_price;
                        $counter = $counter + 1;

                        $html['tdsource'] .= '<tr><td>' . $counter . '</td>';
                        $html['tdsource'] .= '<td><a target="_blank" href="' . url("edit-sale") . '/' . $sale['id'] . '">' . $invoice_no . '</a></td>';
                        $html['tdsource'] .= '<td>' . $date . '</td>';
                        $html['tdsource'] .= '<td>' . $customerName . '</td>';
                        $html['tdsource'] .= '<td>' . $singleProduct->product_code . ' - ' . $productName . '</td>';
                        $html['tdsource'] .= '<td style="text-align: right;">' . $selling_price . '</td>';
                        $html['tdsource'] .= '<td style="text-align: center;">' . $quantity . ' ' . $unit . ' pieces</td>';
                        $html['tdsource'] .= '<td style="text-align: right;">' . $soldValue . '</td></tr>';
                    }
                    $returnTotalAmount = $totalAmount;
                }
                $html['tfootsource'] = '<tr><td colspan="7" style="background: gray; font-weight: bold; color:white;">Total</td><td style="text-align: right; background: gray; font-weight: bold; color:white;">' . optional($settings)['currency'] . ' ' . $returnTotalAmount . '</td></tr>';

                return response(@$html);
            } else {
                $sales = Sale::with('customers')->where('status', 1)->whereBetween('date', [$startDate, $endDate])->get()->toArray();
                // echo "<pre>"; print_r($sales); exit();

                $html['thsource'] = '<th>#</th>';
                $html['thsource'] .= '<th>Invoice#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Customer</th>';
                $html['thsource'] .= '<th>Product Name</th>';
                $html['thsource'] .= '<th>Selling Price</th>';
                $html['thsource'] .= '<th>Item Sales Count</th>';
                $html['thsource'] .= '<th>Sales Amount</th>';

                $html['tdsource'] = null;
                $itemSoldCount = 0;
                $returnItemSoldCount = 0;
                $totalAmount = 0;
                $returnTotalAmount = 0;
                $counter = 0;
                $singleProduct = Product::with('units')->select('id', 'qtyPerUnit', 'product_code', 'unit_id')->find($data['product_id']);
                // echo "<pre>"; print_r($singleProduct->toArray()); exit;
                $piecesPerBox = $singleProduct['qtyPerUnit']; // Number of pieces per box
                $unit = $singleProduct->units->name;
                // dd('piece per box: ' . $piecesPerBox);
                foreach ($sales as $sale) {
                    // echo "<pre>"; print_r($sale); exit();
                    $saleID = $sale['id'];
                    $invoice_no = $sale['invoice_no'];
                    $customerName = $sale['customers']['name'];
                    $date = date('d M Y', strtotime($sale['date']));
                    $variations = unserialize($sale['items_addon']);


                    foreach ($variations as $keys => $variation) {
                        if ($variation['product_id'] == $data['product_id']) {

                            $productName = $variation['productName'];
                            $unit = $variation['unit'];
                            $selling_price = $variation['selling_price'];
                            $quantity = $variation['quantity'];
                            $amount = $variation['amount'];


                            $totalPiecesSold = $variation['quantity']; // Number of pieces sold

                            // Call the helper function to calculate sold boxes and remaining pieces
                            $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining($totalPiecesSold, $piecesPerBox);

                            $itemSoldCount = $variation['quantity'] + $itemSoldCount;
                            $totalAmount = $variation['amount'] + $totalAmount;

                            $soldValue = $quantity * $selling_price;
                            $counter = $counter + 1;

                            $html['tdsource'] .= '<tr><td>' . $counter . '</td>';
                            $html['tdsource'] .= '<td><a target="_blank" href="' . url("edit-sale") . '/' . $sale['id'] . '">' . $invoice_no . '</a></td>';
                            $html['tdsource'] .= '<td>' . $date . '</td>';
                            $html['tdsource'] .= '<td>' . $customerName . '</td>';
                            $html['tdsource'] .= '<td>' . $singleProduct->product_code . ' - ' . $productName . '</td>';
                            $html['tdsource'] .= '<td style="text-align: right;">' . $selling_price . '</td>';
                            $html['tdsource'] .= '<td style="text-align: center;">' . $quantity . ' ' . $unit . ' pieces</td>';
                            $html['tdsource'] .= '<td style="text-align: right;">' . $soldValue . '</td></tr>';
                        }
                    }

                    $returnItemSoldCount = $itemSoldCount;
                    $returnTotalAmount = $totalAmount;



                    $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining($returnItemSoldCount, $piecesPerBox);
                }
                $html['tfootsource'] = '<tr><td colspan="6" style="background: gray; font-weight: bold; color:white;">Total</td><td style="text-align: center; background: gray; font-weight: bold; color:white;">' . $result['boxes_sold'] . ' ' . $unit . ', ' . $result['items_sold'] . ' pieces</td><td style="text-align: right; background: gray; font-weight: bold; color:white;">' . optional($settings)['currency'] . ' ' . $returnTotalAmount . '</td></tr>';
                return response(@$html);
            }
        }
    }
    /*===================================================================*/
    public function downloadSoldItemsPdf(Request $request)
    {
        $data = $request->all();
        $product_id = $data['product_id'];
        // dd($product_id);
        $startDate = date('Y-m-d', strtotime($data['startDate']));
        $endDate = date('Y-m-d', strtotime($data['endDate']));
        if ($data['product_id'] == 'all') { // Fetch Sales Report All Supplier
            $products = Sale::with('customers')->where('status', 1)->whereBetween('date', [$startDate, $endDate])->get()->toArray();
        } else {
            $products = Sale::with('customers')->where('status', 1)->whereBetween('date', [$startDate, $endDate])->get()->toArray();
        }

        $pdf = PDF::loadView('reports.pdf.sales.sold-items-report', compact('products', 'startDate', 'endDate', 'product_id'));
        return $pdf->stream('sold-items-report.pdf');
    }
    /*===================================================================*/
}
