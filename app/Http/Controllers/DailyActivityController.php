<?php

namespace App\Http\Controllers;

use App\Models\AdvanceHistory;
use App\Models\CustomerOpeningBalance;
use App\Models\EmployeeReturnAdvance;
use App\Models\Expense;
use App\Models\MonthlySalary;
use App\Models\SupplierPayment;
use App\Services\DailyActivityService;
use Illuminate\Http\Request;
use DateTime;

class DailyActivityController extends Controller
{
    protected $dailyActivityService;

    public function __construct(DailyActivityService $dailyActivityService)
    {
        $this->dailyActivityService = $dailyActivityService;
    }
    /*********************************************************************/
    public function index()
    {
        $date = date('Y-m-d');
        /*||||||||||||| Employee Advance Credit/Debit Start |||||||||||||*/
        $empAdvanceTotalDebit = AdvanceHistory::select('date','description','current_paidAmount','created_at')->where('date',$date)->get()->toArray();
        $empAdvanceDebitBalance = [];
       foreach ($empAdvanceTotalDebit as $key => $value) {
         $empAdvanceDebitBalance[] = [
            'date' => $value['date'],
            'description' => $value['description'],
            'credit' => '',
            'debit' => $value['current_paidAmount'],
            'created_at' => $value['created_at']
        ];
    }


        $empAdvanceCredit = EmployeeReturnAdvance::select('date','description','return_amount','created_at')->where('date',$date)->get()->toArray();
         $empAdvanceCreditBalance = [];
       foreach ($empAdvanceCredit as $key => $value) {
         $empAdvanceCreditBalance[] = [
            'date' => $value['date'],
            'description' => $value['description'],
            'credit' => $value['return_amount'],
            'debit' => '',
            'created_at' => $value['created_at']
        ];
    }

    /*||||||||||||| Employee Advance Credit/Debit Ends |||||||||||||*/
    $monthlyEmpSalary = MonthlySalary::select('date','amount','created_at')->where('date',$date)->get()->toArray();
                 $monthlyEmpSalaryBalance = [];
               foreach ($monthlyEmpSalary as $key => $value) {
                 $monthlyEmpSalaryBalance[] = [
                    'date' => date('Y-m-d',strtotime($value['created_at'])),
                    'description' => 'Monthly Salary',
                    'credit' => '',
                    'debit' => $value['amount'],
                    'created_at' => $value['created_at']
                ];
            }

            /*||||||||||||| Daily Debit Expenses Start |||||||||||||*/
         $dailyDebitExpense = Expense::select('date','expense_for','amount','created_at')->where('date',$date)->get()->toArray();
         // p($dailyDebitExpense); exit();
                $dailyDebitExpenseBalance = [];
               foreach ($dailyDebitExpense as $key => $value) {
                 $dailyDebitExpenseBalance[] = [
                    'date' => date('Y-m-d',strtotime($value['date'])),
                    'description' => $value['expense_for'],
                    'credit' => '',
                    'debit' => $value['amount'],
                    'created_at' => $value['created_at']
                ];
            }
            /*||||||||||||| Daily Debit Expenses Ends |||||||||||||*/

            /*||||||||||||| Customer Credit/Debit Start |||||||||||||*/
        $customerTotalDebit = CustomerOpeningBalance::select('date','description','amount','created_at')->where('date',$date)->where('type','debit')->get()->toArray();

        $customerDebitBalance = [];
        foreach ($customerTotalDebit as $key => $value) {
          $customerDebitBalance[] = [
             'date' => $value['date'],
             'description' => $value['description'],
             'credit' => '',
             'debit' => $value['amount'],
             'payment_type' => '',
             'created_at' => $value['created_at']
         ];
     }


 $customerTotalCredit = CustomerOpeningBalance::select('date','description','amount','created_at')->where('date',$date)->where('type','credit')->get()->toArray();

        $customerCreditBalance = [];
        foreach ($customerTotalCredit as $key => $value) {
          $customerCreditBalance[] = [
             'date' => $value['date'],
             'description' => $value['description'],
             'credit' => $value['amount'],
             'debit' => '',
             'payment_type' => '',
             'created_at' => $value['created_at']
         ];
     }
     /*||||||||||||| Customer Credit/Debit Ends |||||||||||||*/

     $supplierTotalDebit = SupplierPayment::select('date','description','amount','created_at')->where('date',$date)->get()->toArray();
                 $supplierDebitBalance = [];
               foreach ($supplierTotalDebit as $key => $value) {
                 $supplierDebitBalance[] = [
                    'date' => $value['date'],
                    'description' => $value['description'],
                    'credit' => '',
                    'debit' => $value['amount'],
                    'created_at' => $value['created_at']
                ];
            }

            $creditDebit = array_merge($customerDebitBalance,$customerCreditBalance,$supplierDebitBalance,$empAdvanceDebitBalance,$empAdvanceCreditBalance,$monthlyEmpSalaryBalance,$dailyDebitExpenseBalance);
            usort($creditDebit, function($a, $b) {
              return new DateTime($a['created_at']) <=> new DateTime($b['created_at']);
          });
          $openingBalance = $this->dailyActivityService->calculateOPblnc($date);
// echo "<pre>"; print_r($openingBalance); exit;
        return view('daily_activity.index',compact('date','openingBalance','creditDebit'));
    }
}
