<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee, App\Models\MonthlyEmployeeSalaryLog;
use Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MonthlyEmployeeIncrementController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /*===================================================*/
    public function increment(Request $request, $id = null)
    {
        $this->authenticateRole($module_page = 'employees');
        if ($request->isMethod('post')) {
            try {
                DB::beginTransaction();
                $employee = Employee::find($id);

                $previous_salary = $employee->salary;
                $current_salary = (float)$previous_salary + (float)$request->increment_salary;
                $employee->salary = $current_salary;
                $employee->save();
                $salaryData = new MonthlyEmployeeSalaryLog;
                $salaryData->employee_id = $id;
                $salaryData->previous_salary = $previous_salary;
                $salaryData->increment_salary = $request->increment_salary;
                $salaryData->current_salary = $current_salary;
                $salaryData->effected_date = date('Y-m-d', strtotime($request->effected_date));
                $salaryData->save();
                DB::commit();
                return redirect(route('employees'))->with('success', 'Employee Increment Added!');
            } catch (Exception $e) {
                DB::rollBack();
                Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
                return redirect()->back()->with($e->getMessage());
            }
        } else {
            $employee = Employee::find($id);
            $checkWageType = $employee['wage_type'];
            if ($checkWageType == 'Daily') {
                return redirect('/employees')->with('error', 'Daily Wage Employee salary cannot be increased!');
            }
            return view('employees.increment.create', compact('employee'));
        }
    }
    /*==========================================================*/
}
