<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Session;
use App\Models\EmployeeLeave;
use App\Models\LeavePurpose;
use App\Models\EmployeeAttendance;
use App\Models\Employee;
use Exception;

class AttendanceController extends Controller
{
	/*===========================================================*/
	public function index()
	{
		Session::put('page','employeesAttendance');
		$data['allData'] = EmployeeAttendance::select('date')->groupBy('date')->orderBy('id','desc')->get();
    	// echo "<pre>"; print_r($data['allData']->toArray()); exit();
		return view('employees.attendance.view',$data);
	}
	/*===========================================================*/
	public function create(Request $request)
	{
		// echo "<pre>"; print_r($request->all()); exit();
		if ($request->isMethod('post')) {
			EmployeeAttendance::where('date',date('Y-m-d',strtotime($request->date)))->delete();
			$countemployee = count($request->employee_id);
			for ($i=0; $i <$countemployee ; $i++) {
				$attend_status = 'attend_status'.$i;
				$attend = new EmployeeAttendance();
				$attend->date = date('Y-m-d',strtotime($request->date));
				$attend->employee_id = $request->employee_id[$i];
				$attend->attend_status = $request->$attend_status;
				$attend->save();

			}
			return redirect('/employees-attendance')->with('success','Employee Attendance Successfully Submit');
		}else{

			$employees = Employee::get();
    		// echo "<pre>"; print_r($leavePurpose->toArray()); exit();
			return view('employees.attendance.create',compact('employees'));
		}
	}
	/*===========================================================*/
	public function editAttendance(Request $request, $date)
	{
		if ($request->isMethod('post')) {
   		# code...
		}else{
			$editData = EmployeeAttendance::with('employee')->where('date',$date)->get();
			$employees = Employee::get();
    		// echo "<pre>"; print_r($leavePurpose->toArray()); exit();
			return view('employees.attendance.create',compact('employees','editData'));
		}
	}
	/*===========================================================*/
	public function attendanceDetails($date)
	{
		$data['details'] = EmployeeAttendance::where('date',$date)->get();
		return view('employees.attendance.details',$data);
	}
}
