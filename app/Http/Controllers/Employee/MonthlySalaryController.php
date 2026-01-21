<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\EmployeeLeave;
use App\Models\LeavePurpose;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\AdvanceHistory;
use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Models\EmployeeReturnAdvance;
use Exception;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class MonthlySalaryController extends Controller
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
    public function view()
    {
        $this->authenticateRole($module_page = 'employees');
        return view('employees.monthly_salaries.view');
    }
    /********************************************************************/
    public function salaryList(Request $request)
    {
        if ($request->ajax()) {
            $data = MonthlySalary::with(['employee:id,name'])
                ->select(
                    'monthly_salaries.id', // Explicitly specify the table for 'id'
                    'monthly_salaries.employee_id',
                    'monthly_salaries.date',
                    'monthly_salaries.amount',
                    'monthly_salaries.createdBy'
                )
                ->orderBy('monthly_salaries.id', 'desc'); // Order by 'id' in descending order

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    return optional($row['employee'])->name;
                })
                ->addColumn('date', function ($row) {
                    return date('M, Y', strtotime($row->date));
                })
                ->filterColumn('employee_name', function ($query, $keyword) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge badge-success" style="width: 100px;">Paid</span>';
                })
                ->rawColumns(['status']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /*==============================================================*/
    public function monthlySalaryDatewiseGet(Request $request)
    {

        /* Calculate Monthly Advance */
        function testinggetMonthlySalary($employee_id)
        {
            // dd($employee_id);
            $empMonthly = AdvanceHistory::where('employee_id', $employee_id)->get()->toArray();
            // echo"<pre>"; print_r($empMonthly); exit();
            $empAdvance = [];
            foreach ($empMonthly as $key => $value) {
                $empAdvance[] = $value['current_paidAmount'];
            }
            return $empAdvance;
        }
        /* Calculate Monthly Advance */

        /* Calculate Monthly Return Advacne */
        function returnMonthlySalary($employee_id)
        {
            $empReturnMonthly = EmployeeReturnAdvance::where('employee_id', $employee_id)->get()->toArray();
            // echo"<pre>"; print_r($empReturnMonthly); exit();
            $empReturnAdvance = [];
            foreach ($empReturnMonthly as $key => $value) {
                $empReturnAdvance[] = $value['return_amount'];
            }
            return $empReturnAdvance;
        }
        /* Calculate Monthly Return Advacne */
        $date = date('Y-m', strtotime($request->date));
        if ($date != '') {
            $where[] = ['date', 'like', $date . '%'];
        }
        $data = Employee::get()->toArray();
        // echo "<pre>"; print_r($data); exit();

        $html['thsource'] = '<th>SL</th>';
        $html['thsource'] .= '<th>Employee Name</th>';
        $html['thsource'] .= '<th>Total Advance</th>';
        $html['thsource'] .= '<th>Salary(This Month)</th>';
        $html['thsource'] .= '<th>Select</th>';
        foreach ($data as $key => $attend) {
            $check = $attend['id'];
            $advanceSalary = testinggetMonthlySalary($check);
            $advanceSalary = array_sum($advanceSalary);

            $returnMonthlyAdvance = returnMonthlySalary($check);
            $returnMonthlyAdvance = array_sum($returnMonthlyAdvance);
            // dd($advanceSalary);
            $account_salary = MonthlySalary::where('employee_id', $attend['id'])->where($where)->first();

            if ($account_salary != null) {
                // dd('ok');
                $checked = 'checked';
            } else {
                $checked = '';
            }


            // $totalattend = EmployeeAttendance::with(['employee'])->where($where)->where('employee_id',$attend['id'])->get();
            // // echo "<pre>"; print_r($totalattend->toArray()); exit();
            // $absentCount = count($totalattend->where('attend_status','Absent'));




            // $color = 'success';

            /*=================*/
            $html[$key]['tdsource'] = '<td>' . ($key + 1) . '</td>';
            $html[$key]['tdsource'] .= '<td>' . $attend['name'] . '</td>';



            $remainAdvance = $advanceSalary - $returnMonthlyAdvance;

            $html[$key]['tdsource'] .= '<td>' . $remainAdvance . '</td>';



            // echo "<pre>"; print_r($finalfee); exit();
            $html[$key]['tdsource'] .= '<td>' . $attend['salary'] . ' <input type="hidden" name="amount[]" value="' . $attend['salary'] . '"' . '</td>';
            $html[$key]['tdsource'] .= '<td>';
            $html[$key]['tdsource'] .= ' <input type="hidden" name="employee_id[]" value="' . $attend['id'] . '">' . '<input type="checkbox" name="checkmanage[]" value="' . $key . '" ' . $checked . ' style="transform: scal(1.5);margin-left: 10px;">' . '</td>';
            $html[$key]['tdsource'] .= '</td>';
        }
        return response()->json(@$html);
    }


    /*==============================================================*/
    public function paySalary(Request $request)
    {
        $this->authenticateRole($module_page = 'employees');

        if ($request->isMethod('post')) {
            $date = date('Y-m', strtotime($request->date));

            // Delete all records for this date first to avoid duplicates
            MonthlySalary::where('date', $date)->delete();

            $checkdata = $request->checkmanage;
            $successCount = 0;

            if ($checkdata != null && count($checkdata) > 0) {
                foreach ($checkdata as $key => $checkboxValue) {
                    // Make sure the array indexes exist and have values
                    if (
                        isset($request->employee_id[$key]) && !empty($request->employee_id[$key]) &&
                        isset($request->amount[$key]) && !empty($request->amount[$key])
                    ) {

                        // Create new record (always create fresh since we deleted old ones)
                        $data = new MonthlySalary;
                        $data->date = $date;
                        $data->employee_id = $request->employee_id[$key];
                        $data->amount = $request->amount[$key];
                        $data->save();
                        $successCount++;
                    }
                }

                if ($successCount > 0) {
                    return redirect('/employee-monthly-salary')->with('success', $successCount . ' employee(s) salary successfully paid/updated!');
                } else {
                    return redirect()->back()->with('error', 'No payments processed. Please check the data.');
                }
            } else {
                return redirect()->back()->with('error', 'Please select at least one employee to pay');
            }
        } else {
            return view('employees.monthly_salaries.create');
        }
    }
    public function paySalary_xxx(Request $request)
    {
        $this->authenticateRole($module_page = 'employees');
        if ($request->isMethod('post')) {
            // echo "<pre>"; print_r($request->all()); exit();
            $date = date('Y-m', strtotime($request->date));
            MonthlySalary::where('date', $date)->delete();
            $checkdata = $request->checkmanage;
            // dd(count($checkdata));
            if ($checkdata != null) {
                for ($i = 0; $i < count($checkdata); $i++) {
                    // dd($request->employee_id[$checkdata[$i]]);
                    $data = new MonthlySalary;
                    $data->date = $date;
                    $data->employee_id = $request->employee_id[$checkdata[$i]];
                    $data->amount = $request->amount[$checkdata[$i]];
                    $data->save();
                }
            }
            if (!empty(@$data) || empty($checkdata)) {
                return redirect('/employee-monthly-salary')->with('success', 'Employee salary successfully paid!');
            } else {
                return redirect()->back()->with('error', 'Sorry! Data not saved');
            }
        } else {
            return view('employees.monthly_salaries.create');
        }
    }
    /*==============================================================*/
}
