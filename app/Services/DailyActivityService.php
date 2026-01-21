<?php
namespace App\Services;

use App\Models\AdvanceHistory;
use App\Models\CustomerOpeningBalance;
use App\Models\EmployeeReturnAdvance;
use App\Models\Expense;
use App\Models\MonthlySalary;
use App\Models\SupplierPayment;
use Auth;
use Session;
use App\Models\Purchase;
use App\Models\Product;
use App\User;
use Illuminate\Support\Facades\Http;


class DailyActivityService
{
    public function calculateOPblnc($date)
    {
        $startDate  = date('2002-01-01');

        $newDate = date('Y-m-d',strtotime($date . '-1 days'));
        // $preDate = date('Y-m-d', $newDate);
        // dd($newDate);
        $txtOBReturn = NULL;



        $customerCreditBlnc  = CustomerOpeningBalance::whereBetween('date',[$startDate,$newDate])->where('type','credit')->sum('amount');

        $customerDebitBlnc  = CustomerOpeningBalance::whereBetween('date',[$startDate,$newDate])->where('type','debit')->sum('amount');



        $totalSupplierBlnc  = SupplierPayment::whereBetween('date',[$startDate,$newDate])->sum('amount');



        $empReturnAdvanceCreditBlnc  = EmployeeReturnAdvance::whereBetween('date',[$startDate,$newDate])->sum('return_amount');

        $empTakeAdvanceDebitBlnc  = AdvanceHistory::whereBetween('date',[$startDate,$newDate])->sum('current_paidAmount');




        $totalEmployeeSalaries  = MonthlySalary::whereBetween('date',[$startDate,$newDate])->sum('amount');





        $expenses  = Expense::whereBetween('date',[$startDate,$newDate])->sum('amount');




        $netIncome = ($customerCreditBlnc + $empReturnAdvanceCreditBlnc);

        $minusIncome= ($customerDebitBlnc + $totalSupplierBlnc + $empTakeAdvanceDebitBlnc + $expenses);

        $totalIncome  = $netIncome  - $minusIncome;

        if (!empty($totalIncome)) {
                $txtOBReturn = $totalIncome;
            } else {
                $txtOBReturn = 0;
            }

            return $txtOBReturn;

    }
}
