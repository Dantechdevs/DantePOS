<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Auth;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Area;
use App\Models\AdvanceCustomerPayment;
use App\Models\ReceivablePayable;
use App\User;
use PDF;
use Exception;

class SalesController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if($permissionCheck->access == 0){

            return redirect()->to('/dashboard')->send()->with('error','You have no permission!');
        }
    }
    /*===================================================*/
    public function index()
    {
        $this->authenticateRole($module_page='reports');
        Session::put('page','reportSales');
        $customers = Customer::get()->toArray();
        $areas = Area::get();
        return view('reports.sale.index',compact('customers','areas'));
        
    }
    /*=====================================================================*/
    public function getSales(Request $request)
    {
        $data = $request->all();
        $customerPayment = '';
        // echo"<pre>"; print_r($data); exit();
        $startDate = date('Y-m-d',strtotime($data['startDate']));
        $endDate = date('Y-m-d',strtotime($data['endDate']));
       // Customer Wise Else Start
            if ($data['customer_id']=='all') { // Fetch Sales Report All Customer
                $customerPayment = Sale::with('customers')->where('status',1)->whereBetween('date',[$startDate,$endDate])->get()->toArray();


        }else{ // Fetch Sales Report by Customer
            $customerPayment = Sale::with('customers')->where('status',1)->whereBetween('date',[$startDate,$endDate])->where('customer_id',$data['customer_id'])->get()->toArray();

            
        }
         // Customer Wise Else Ends
        if ($customerPayment) {
                $html['thsource'] =  '<th>#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Invoice#</th>';
                $html['thsource'] .= '<th>Customer Name</th>';
                $html['thsource'] .= '<th>Total Amount</th>';

                $html['tdsource'] = null;
                $totalAmount = 0;
                $returnTotalAmount = 0;

                foreach ($customerPayment as $key => $value) {
                    // echo"<pre>"; print_r($value['amount']); exit();
                    $totalAmount = $value['amount'] + $totalAmount;
                    

                    $html[$key]['tdsource'] = '<td>'.($key+1).'</td>';
                    $html[$key]['tdsource'] .= '<td>'.date('d M Y',strtotime($value['date'])).'</td>';
                    $html[$key]['tdsource'] .= '<td><a target="_blank" href="' .url("sale-invoice").'/'.$value['id'].'">'.$value['invoice_no'].'</a></td>';
                    $html[$key]['tdsource'] .= '<td>'.$value['customers']['name'].'</td>';
                    $html[$key]['tdsource'] .= '<td style="text-align: right;">'.number_format($value['amount'],2).'</td>';
                    
                    
                }
                $returnTotalAmount = $totalAmount;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="4">Total</td><td style="text-align: right; font-weight: bold; color:white;">'.number_format($returnTotalAmount,2).'</td></tr>';

                return response(@$html);
                // return response()->json(@$html);
            }else{
                return "false";
            }
        
    }
    /*=====================================================================*/
    public function downloadSalesPdf(Request $request)
    {
        $data = $request->all();
        // echo "<pre>"; print_r($data); exit();
        $startDate = date('Y-m-d',strtotime($data['startDate']));
        $endDate = date('Y-m-d',strtotime($data['endDate']));
         // Customer Wise Else Start
            if ($data['customer_id']=='all') { // Fetch Sales Report All Supplier
            $customerPayment = Sale::with(['customers','areas'])->where('status',1)->whereBetween('date',[$startDate,$endDate])->get()->toArray();

        }else{
            $customerPayment = Sale::with(['customers','areas'])->where('status',1)->whereBetween('date',[$startDate,$endDate])->where('customer_id',$data['customer_id'])->get()->toArray();
        }
        // Customer Wise Else Ends
        
        
        
        $pdf = PDF::loadView('reports.pdf.sales.sale-report',compact('customerPayment','startDate','endDate'));
        return $pdf->stream('sale-report.pdf');
    }
    /*=====================================================================*/
}
