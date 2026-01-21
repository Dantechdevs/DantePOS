<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\SiteSetting;
use App\User;
use PDF;
use Exception;
use Illuminate\Support\Facades\Session;

class ExpenseReportController extends Controller
{
    /*====================================*/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if($permissionCheck->access == 0){

            return redirect()->to('/dashboard')->send()->with('error','You have no permission!');
        }
    }
    /*===================================================*/
    public function index()
    {
        $this->authenticateRole($module_page='reports');
        Session::put('page','expenseReport');
        $expCateogry = ExpenseCategory::get()->toArray();
        return view('reports.expense.index',compact('expCateogry'));
    }
    /*=================================================================*/
    public function getExpense(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();

            $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));
            $settings = SiteSetting::pluck('value', 'key');
            if ($data['category_id']=='all') {
                $expenses = Expense::with(['category','user'])->whereBetween('date',[$startDate,$endDate])->orderBy('id','DESC')->get()->toArray();
               // echo"<pre>"; print_r($expenses); exit();
                $html['thsource'] = '<th>#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Category</th>';
                $html['thsource'] .= '<th>Expense For</th>';
                $html['thsource'] .= '<th>Amount</th>';
                $html['thsource'] .= '<th>Note</th>';
                $html['thsource'] .= '<th>Created By</th>';

                $html['tdsource'] = null;
                $totalExpense = 0;
                $returnExpense = 0;
                $counter = 0;
                foreach ($expenses as $expense) {

                    $totalExpense = $expense['amount'] + $totalExpense;
                    $counter = $counter+1;
                    $html['tdsource'] .= '<tr><td>'.$counter.'</td>';
                    $html['tdsource'] .= '<td>'.date('d M Y',strtotime($expense['date'])).'</td>';
                    $html['tdsource'] .= '<td>'.$expense['category']['name'].'</td>';
                    $html['tdsource'] .= '<td>'.$expense['expense_for'].'</td>';
                    $html['tdsource'] .= '<td style="text-align: right;">'.number_format($expense['amount'],2).'</td>';
                    $html['tdsource'] .= '<td>'.$expense['note'].'</td>';
                    $html['tdsource'] .= '<td>'.$expense['user']['name'].'</td></tr>';

                }
                $returnExpense = $totalExpense;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="4">Total</td><td style="text-align: right; font-weight: bold; color:white;">'. optional($settings)['currency'] . ' ' .number_format($returnExpense,2).'</td><td colspan="2"></td></tr>';
                return response(@$html);

            }else{
                $expenses = Expense::with(['category','user'])->where('exp_category_id',$data['category_id'])->whereBetween('date',[$startDate,$endDate])->orderBy('id','DESC')->get()->toArray();

                $html['thsource'] = '<th>#</th>';
                $html['thsource'] .= '<th>Date</th>';
                $html['thsource'] .= '<th>Category</th>';
                $html['thsource'] .= '<th>Expense For</th>';
                $html['thsource'] .= '<th>Amount</th>';
                $html['thsource'] .= '<th>Note</th>';
                $html['thsource'] .= '<th>Created By</th>';

                $html['tdsource'] = null;
                $totalExpense = 0;
                $returnExpense = 0;
                $counter = 0;
                foreach ($expenses as $expense) {
                    $totalExpense = $expense['amount'] + $totalExpense;
                    $counter = $counter+1;

                    $html['tdsource'] .= '<tr><td>'.$counter.'</td>';
                    $html['tdsource'] .= '<td>'.date('d M Y',strtotime($expense['date'])).'</td>';
                    $html['tdsource'] .= '<td>'.$expense['category']['name'].'</td>';
                    $html['tdsource'] .= '<td>'.$expense['expense_for'].'</td>';
                    $html['tdsource'] .= '<td style="text-align: right;">'.number_format($expense['amount'],2).'</td>';
                    $html['tdsource'] .= '<td>'.$expense['note'].'</td>';
                    $html['tdsource'] .= '<td>'.$expense['user']['name'].'</td></tr>';

                }
                $returnExpense = $totalExpense;
                $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="4">Total</td><td style="text-align: right; font-weight: bold; color:white;">'. optional($settings)['currency'] . ' ' .number_format($returnExpense,2).'</td><td colspan="2"></td></tr>';
                return response(@$html);
            }
        }
    }
    /*=================================================================*/
    public function downloadExpensePdf(Request $request)
    {
        $data = $request->all();
        $startDate = date('Y-m-d',strtotime($data['startDate']));
            $endDate = date('Y-m-d',strtotime($data['endDate']));
        if ($data['category_id']=='all') {
            $expenses = Expense::with(['category','user'])->whereBetween('date',[$startDate,$endDate])->orderBy('id','DESC')->get()->toArray();
        }else{
            $expenses = Expense::with(['category','user'])->where('exp_category_id',$data['category_id'])->whereBetween('date',[$startDate,$endDate])->orderBy('id','DESC')->get()->toArray();
        }
        $pdf = PDF::loadView('reports.pdf.expense.expense-report',compact('expenses','startDate','endDate'));
        return $pdf->stream('expense-report.pdf');
    }
    /*=================================================================*/
}
