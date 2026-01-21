<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Auth;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentDetail;
use App\Models\Supplier;
use App\User;
use PDF;
use Exception;

class PurchaseController extends Controller
{
    /***************************************************************/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /***************************************************************/
    public function index()
    {
        $this->authenticateRole($module_page = 'reports');
        $suppliers = Supplier::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        return view('reports.purchase.index', compact('suppliers', 'users'));
    }
    /***************************************************************/
    public function getPurchase(Request $request)
    {
        $data = $request->all();

        $startDate = date('Y-m-d', strtotime($data['startDate']));
        $endDate = date('Y-m-d', strtotime($data['endDate']));
        if ($data['supplier_id'] == 'all') { // Fetch Sales Report All Supplier
            $supplierPayment = Purchase::where('status', 'received')->with('suppliers')->whereBetween('date', [$startDate, $endDate])->get()->toArray();

            if ($supplierPayment) {
                $html['thsource'] =  '<th>#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Invoice#</th>';
                $html['thsource'] .= '<th>Supplier Name</th>';
                $html['thsource'] .= '<th>Total Amount</th>';

                $html['tdsource'] = null;
                $totalAmount = 0;
                $returnTotalAmount = 0;

                foreach ($supplierPayment as $key => $value) {

                    $totalAmount = $value['amount'] + $totalAmount;
                    $html[$key]['tdsource'] = '<td>' . ($key + 1) . '</td>';
                    $html[$key]['tdsource'] .= '<td>' . date('d M Y', strtotime($value['date'])) . '</td>';
                    $html[$key]['tdsource'] .= '<td style="text-align: center;">' . $value['purchase_no'] . '</td>';
                    $html[$key]['tdsource'] .= '<td>' . $value['suppliers']['name'] . '</td>';
                    $html[$key]['tdsource'] .= '<td style="text-align: right;">' . number_format($value['amount'], 2) . '</td>';
                }
                $returnTotalAmount = $totalAmount;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="4">Total</td><td style="text-align: right; font-weight: bold; color:white;">' . number_format($returnTotalAmount, 2) . '</td></tr>';
                return response()->json(@$html);
            } else {
                return "false";
            }
        } else { // Fetch Sales Report by Supplier
            $supplierPayment = Purchase::where('status', 'received')->with('suppliers')->whereBetween('date', [$startDate, $endDate])->where('supplier_id', $data['supplier_id'])->get()->toArray();
            // echo "<pre>"; print_r($supplierPayment); exit();
            if ($supplierPayment) {
                $html['thsource'] =  '<th>#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Invoice#</th>';
                $html['thsource'] .= '<th>Supplier Name</th>';
                $html['thsource'] .= '<th>Total Amount</th>';

                $html['tdsource'] = null;
                $totalAmount = 0;
                $returnTotalAmount = 0;


                foreach ($supplierPayment as $key => $value) {
                    // echo"<pre>"; print_r($value['total_amount']); exit();
                    $totalAmount = $value['amount'] + $totalAmount;

                    $html[$key]['tdsource'] = '<td>' . ($key + 1) . '</td>';
                    $html[$key]['tdsource'] .= '<td>' . date('d M Y', strtotime($value['date'])) . '</td>';
                    $html[$key]['tdsource'] .= '<td style="text-align: center;">' . $value['purchase_no'] . '</td>';
                    $html[$key]['tdsource'] .= '<td>' . $value['suppliers']['name'] . '</td>';
                    $html[$key]['tdsource'] .= '<td style="text-align: right;">' . number_format($value['amount'], 2) . '</td>';
                }
                $returnTotalAmount = $totalAmount;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="4">Total</td><td style="text-align: right; font-weight: bold; color:white;">' . number_format($returnTotalAmount, 2) . '</td></tr>';

                return response(@$html);
                // return response()->json(@$html);
            } else {
                return "false";
            }
        }
    }
    /***************************************************************/
    public function downloadPurchasePdf(Request $request)
    {

        $data = $request->all();
        $startDate = date('Y-m-d', strtotime($data['startDate']));
        $endDate = date('Y-m-d', strtotime($data['endDate']));
        if ($data['supplier_id'] == 'all') { // Fetch Sales Report All Supplier
            $supplierPayment = Purchase::where('status', 'received')->with('suppliers')->whereBetween('date', [$startDate, $endDate])->get()->toArray();
        } else {
            $supplierPayment = Purchase::where('status', 'received')->with('suppliers')->whereBetween('date', [$startDate, $endDate])->where('supplier_id', $data['supplier_id'])->get()->toArray();
        }

        $pdf = PDF::loadView('reports.pdf.purchase.purchase-report', compact('supplierPayment', 'startDate', 'endDate'));
        return $pdf->stream('purchase-report.pdf');
    }
    /***************************************************************/
}
