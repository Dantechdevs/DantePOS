<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Auth;
use PDF;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\AdvancePayable;
use App\Models\SupplierOpeningBalance;

class SupplierCreditDebitController extends Controller
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
            Session::put('page','supplierCreditDebitReport');
            $suppliers = Supplier::get();
            return view('reports.credit_debit_report.supplier',compact('suppliers'));
    }
    /*=======================================================*/
    public function compare_date($element1, $element2) {
               
                $datetime1 = strtotime($element1['date']);
                $datetime2 = strtotime($element2['date']);
                return $datetime1 - $datetime2;
            }
    public function supplierCreditDebitReport(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $startDate = date('Y-m-d',strtotime($data['startDate']));
            $endDate = date('Y-m-d',strtotime($data['endDate']));
            $suppliers = array();
            $debitCredit = array();
            $supplierBalance = array();
            $supplierDiscount = array();
            $supplier = array();
            $allSuppliersData = array();
            if ($data['supplier_id']=='all') {
                $allSuppliersData = $data['supplier_id'];
                $suppliers = Supplier::get()->toArray();
            }else{
                $purchaseTotalAmount = Purchase::select('purchase_no','date','description','amount')->where('status','received')->whereBetween('date',[$startDate,$endDate])->where('supplier_id',$data['supplier_id'])->get()->toArray();

                $arrPurchaseCredit = [];
               foreach ($purchaseTotalAmount as $key => $value) {
                 $arrPurchaseCredit[] = [
                    'voucher_no' => "PN-".($value['purchase_no']),
                    'date' => $value['date'],
                    'description' => $value['description'],
                    'credit' => $value['amount'],
                    'debit' => ''
                ];
            }
                $advancePayableAmount = SupplierOpeningBalance::select('voucher_no','date','description','amount')->where('type','credit')->whereBetween('date',[$startDate,$endDate])->where('supplier_id',$data['supplier_id'])->get()->toArray();
                $arrCreditBalance = [];
               foreach ($advancePayableAmount as $key => $value) {
                 $arrCreditBalance[] = [
                    'voucher_no' => "VCH-".($value['voucher_no']),
                    'date' => $value['date'],
                    'description' => $value['description'],
                    'credit' => $value['amount'],
                    'debit' => ''
                ];
            }

                $supplierPayments = SupplierPayment::select('purchase_no','date','description','amount')->whereBetween('date',[$startDate,$endDate])->where('supplier_id',$data['supplier_id'])->get()->toArray();
                $arrSupplierDebitPayment = [];
               foreach ($supplierPayments as $key => $value) {
                 $arrSupplierDebitPayment[] = [
                    'voucher_no' => "VCH-".($value['purchase_no']),
                    'date' => $value['date'],
                    'description' => $value['description'],
                    'credit' => '',
                    'debit' => $value['amount']
                ];
            }
                $advancePaidAmount = SupplierOpeningBalance::select('voucher_no','date','description','amount')->where('type','debit')->whereBetween('date',[$startDate,$endDate])->where('supplier_id',$data['supplier_id'])->get()->toArray();
                 $arrDebitBalance = [];
               foreach ($advancePaidAmount as $key => $value) {
                 $arrDebitBalance[] = [
                    'voucher_no' => "VCH-".($value['purchase_no']),
                    'date' => $value['date'],
                    'description' => $value['description'],
                    'credit' => '',
                    'debit' => $value['amount']
                ];
            }
                $debitCredit = array_merge($arrCreditBalance, $arrDebitBalance,$arrPurchaseCredit,$arrSupplierDebitPayment);
                usort($debitCredit, array("App\Http\Controllers\Reports\SupplierCreditDebitController", 'compare_date'));
                // echo "<pre>"; print_r($debitCredit); exit();
                $supplierDiscount = SupplierPayment::where('supplier_id',$data['supplier_id'])->sum('payment_discount');
                // dd($supplierDiscount);

                $supplierBalance = ($this->supplierTotalAmount($data['supplier_id']) + $this->supplierCreditAmount($data['supplier_id'])) - ($this->supplierPaymentDicsount($data['supplier_id']) + $this->supplierDebitAmount($data['supplier_id']) + $this->paymentToSupplier($data['supplier_id']));

                $suppliers = Supplier::where('id',$data['supplier_id'])->first();
            }
            $pdf = PDF::loadView('reports.pdf.credit_debit.supplier-report',compact('startDate','endDate','suppliers','debitCredit','supplierBalance','supplierDiscount','supplier','allSuppliersData'));
           return $pdf->stream('credit-debit-supplier-report.pdf');
        }
        
    }
    /*=======================================================*/
}
