<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee, App\Models\EmployeeAdvance;
use App\Models\MonthlyEmployeeSalaryLog;
use App\Models\AdvanceHistory;
use App\Models\EmployeeReturnAdvance;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
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
    public function index()
    {
        $this->authenticateRole($module_page = 'employees');
        Session::put('page', 'employees');
        $employees = Employee::orderBy('id', 'DESC')->get();
        return view('employees.index', compact('employees'));
    }
    /********************************************************************/
    public function employeesList(Request $request)
    {
        if ($request->ajax()) {
            $data = Employee::with(['user:id,name'])
                ->select(
                    'employees.id', // Explicitly specify the table for 'id'
                    'employees.national_id',
                    'employees.name',
                    'employees.mobile',
                    'employees.address',
                    'employees.salary',
                    'employees.status',
                    'employees.createdBy'
                )
                ->orderBy('employees.id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()


                ->addColumn('status', function ($row) {
                    if ($row->status === 1) {
                        return '<span class="badge badge-success" style="width: 100px;">Active</span>';
                    } else {
                        return '<span class="badge badge-warning" style="width: 100px;">Inactive</span>';
                    }
                })
                ->filterColumn('status', function ($query, $keyword) {
                    if (strtolower($keyword) === 'active') {
                        $query->where('status', 1);
                    } elseif (strtolower($keyword) === 'inactive') {
                        $query->where('status', 0);
                    }
                })
                ->addColumn('createdBy', function ($row) {
                    return $row->user ? $row->user->name : '';
                })
                ->filterColumn('createdBy', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    // Dropdown action menu
                    $actions = '
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Action
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionMenu">
                    <a class="dropdown-item addAdvance" href="javascript:void(0);" data-url="' . route('employee.advance', $row->id) . '" data-advanceListUrl="' . route('employee.advances', $row->id) . '">
                            <i class="fas fa-money-check-alt"></i> Advance
                        </a>
                        <a class="dropdown-item returnAdvance" href="javascript:void(0);" data-url="' . route('employee.return.advance', $row->id) . '" data-advanceReturnListUrl="' . route('employee.return.advances', $row->id) . '">
                            <i class="fas fa-plus-circle text-green"></i> Return Advance
                        </a>
                        <a class="dropdown-item" href="' . route('employee.increment', $row->id) . '">
                            <i class="fas fa-plus-circle text-green"></i> Increment?
                        </a>
                        <a class="dropdown-item" href="' . route('employee.details', $row->id) . '">
                            <i class="fas fa-eye text-blue"></i> View
                        </a>
                        <a class="dropdown-item" href="' . route('update.employee', $row->id) . '">
                            <i class="fas fa-edit text-blue"></i> Edit
                        </a>';
                    // Conditional delete option for pending sales
                    $actions .= '
                        <a class="dropdown-item delete" href="javascript:void(0);" data-url="' . route('delete.employee', $row->id) . '">
                            <i class="fas fa-trash text-red"></i> Delete
                        </a>';

                    $actions .= '</div></div>';
                    return $actions;
                })
                ->rawColumns(['status', 'action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /********************************************************************/
    public function deleteEmployee(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                $employee = Employee::findOrFail($id);

                // Call the delete method to trigger the `deleting` event
                $employee->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Employee Successfully Deleted!',
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the employee.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
    /*==========================================================*/
    public function create(Request $request)
    {
        $this->authenticateRole($module_page = 'employees');
        if ($request->isMethod('post')) {
            $data = $request->all();
            try {
                $employee = new Employee;
                $employee->name = $data['name'];
                $employee->national_id = $data['national_id'];
                $employee->mobile = $data['mobile'];
                $employee->address = $data['address'];
                $employee->salary = isset($data['salary']) ? $data['salary'] : 'N/A';
                $employee->save();
                return redirect('/employees')->with('success', 'Employee Successfully Added!');
            } catch (Exception $e) {
                Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
                return redirect()->back()->with($e->getMessage());
            }
        } else {

            Session::put('page', 'createEmployee');
            return view('employees.create');
        }
    }
    /*==========================================================*/
    public function edit(Request $request, $id = null)
    {
        $this->authenticateRole($module_page = 'employees');
        $editEmployee = Employee::find($id);
        $data = $request->all();
        if ($request->isMethod('post')) {
            try {
                $editEmployee->name = $data['name'];
                $editEmployee->cnic = $data['cnic'];
                $editEmployee->mobile = $data['mobile'];
                $editEmployee->address = $data['address'];
                $editEmployee->save();
                return redirect('/employees')->with('success', 'Employee Successfully Upadated!');
            } catch (Exception $e) {
                Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
                return redirect('/user-profile')->with($e->getMessage());
            }
        } else {
            return view('employees.create', compact('editEmployee'));
        }
    }

    /*===========================================================*/
    public function employeeDetails($id = null)
    {
        $this->authenticateRole($module_page = 'employees');
        $employeeDetails = MonthlyEmployeeSalaryLog::where('employee_id', $id)->get();
        // echo "<pre>"; print_r($employeeDetails->toArray()); exit();
        $employee = Employee::find($id);
        return view('employees.employee_details', compact('employee', 'employeeDetails'));
    }
    /*==========================================================*/
    public function returnAdvancex(Request $request, $id = null)
    {
        $this->authenticateRole($module_page = 'employees');
        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo "<pre>"; print_r($data); exit();
            try {
                $returnAdvance = new EmployeeReturnAdvance;
                $returnAdvance->employee_id = $id;
                $returnAdvance->date = date('Y-m-d', strtotime($data['date']));
                $returnAdvance->return_amount = $data['return_amount'];
                $returnAdvance->createdBy = Auth::user()->id;
                $returnAdvance->save();
                return redirect('/employees')->with('success', 'Advance Return Added!');
            } catch (Exception $e) {
                Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
                return redirect('/employees')->with($e->getMessage());
            }
        } else {
            Session::put('page', 'createEmployee');
            $employee = Employee::find($id);
            $checkEmployeeAdvance = AdvanceHistory::where('employee_id', $id)->first();
            // echo"<pre>"; print_r($checkEmployeeAdvance); exit();
            if ($checkEmployeeAdvance) {

                return view('employees.return_advance', compact('employee'));
            } else {
                return redirect()->back()->with('error', 'This employee did not receive an advance.');
            }
        }
    }
    /*==========================================================*/
    public function returnAdvance(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                // Validate the input data
                $validatedData = $request->validate([
                    'description' => 'string|max:255',
                    'amount' => 'required|numeric|min:1',
                    'date' => 'required|date',
                ]);

                // Create a new ReturnAdvanceHistory entry
                $returnAdvance = new EmployeeReturnAdvance();
                $returnAdvance->description = $validatedData['description'] ?? null;
                $returnAdvance->employee_id = $id;
                $returnAdvance->return_amount = $validatedData['amount'];
                $returnAdvance->date = $validatedData['date'];
                $returnAdvance->save();

                // Return a successful JSON response
                return response()->json([
                    'status' => 'success',
                    'message' => 'Employee Return Advance Added!',
                    'data' => $returnAdvance,
                    'url' => route('employee.return.advances', ['employee_id' => $id])
                ], 200);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Return a validation error response
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            } catch (\Exception $e) {
                // Handle general errors
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, Something went wrong. Please try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        // Return an error response if the request is not AJAX
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request type. AJAX request required.',
        ], 400);
    }


    /**************************************************************/
    public function getEmployeeReturnAdvances(Request $request, $employee_id = null)
    {
        if ($request->ajax()) {
            $data = EmployeeReturnAdvance::select('id', 'description', 'return_amount', 'date')
                ->where('employee_id', $employee_id)
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('amount', function ($row) {
                    return number_format($row->return_amount);
                })
                ->addColumn('date', function ($row) {
                    return date('d-M-Y', strtotime($row->date)); // Format the date as 'dd-MMM-YYYY'
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" class="btn btn-danger btn-sm deleteRecord" data-url="' . route('return.advance.delete', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }

    public function deleteReturnAdvance(Request $request, $id)
    {
        if ($request->ajax()) {
            try {
                $advance = EmployeeReturnAdvance::findOrFail($id);
                $advance->delete();

                return response()->json(['status' => 'success', 'message' => 'Record deleted successfully!']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Failed to delete the record.']);
            }
        }
    }
}
