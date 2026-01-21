<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Product;
use App\SiteSetting;
use PDF;
use Exception;
use Illuminate\Support\Facades\Session;

class StockController extends Controller
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
        Session::put('page', 'stock');
        $getProducts = Product::get();
        return view('reports.stock.index', compact('getProducts'));
    }
    /*=========================================================================*/
    public function getStock(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $settings = SiteSetting::pluck('value', 'key');
        $currency = optional($settings)['currency'] ?? '';

        $productQuery = Product::with('units');

        if ($request->input('product_id') !== 'all') {
            $productQuery->where('id', $request->input('product_id'));
        }

        $products = $productQuery->get();
        $totalAmount = 0;

        // Prepare table headers
        $html = [
            'thsource' => '<th>#</th>
                      <th>Product Name</th>
                      <th>Cost</th>
                      <th>Sale Price</th>
                      <th>Current Stock</th>
                      <th>Stock Value</th>',
            'tdsource' => '',
            'tfootsource' => ''
        ];

        foreach ($products as $index => $product) {
            $unitInfo = $product->unit_info;

            if (empty($unitInfo)) {
                // Skip products with no unit info
                continue;
            }

            // Get the last unit in the array
            $lastUnit = end($unitInfo);
            reset($unitInfo); // Reset array pointer

            // Calculate stock values using last unit's prices
            $quantity = $product->quantity;
            $cost = $lastUnit['purchase_price'] ?? 0;
            $sellingPrice = $lastUnit['selling_price'] ?? 0;

            $stockValue = (float) $quantity * (float) $cost;
            $totalAmount += $stockValue;

            // Format stock display - show quantity with last unit's measurement
            $stockDisplay = number_format($quantity) . ' ' . ($lastUnit['unit'] ?? '');
            $stock = $product->getDisplayStock();
            $html['tdsource'] .= '<tr>
            <td>' . ($index + 1) . '</td>
            <td>' . htmlspecialchars($product->name) . '</td>
            <td style="text-align: center;">' . number_format($cost, 2) . '</td>
            <td style="text-align: center;">' . number_format($sellingPrice, 2) . '</td>
            <td style="text-align: center;">' . $stock . '</td>
            <td style="text-align: right;">' . number_format($stockValue, 2) . '</td>
        </tr>';
        }

        // Prepare footer with total
        $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;">
        <td colspan="5">Total</td>
        <td style="text-align: right; font-weight: bold; color:white;">'
            . $currency . ' ' . number_format($totalAmount, 2) . '</td>
    </tr>';

        return response()->json($html);
    }
    public function getStockxxx(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $settings = SiteSetting::pluck('value', 'key');
            if ($data['product_id'] == 'all') {
                $products = Product::with('units')->get();

                $html['thsource'] = '<th>#</th>';
                $html['thsource'] .= '<th>Product Name</th>';
                $html['thsource'] .= '<th>Cost</th>';
                $html['thsource'] .= '<th>Sale Price</th>';
                $html['thsource'] .= '<th>Current Stock</th>';
                $html['thsource'] .= '<th>Stock Value</th>';

                $html['tdsource'] = null;
                $totalAmount = 0;
                $returnTotalAmount = 0;
                $counter = 0;
                foreach ($products as $mainProduct) {
                    $counter = $counter + 1;
                    $mainProductID = $mainProduct['id'];
                    $mainProductName = $mainProduct['name'];
                    $quantity = $mainProduct['quantity'];
                    $stock = $mainProduct->getDisplayStock();
                    if ($mainProduct['qtyPerUnit'] > 0) {
                        $cost = $mainProduct['item_cost'];
                        $selling_price = $mainProduct['item_selling_price'];
                    } else {
                        $cost = $mainProduct['cost'];
                        $selling_price = $mainProduct['selling_price'];
                    }




                    $stockValue = (float) $quantity * (float) $cost;
                    $totalAmount = $stockValue + $totalAmount;



                    $html['tdsource'] .= '<tr><td>' . $counter . '</td>';
                    $html['tdsource'] .= '<td>' . $mainProductName . '</td>';
                    $html['tdsource'] .= '<td style="text-align: center;">' . number_format($cost, 2) . '</td>';
                    $html['tdsource'] .= '<td style="text-align: center;">' . number_format($selling_price, 2) . '</td>';
                    $html['tdsource'] .= '<td style="text-align: center;">' . $stock . ' </td>';
                    $html['tdsource'] .= '<td style="text-align: right;">' . number_format($stockValue, 2) . '</td></tr>';
                }
                $returnTotalAmount = $totalAmount;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="5">Total</td><td style="text-align: right; font-weight: bold; color:white;">' . optional($settings)['currency'] . ' '  . number_format($returnTotalAmount, 2) . '</td></tr>';
                return response(@$html);
            } else {
                $products = Product::with('units')->where('id', $data['product_id'])->get();

                $html['thsource'] = '<th>#</th>';
                $html['thsource'] .= '<th>Product Name</th>';
                $html['thsource'] .= '<th>Cost</th>';
                $html['thsource'] .= '<th>Sale Price</th>';
                $html['thsource'] .= '<th>Current Stock</th>';
                $html['thsource'] .= '<th>Stock Value</th>';

                $html['tdsource'] = null;
                $totalAmount = 0;
                $returnTotalAmount = 0;
                $counter = 0;
                foreach ($products as $mainProduct) {
                    $counter = $counter + 1;
                    $mainProductID = $mainProduct['id'];
                    $mainProductName = $mainProduct['name'];
                    $quantity = $mainProduct['quantity'];
                    $stock = $mainProduct->getDisplayStock();


                    if ($mainProduct['qtyPerUnit'] > 0) {
                        $cost = $mainProduct['item_cost'];
                        $selling_price = $mainProduct['item_selling_price'];
                    } else {
                        $cost = $mainProduct['cost'];
                        $selling_price = $mainProduct['selling_price'];
                    }

                    $stockValue = (float) $quantity * (float) $cost;
                    $totalAmount = $stockValue + $totalAmount;


                    $html['tdsource'] .= '<tr><td>' . $counter . '</td>';
                    $html['tdsource'] .= '<td>' . $mainProductName . '</td>';
                    $html['tdsource'] .= '<td style="text-align: center;">' . number_format($cost, 2) . '</td>';
                    $html['tdsource'] .= '<td style="text-align: center;">' . number_format($selling_price, 2) . '</td>';
                    $html['tdsource'] .= '<td style="text-align: center;">' . $stock . ' </td>';
                    $html['tdsource'] .= '<td style="text-align: right;">' . number_format($stockValue, 2) . '</td></tr>';
                }
                $returnTotalAmount = $totalAmount;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="5">Total</td><td style="text-align: right; font-weight: bold; color:white;">' . optional($settings)['currency'] . ' '  . number_format($returnTotalAmount, 2) . '</td></tr>';
                return response(@$html);
            }
        }
    }
    /*=========================================================================*/
    public function downloadStockPdf(Request $request)
    {
        $data = $request->all();
        if ($data['product_id'] == 'all') {
            $products = Product::with('units')->get()->toArray();
        } else {
            $products = Product::with('units')->where('id', $data['product_id'])->get()->toArray();
        }
        $pdf = PDF::loadView('reports.pdf.stock.stock-report', compact('products'));
        return $pdf->stream('stock-report.pdf');
    }
    /*=========================================================================*/
}
