<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\ExpenseCategory;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class ExpenseController extends Controller
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
        $this->authenticateRole($module_page = 'expenses');
        Session::put('page', 'expenses');
        $expenses = Expense::with(['user'])->get();
        return view('expenses.view_expenses', compact('expenses'));
    }
    /********************************************************************/
    public function expensesList(Request $request)
    {
        if ($request->ajax()) {
            $data = Expense::with(['category:id,name', 'user:id,name'])
                ->select(
                    'expenses.id', // Explicitly specify the table for 'id'
                    'expenses.date',
                    'expenses.exp_category_id',
                    'expenses.expense_for',
                    'expenses.amount',
                    'expenses.createdBy'
                )
                ->orderBy('expenses.id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return date('d-m-Y | h:i A', strtotime($row->date));
                })

                ->addColumn('category', function ($row) {
                    return optional($row->category)->name;
                })
                ->filterColumn('category', function ($query, $keyword) {
                    $query->whereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('createdBy', function ($row) {
                    return optional($row->user)->name;
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
                    <a class="dropdown-item" href="' . route('update.expense', $row->id) . '">
                            <i class="fas fa-edit text-blue"></i> Edit
                        </a>';
                    // Conditional delete option for pending sales
                    $actions .= '
                        <a class="dropdown-item delete" href="javascript:void(0);" data-url="' . route('delete.expense', $row->id) . '">
                            <i class="fas fa-trash text-red"></i> Delete
                        </a>';

                    $actions .= '</div></div>';
                    return $actions;
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /*=======================================================*/
    public function addExpenses(Request $request)
    {
        $this->authenticateRole($module_page = 'expenses');

        if ($request->isMethod('post')) {
            $data = $request->all();
            try {
                // Pass `null` for a new expense to the `storeExpense` method
                $this->storeExpense($data, null);
                return redirect()->back()->with('success', 'Expense added successfully!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Oops, Something went wrong. Try again.');
            }
        } else {
            Session::put('page', 'addExpenses');
            $expCategories = ExpenseCategory::all(); // Use `all()` for better readability
            return view('expenses.add_expenses', compact('expCategories'));
        }
    }

    /*=======================================================*/
    public function editExpenses(Request $request, $id = null)
    {
        $this->authenticateRole($module_page = 'expenses');

        if ($request->isMethod('post')) {
            $data = $request->all();
            try {
                // Pass the expense ID to `storeExpense` for updating
                $this->storeExpense($data, $id);
                return redirect('/expenses')->with('success', 'Expense updated successfully!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Oops, Something went wrong. Try again.');
            }
        } else {
            $editExpense = Expense::findOrFail($id); // Use `findOrFail` to handle missing records gracefully
            $expCategories = ExpenseCategory::all();
            return view('expenses.edit_expenses', compact('expCategories', 'editExpense'));
        }
    }

    private function storeExpense($data, $id = null)
    {
        // Check if the expense exists or create a new instance
        $expense = $id ? Expense::findOrFail($id) : new Expense;

        // Assign the data to the expense model
        $expense->fill($data);

        // Save the expense (the trait handles the date and createdBy)
        $expense->save();

        return $expense; // Return the saved expense, if needed
    }


    /*=======================================================*/
    public function expenseCategories()
    {
        $this->authenticateRole($module_page = 'expenses');
        Session::put('page', 'expenseCategories');
        $expenseCategories = ExpenseCategory::get();
        return view('expenses.expense_categories', compact('expenseCategories'));
    }
    /********************************************************************/
    public function expenseCategoriesList(Request $request)
    {
        if ($request->ajax()) {
            $data = ExpenseCategory::with(['user:id,name'])
                ->select(
                    'expense_categories.id', // Explicitly specify the table for 'id'
                    'expense_categories.name',
                    'expense_categories.createdBy'
                )
                ->orderBy('expense_categories.id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('createdBy', function ($row) {
                    return optional($row->user)->name;
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
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionMenu">';
                    // Conditional delete option for pending sales
                    $actions .= '
                        <a class="dropdown-item delete" href="javascript:void(0);" data-url="' . route('delete.expense.category', $row->id) . '">
                            <i class="fas fa-trash text-red"></i> Delete
                        </a>';

                    $actions .= '</div></div>';
                    return $actions;
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /*=======================================================*/
    public function addExpCat(Request $request)
    {
        $this->authenticateRole($module_page = 'expenses');
        if ($request->isMethod('post')) {
            $expCategory = new ExpenseCategory;
            $expCategory->name = $request->name;
            $expCategory->save();
            return redirect('/expense-categories')->with('success', 'Expense Category Created!');
        } else {
            return view('expenses.add_exp_category');
        }
    }
    /*=======================================================*/
    public function deleteExpense(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                $expense = Expense::findOrFail($id);

                // Call the delete method to trigger the `deleting` event
                $expense->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Expense Successfully Deleted!',
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the Expense.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
    /*=======================================================*/
    public function deleteExpCategory(Request $request,$id = null)
    {
        if ($request->ajax()) {
            try {
                $data = ExpenseCategory::findOrFail($id);

                // Call the delete method to trigger the `deleting` event
                $data->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Data Successfully Deleted!',
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the Data.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

    }
}
