<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Auth;
use App\Models\Employee, App\Models\EmployeeAdvance;
use App\Models\AdvanceHistory;
use DB;
use Exception;
use Yajra\DataTables\DataTables;

class EmployeeAdvanceController extends Controller
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
    public function advanceSalary(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                // Validate the input data
                $validatedData = $request->validate([
                    'description' => 'string|max:255',
                    'amount' => 'required|numeric|min:1',
                    'date' => 'required|date',
                ]);

                // Create a new AdvanceHistory entry
                $employeeAdvanceHistory = new AdvanceHistory();
                $employeeAdvanceHistory->description = $validatedData['description'] ?? null;
                $employeeAdvanceHistory->employee_id = $id;
                $employeeAdvanceHistory->current_paidAmount = $validatedData['amount'];
                $employeeAdvanceHistory->date = $validatedData['date'];
                $employeeAdvanceHistory->status = $request->status ?? 0;
                $employeeAdvanceHistory->save();

                // Return a successful JSON response
                return response()->json([
                    'status' => 'success',
                    'message' => 'Employee Advance Added!',
                    'data' => $employeeAdvanceHistory,
                    'url' => route('employee.advances', ['employee_id' => $id])
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
    public function getEmployeeAdvances(Request $request, $employee_id = null)
    {
        if ($request->ajax()) {
            $data = AdvanceHistory::select('id', 'description', 'current_paidAmount', 'date')
                ->where('employee_id', $employee_id)
                ->orderBy('id', 'desc') // Order by 'id' in descending order
                ->get();

            // echo "<pre>"; print_r($data->toArray()); exit;

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('amount', function ($row) {
                    return number_format($row->current_paidAmount);
                })
                ->addColumn('date', function ($row) {
                    return date('d-M-Y', strtotime($row->date)); // Format the date as 'dd-MMM-YYYY'
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0);" class="btn btn-danger btn-sm deleteRecord" data-url="' . route('advance.delete', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }

    public function deleteAdvance(Request $request, $id)
    {
        if ($request->ajax()) {
            if ($request->ajax()) {
                try {
                    $advance = AdvanceHistory::findOrFail($id);
                    $advance->delete();

                    return response()->json(['status' => 'success', 'message' => 'Record deleted successfully!']);
                } catch (\Exception $e) {
                    return response()->json(['status' => 'error', 'message' => 'Failed to delete the record.']);
                }
            }
        }
    }


    /*============================================================*/
}
