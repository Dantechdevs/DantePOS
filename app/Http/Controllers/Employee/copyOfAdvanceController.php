<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Auth;
use App\Models\Employee,App\Models\EmployeeAdvance;
use App\Models\Gate;
use App\Models\Bharai;
use App\Models\BharaiHisab;
use App\Models\Pathair;
use App\Models\PathairHisab;
use App\Models\AdvanceHistory;
use App\Models\Nakasi;
use App\Models\NakasiEmployeeReturnAdvanceHistory;
use DB;
// use Exception;

class EmployeeAdvanceController extends Controller
{
    /*============================================================*/
    public function advanceSalary(Request $request,$id=null)
    {
        if ($request->isMethod('post')) {

            $data = $request->all();

            /*=====///////////===Pathair Calculations Starts===////////////========*/

            /* Get Pathair Amount Start */
            $returnPathairAmount = 0;
            $pathairEmployee = Pathair::where(['employee_id'=>$id])->get();
            $pathairAmount = 0;
            if ($pathairEmployee === NULL) {
                $returnPathairAmount = 0;
            } else {
                $empTotalAmount = $pathairEmployee;
                foreach ($empTotalAmount as $value) {
                    $pathairAmount = $value->totalAmount + $pathairAmount;
                }
                $returnPathairAmount = $pathairAmount;
            }
            /* Get Pathair Amount End */

            /* Get Pathair Hisab Amount Start */
            $returnPathairHisab = 0;
            $pathairEmployee = PathairHisab::where(['employee_id'=>$id])->get();
            $bharaiHisabAmounut = 0;
            if ($pathairEmployee === NULL) {
                $returnPathairHisab = 0;
            } else {
                $empTotalAmount = $pathairEmployee;
                foreach ($empTotalAmount as $value) {
                    $bharaiHisabAmounut = $value->hisabAmount + $bharaiHisabAmounut;
                }
                $returnPathairHisab = $bharaiHisabAmounut;
            }
            /* Get Pathair Hisab Amount End */

            /*=====///////////===Pathair Calculations Ends===////////////========*/

            /*======/////////===Bharai Calculations Starts===//////////========*/
            /* Get Bharai Amount Start */
            $returnTotalAmount = 0;
            $employeeTotalAmount = Bharai::where(['employee_id'=>$id])->get();
            $returnAmountOutPut = 0;
            if ($employeeTotalAmount === NULL) {
                $returnTotalAmount = 0;
            } else {
                $empTotalAmount = $employeeTotalAmount;
                foreach ($empTotalAmount as $value) {
                    $returnAmountOutPut = $value->totalAmount + $returnAmountOutPut;
                }
                $returnTotalAmount = $returnAmountOutPut;
            }
            /* Get Bharai Amount End */

            /* Get Bharai Hisab Amount Start */
            $returnHisabAmount = 0;
            $employeeTotalAmount = BharaiHisab::where(['employee_id'=>$id])->get();
            $bharaiHisabAmounut = 0;
            if ($employeeTotalAmount === NULL) {
                $returnHisabAmount = 0;
            } else {
                $empTotalAmount = $employeeTotalAmount;
                foreach ($empTotalAmount as $value) {
                    $bharaiHisabAmounut = $value->hisabAmount + $bharaiHisabAmounut;
                }
                $returnHisabAmount = $bharaiHisabAmounut;
            }
            /* Get Bharai Hisab Amount End */

            /*======/////////===Bharai Calculations Ends===//////////========*/

            /*=====///////////===Nakasi Calculations Starts===////////////========*/

            /* Get Nakasi Work Amount Start */
            $returnNakasiWorkAmount = 0;
            $nakasiEmployee = Nakasi::where(['employee_id'=>$id])->get();
            $nakasiWorkAmount = 0;
            if ($nakasiEmployee === NULL) {
                $returnNakasiWorkAmount = 0;
            } else {
                $empTotalAmount = $nakasiEmployee;
                foreach ($empTotalAmount as $value) {
                    $nakasiWorkAmount = $value->work_amount + $nakasiWorkAmount;
                }
                $returnNakasiWorkAmount = $nakasiWorkAmount;
            }
            /*=====///////////===Nakasi Calculations Ends===////////////========*/

            /*=====///////===Calculate Nakasi Employee Return Advance Starts===////////////========*/

            /* Get Nakasi Employee Return  Amount Start */
            $returnNakasiEmployeeAdvance = 0;
            $nakasiEmployee = NakasiEmployeeReturnAdvanceHistory::where(['employee_id'=>$id])->get();
            $nakasiEmpReturnAmount = 0;
            if ($nakasiEmployee === NULL) {
                $returnNakasiEmployeeAdvance = 0;
            } else {
                $empTotalAmount = $nakasiEmployee;
                foreach ($empTotalAmount as $value) {
                    $nakasiEmpReturnAmount = $value->advance_return + $nakasiEmpReturnAmount;
                }
                $returnNakasiEmployeeAdvance = $nakasiEmpReturnAmount;
            }
            /*=====///////===Calculate Nakasi Employee Return Advance Ends===////////////========*/



            $employee_advance = EmployeeAdvance::where('employee_id',$id)->first();

            if ($employee_advance) {

                $getEmployeeAdvance = ($employee_advance['advance_amount'] + $request->advance_amount);
                if ($employee_advance['wage_type']=='Monthly') {
                    $getMonthlyEmployeeSalary = Employee::select('salary')->where('id',$id)->first();

                    if ($getMonthlyEmployeeSalary->salary <= $getEmployeeAdvance) {
                        return redirect()->back()->with('error','Monthly Employee cannot take more advance');
                    }
                }
                     // echo "<pre>"; print_r($getEmployeeAdvance); exit();
                $getPaidAmount = $employee_advance['paid_amount'];
                $employeeBalance = $getEmployeeAdvance - $getPaidAmount;
                EmployeeAdvance::where('employee_id',$id)->update([
                    'wage_type' => $data['wage_type'],
                    'advance_amount' => $getEmployeeAdvance,
                    'paid_amount' => $getPaidAmount,
                    'due_amount' => $employeeBalance,
                    'date' => date('Y-m-d',strtotime($data['date']))
                ]);
            }else{
                try {
                    if ($data['wage_type']=='Monthly') {
                        $getMonthlyEmployeeSalary = Employee::select('salary')->where('id',$id)->first();

                        if ($getMonthlyEmployeeSalary->salary <= $data['advance_amount']) {
                            return redirect()->back()->with('error','Monthly Employee cannot take more advance');
                        }
                    }
                    $employee_advance = new EmployeeAdvance;
                    $employee_advance->employee_id = $id;
                    $employee_advance->wage_type = $data['wage_type'];
                    $employee_advance->employee_type = $data['employee_type'];
                    $employee_advance->advance_amount = $data['advance_amount'];
                    $employee_advance->due_amount = $data['advance_amount'];
                    $employee_advance->date = date('Y-m-d',strtotime($data['date']));

                    DB::transaction(function() use($request,$employee_advance,$data,$id,$returnNakasiEmployeeAdvance,$returnPathairAmount,$returnPathairHisab,$returnTotalAmount,$returnHisabAmount){

                        if($employee_advance->save()){
                            $employee_advance = EmployeeAdvance::where('employee_id',$id)->first();
                            switch ($employee_advance) {

                                case $employee_advance['employee_type']=='Pathair': 
                                EmployeeAdvance::where('employee_id',$id)->update([
                                    'advance_amount' => $data['advance_amount'] + $returnPathairAmount,
                                    'paid_amount' => $returnPathairHisab,
                                    'due_amount' => ($data['advance_amount'] + $returnPathairAmount) - $returnPathairHisab
                                ]);   
                                break;
                                case $employee_advance['employee_type']=='Bharai':

                                EmployeeAdvance::where('employee_id',$id)->update([
                                    'advance_amount' => $data['advance_amount'] + $returnTotalAmount,
                                    'paid_amount' => $returnHisabAmount,
                                    'due_amount' => ($data['advance_amount'] + $returnTotalAmount) - $returnHisabAmount
                                ]);
                                break;
                                case $employee_advance['employee_type']=='Nakasi':
                                EmployeeAdvance::where('employee_id',$id)->update([
                                    'advance_amount' => $data['advance_amount'],
                                    'paid_amount' => $returnNakasiEmployeeAdvance,
                                    'due_amount' => $data['advance_amount']  - $returnNakasiEmployeeAdvance
                                ]);
                                break;
                                case $employee_advance['employee_type']=='Delivery':
                            # code...
                                break;
                                case $employee_advance['employee_type']=='Monthly':
                            # code...
                                break;
                                default:
                                return redirect()->back()->with('error','No Record Found!');
                            }

                            

                        }

                    });

                } catch (Exception $e) {
                    Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
                    return redirect()->back()->with($e->getMessage());
                }

            }
            /* Employee Advance History Data Save in Separate Table */
                            $employeeAdvanceHistory = new AdvanceHistory;
                            $employeeAdvanceHistory->gate_id = $data['gate_id'];
                            $employeeAdvanceHistory->description = $request->description;
                            $employeeAdvanceHistory->employee_id = $id;
                            $employeeAdvanceHistory->employee_type = $data['employee_type'];
                            $employeeAdvanceHistory->wage_type = $request->wage_type;
                            $employeeAdvanceHistory->current_paidAmount = $request->advance_amount;
                            $employeeAdvanceHistory->date = date('Y-m-d',strtotime($data['date']));
                            $employeeAdvanceHistory->status = isset($data['status'])?$data['status']:0;
                            $employeeAdvanceHistory->save();

            return redirect('/employees')->with('success','Employee Advance Added!');
            
        }else{
            $employee = Employee::find($id);
            $gates = Gate::get();
            return view('employees.employee_advance',compact('employee','gates'));
        }
    }
    /*============================================================*/
}
