<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAdvance;

class extraFunctionsController extends Controller
{
     /*==============================================================*/
    public function extrafunctions(Request $request){
        /* Hisab Calculation in Advance Start */
        $employee_advance = EmployeeAdvance::where('employee_id',$request->employee_id)->first();
        if ($employee_advance) {
            $getEmployeeAdvance = $employee_advance['advance_amount'] + $request->totalAmount;
            $getEmployeePaidAmount = $employee_advance['paid_amount'] + $request->hisabAmount;
            $due_amount = $getEmployeeAdvance - $getEmployeePaidAmount;
            EmployeeAdvance::where(['employee_id'=>$request->employee_id])->update([
                'advance_amount' => $getEmployeeAdvance,
                'paid_amount' => $getEmployeePaidAmount,
                'due_amount' => $due_amount,
                'date' => date('Y-m-d',strtotime($request['date']))
            ]);
        }
        /* Hisab Calculation in Advance End */
        $getTotalAmount = 0;
        $getHisabAmount = 0;
        /* Hisab Calculation in Advance Start */
         $employee_advance = EmployeeAdvance::where('employee_id',$request->employee_id)->first();
         if ($employee_advance) {
            $getEmployeeAdvance = $employee_advance['advance_amount'] - $getTotalAmount;
            $getEmployeePaidAmount = $employee_advance['paid_amount'] - $getHisabAmount;
            $due_amount = $getEmployeeAdvance - $getEmployeePaidAmount;
            EmployeeAdvance::where(['employee_id'=>$request->employee_id])->update([
                'advance_amount' => $getEmployeeAdvance,
                'paid_amount' => $getEmployeePaidAmount,
                'due_amount' => $due_amount,
                'date' => date('Y-m-d',strtotime($request['date']))
            ]);
        }
        /* Hisab Calculation in Advance End */
    }
     /*==============================================================*/
}
