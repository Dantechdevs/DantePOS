<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Session;
use App\Models\EmployeeLeave;
use App\LeavePurpose;
use App\Models\Employee;
// use Exception;

class LeaveController extends Controller
{
    /*=========================================================*/
    public function index()
    {
    	Session::put('page','employeesLeaves');
    	$data['allData'] = EmployeeLeave::with(['employee','LeavePurpose'])->orderBy('id','desc')->get();
// echo "<pre>"; print_r($data['allData']->toArray()); exit();
    	return view('employees.leaves.view',$data);
    }
    /*=========================================================*/
    public function addLeave(Request $request)
    {
    	if ($request->isMethod('post')) {
    		try {
    			// $data = $request->all();

                // $leave_purpose_id = 0;
    			if ($request->employee_leave_id == "0") {
    				$leavePurpose = new LeavePurpose;
    				$leavePurpose->name = $request->name;
    				$leavePurpose->save();
    				$leave_purpose_id = $leavePurpose->id;
    			}else{
    				$leave_purpose_id = $request->employee_leave_id;

    			}
    			$employee_leave = new EmployeeLeave;
    			$employee_leave->employee_id = $request->employee_id;
    			$employee_leave->start_date = date('Y-m-d',strtotime($request->start_date));
    			$employee_leave->end_date = date('Y-m-d',strtotime($request->end_date));
    			$employee_leave->employee_leave_id = $leave_purpose_id;
    			$employee_leave->save();
    			return redirect('/employees-leaves')->with('success','Data Inserted successfully!');
    		} catch (Exception $e) {
    			Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
				return redirect()->back()->with($e->getMessage());
    		}
    	}else{
    		$employees = Employee::get();
    		$leavePurpose = LeavePurpose::get();
    		// echo "<pre>"; print_r($leavePurpose->toArray()); exit();
    		return view('employees.leaves.create',compact('employees','leavePurpose'));
    	}
    }
    /*=========================================================*/
    public function editLeave(Request $request, $id)
    {
    	if ($request->isMethod('post')) {
    		try {
    			// $data = $request->all();
    			// echo "<pre>"; print_r($data); exit();
    			if ($request->employee_leave_id == "0") {
    				$leavePurpose = new LeavePurpose;
    				$leavePurpose->name = $request->name;
    				$leavePurpose->save();
    				$leave_purpose_id = $leavePurpose->id;
    			}else{
    				$leave_purpose_id = $request->employee_leave_id;
    			}
    			$employee_leave = EmployeeLeave::find($id);
    			$employee_leave->employee_id = $request->employee_id;
    			$employee_leave->start_date = date('Y-m-d',strtotime($request->start_date));
    			$employee_leave->end_date = date('Y-m-d',strtotime($request->end_date));
    			$employee_leave->employee_leave_id = $leave_purpose_id;
    			$employee_leave->save();
    			return redirect('/employees-leaves')->with('success','Data Updated successfully!');
    		} catch (Exception $e) {
    			Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
				return redirect()->back()->with($e->getMessage());
    		}
    	}else{
    		$editData =  EmployeeLeave::find($id);
    		$employees = Employee::get();
    		$leavePurpose = LeavePurpose::get();
    		return view('employees.leaves.create',compact('editData','employees','leavePurpose'));
    	}
    }
    /*=========================================================*/
}
