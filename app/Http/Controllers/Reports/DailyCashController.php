<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class DailyCashController extends Controller
{
    /********************************************************************/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /********************************************************/
    public function index()
    {
        $this->authenticateRole('reports');
        return view('reports.daily_cash.index');
    }

    /********************************************************/
    public function dailyCashList(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        // Convert date to proper format
        try {
            $selectedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        // Get previous day's closing balance (today's opening)
        $previousDay = Carbon::parse($selectedDate)->subDay()->format('Y-m-d');
        $openingBalance = $this->calculateDayBalance($previousDay);

        // Get all transactions for the selected day
        $transactions = $this->getDailyTransactions($selectedDate);

        // Calculate closing balance
        $closingBalance = $openingBalance;
        foreach ($transactions as $transaction) {
            if ($transaction->type === 'in') {
                $closingBalance += $transaction->amount;
            } else {
                $closingBalance -= $transaction->amount;
            }
        }

        return response()->json([
            'success' => true,
            'date' => $selectedDate,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'transactions' => $transactions
        ]);
    }

    /********************************************************/
    private function calculateDayBalance($date)
    {
        // Calculate net balance up to the given date
        $cashIn = DB::table('customer_opening_balances')
            ->whereDate('created_at', '<=', $date)
            ->sum('amount') +
            DB::table('customer_payments')
            ->whereDate('created_at', '<=', $date)
            ->sum('amount') +
            DB::table('employee_return_advances')
            ->whereDate('created_at', '<=', $date)
            ->sum('return_amount') +
            DB::table('sales')
            ->where('status', 1)
            ->whereDate('created_at', '<=', $date)
            ->sum('paid_amount');

        $cashOut = DB::table('advance_histories')
            ->whereDate('created_at', '<=', $date)
            ->sum('current_paidAmount') +
            DB::table('expenses')
            ->whereDate('created_at', '<=', $date)
            ->sum('amount') +
            DB::table('monthly_salaries')
            ->whereDate('created_at', '<=', $date)
            ->sum('amount') +
            DB::table('supplier_payments')
            ->whereDate('created_at', '<=', $date)
            ->sum('amount');

        return $cashIn - $cashOut;
    }

    /********************************************************/
    /********************************************************/
    private function getDailyTransactions($date)
    {
        $queries = [];

        // Cash In Transactions
        $cashInSources = [
            'customer_opening_balances' => [
                'description' => 'Customer Opening Balance',
                'amount_field' => 'amount'
            ],
            'customer_payments' => [
                'description' => 'Customer Payment',
                'amount_field' => 'amount'
            ],
            'employee_return_advances' => [
                'description' => 'Employee Advance Return',
                'amount_field' => 'return_amount'
            ],
            'sales' => [
                'description' => 'Sale Payment',
                'amount_field' => 'paid_amount'
            ]
        ];

        foreach ($cashInSources as $table => $config) {
            $query = DB::table($table) // Assign to variable first
                ->select(
                    'id',
                    DB::raw("'{$config['description']}' as description"),
                    DB::raw("{$config['amount_field']} as amount"),
                    DB::raw("'in' as type"),
                    'created_at'
                )
                ->whereDate('created_at', $date)
                ->where($config['amount_field'], '>', 0);

            // Add status condition only for sales table
            if ($table === 'sales') {
                $query->where('status', 1); // Only include sales with status = 1
            }

            $queries[] = $query; // Add the query to the array
        }

        // Cash Out Transactions
        $cashOutSources = [
            'advance_histories' => [
                'description' => 'Advance Payment',
                'amount_field' => 'current_paidAmount'
            ],
            'expenses' => [
                'description' => 'Expense',
                'amount_field' => 'amount'
            ],
            'monthly_salaries' => [
                'description' => 'Salary Payment',
                'amount_field' => 'amount'
            ],
            'supplier_payments' => [
                'description' => 'Supplier Payment',
                'amount_field' => 'amount'
            ]
        ];

        foreach ($cashOutSources as $table => $config) {
            $query = DB::table($table) // Assign to variable first
                ->select(
                    'id',
                    DB::raw("'{$config['description']}' as description"),
                    DB::raw("{$config['amount_field']} as amount"),
                    DB::raw("'out' as type"),
                    'created_at'
                )
                ->whereDate('created_at', $date)
                ->where($config['amount_field'], '>', 0);

            $queries[] = $query; // Add the query to the array
        }

        // Execute all queries as a union
        if (empty($queries)) {
            return [];
        }

        $baseQuery = array_shift($queries);
        foreach ($queries as $query) {
            $baseQuery->unionAll($query);
        }

        $transactions = $baseQuery->orderBy('created_at')->get();

        return $transactions;
    }

    /********************************************************/
    public function downloadDailyCashPdf(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        // Convert date to proper format
        try {
            $selectedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            $selectedDate = now()->format('Y-m-d');
        }

        // Get previous day's closing balance (today's opening)
        $previousDay = Carbon::parse($selectedDate)->subDay()->format('Y-m-d');
        $openingBalance = $this->calculateDayBalance($previousDay);

        // Get all transactions for the selected day
        $transactions = $this->getDailyTransactions($selectedDate);

        // Calculate closing balance and totals
        $closingBalance = $openingBalance;
        $cashInTotal = 0;
        $cashOutTotal = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->type === 'in') {
                $cashInTotal += $transaction->amount;
                $closingBalance += $transaction->amount;
            } else {
                $cashOutTotal += $transaction->amount;
                $closingBalance -= $transaction->amount;
            }
        }

        $netChange = $closingBalance - $openingBalance;

        $data = [
            'date' => $selectedDate,
            'formatted_date' => Carbon::parse($selectedDate)->format('d M, Y'),
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'cash_in_total' => $cashInTotal,
            'cash_out_total' => $cashOutTotal,
            'net_change' => $netChange,
            'transactions' => $transactions,
            'company_name' => config('app.name', 'Business Management System'),
            'generated_at' => now()->format('d M, Y h:i A'),
        ];

        $pdf = PDF::loadView('reports.daily_cash.pdf_template', $data);

        $filename = 'daily-cash-report-' . $selectedDate . '.pdf';

        return $pdf->download($filename);
    }
}
