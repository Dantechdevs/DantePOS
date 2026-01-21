<?php

namespace App\Http\Controllers\Reports;

use App\CustomerPayment;
use App\Godown;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\CustomerOpeningBalance;
use App\Models\Sale;
use App\Services\SaleService;
use App\SiteSetting;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReportController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }
    /********************************************************************/
    public function authenticateRole($module_page)
    {
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {

            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /***************************************************************/
    public function index()
    {
        $this->authenticateRole('reports');
        $customers = Customer::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        $godowns = Godown::where('status', 'active')->get();
        return view('reports.sale.index', compact('customers', 'users', 'godowns'));
    }
    /***************************************************************/
    public function saleReportList(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $time = date('H:i:s');

            $startDate = date('Y-m-d 00:00:00', strtotime($data['startDate']));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' ' . $time));
            $userId = $request->user_id;
            $customerId = $request->customer_id;
            $status = $request->input('status'); // Get the status filter


            $query = Sale::with(['customers:id,name,mobile', 'users:id,name'])
                ->select('id', 'date', 'invoice_no', 'customer_id', 'items_addon', 'grand_total', 'createdBy');

            // Apply date filter
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // Apply user filter if provided
            if ($userId && $userId !== 'all') {
                $query->where('createdBy', $userId);
            }

            if ($customerId && $customerId !== 'all') {
                $query->where('customer_id', $customerId);
            }

            if ($status && $status !== 'all') {
                $findWithStatus = $status === 'cancel' ? 0 : ($status === 'billed' ? 1 : ($status === 'return' ? 3 : 2));
                // $findWithStatus = $status === 'cancel' ? 0 : ($status === 'billed' ? 1 : 2);
                $query->where('status', $findWithStatus); // Apply the status filter
            }

            // Initialize response variables
            $sales = [];
            $grandTotal = 0;

            // Process data in chunks
            $query->chunk(1000, function ($records) use (&$sales, &$grandTotal) {
                foreach ($records as $record) {
                    $sales[] = $record;
                    $grandTotal += $record->grand_total;
                }
            });

            usort($sales, function ($a, $b) {
                return (new DateTime($b->date)) <=> (new DateTime($a->date));
            });
            $settings = SiteSetting::pluck('value', 'key');
            // Return the data as a JSON response
            return response()->json([
                'data' => $sales,
                'grandTotal' => $grandTotal, // Calculate total on the backend
                'startDate' => date('d/m/Y', strtotime($startDate)),
                'endDate' => date('d/m/Y', strtotime($endDate)),
                'currency' => optional($settings)['currency']
            ]);
        }
    }
    /***************************************************************/
    public function customerPayments()
    {
        $this->authenticateRole('reports');
        $customers = Customer::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        return view('reports.sale.customer_payments', compact('customers', 'users'));
    }
    /***************************************************************/
    public function customerPaymentsList(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $time = date('H:i:s');

            $startDate = date('Y-m-d 00:00:00', strtotime($data['startDate']));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' ' . $time));
            $userId = $request->user_id;
            $customerId = $request->customer_id;
            $type = $request->input('type'); // Get the type filter

            $query = CustomerOpeningBalance::with(['customers:id,name', 'users:id,name'])
                ->select('id', 'date', 'invoice_no', 'customer_id', 'amount', 'type', 'description', 'createdBy');

            // Apply date filter
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }

            // Apply user filter if provided
            if ($userId && $userId !== 'all') {
                $query->where('createdBy', $userId);
            }

            if ($customerId && $customerId !== 'all') {
                $query->where('customer_id', $customerId);
            }
            if ($type && $type !== 'all') {
                $query->where('type', $type); // Apply the type filter
            }

            // Initialize response variables
            $sales = [];
            $grandTotal = 0;

            // Process data in chunks
            $query->chunk(1000, function ($records) use (&$sales, &$grandTotal) {
                foreach ($records as $record) {
                    $sales[] = $record;
                    $grandTotal += $record->amount;
                }
            });
            usort($sales, function ($a, $b) {
                return (new DateTime($b->date)) <=> (new DateTime($a->date));
            });
            // usort($sales, function ($a, $b) {
            //     return (new DateTime($a->date)) <=> (new DateTime($b->date));
            // });
            // echo "<pre>"; print_r($sales); exit;
            $settings = SiteSetting::pluck('value', 'key');
            // Return the data as a JSON response
            return response()->json([
                'data' => $sales,
                'grandTotal' => $grandTotal, // Calculate total on the backend
                'startDate' => date('d/m/Y', strtotime($startDate)),
                'endDate' => date('d/m/Y', strtotime($endDate)),
                'currency' => optional($settings)['currency']
            ]);
        }
    }
    /***************************************************************/
    public function customerLedger()
    {
        $this->authenticateRole('reports');
        $customers = Customer::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        return view('reports.sale.customer_ledger', compact('customers', 'users'));
    }
    /***************************************************************/
    public function customerLedgerList(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();


            $customerId = $request->customer_id;
            $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

            $reportData = $this->generateCustomerLedger($customerId, $startDate, $endDate);
            if ($customerId) {
                $currentbalance = $this->saleService->customerTotalBalance($customerId);
                // dd($currentbalance);
                // Specific customer report
                $customerReport = $reportData[0];
                $customer = $customerReport['customer'];
                $openingBalance = $customerReport['openingBalance'];
                $transactions = $customerReport['transactions'];
                $totalBalance = $customerReport['totalBalance'];

                $settings = SiteSetting::pluck('value', 'key');
                return response()->json([
                    'currentBalance' => round($currentbalance),
                    'customerReport' => $customerReport,
                    'customer' => $customer,
                    'openingBalance' => round($openingBalance),
                    'transactions' => $transactions,
                    'totalBalance' => $totalBalance,
                    'startDate' => date('d/m/Y', strtotime($startDate)),
                    'endDate' => date('d/m/Y', strtotime($endDate)),
                    'currency' => optional($settings)['currency']
                ]);
            }


            return response()->json(['html' => '<p>No report available.</p>']);
        }
    }
    /***************************************************************/
    public function generateCustomerLedger(?int $customerId, string $startDate, string $endDate): array
    {
        $customers = $customerId
            ? Customer::where('id', $customerId)->get()
            : Customer::all();

        $reports = [];

        // Process customers in chunks
        $customers->chunk(100)->each(function ($customerChunk) use (&$reports, $startDate, $endDate) {
            foreach ($customerChunk as $customer) {
                // Opening balance
                // $openingBalance = CustomerOpeningBalance::where('customer_id', $customer->id)
                //     ->where('date', '<', $startDate)
                //     ->sum('amount');

                $openingBalance = $this->customerOPBalance($startDate, $customer->id);
                // dd($openingBalance);
                // Fetch transactions
                $transactions = $this->fetchTransactions($customer->id, $startDate, $endDate);
                // echo "<pre>"; print_r($transactions->toArray()); exit;
                // Calculate balances
                $balance = $openingBalance;
                $finalTransactions = $transactions->map(function ($transaction) use (&$balance) {
                    $isDebit = $transaction['type'] === 'debit';
                    $balance += $isDebit ? -$transaction['amount'] : $transaction['amount'];

                    return [
                        'invoice_no' => $transaction['invoice_no'],
                        'date' => date('d-m-Y h:i:s', strtotime($transaction['date'])),
                        'description' => $transaction['description'],
                        'debit' => $isDebit ? $transaction['amount'] : '-',
                        'credit' => !$isDebit ? $transaction['amount'] : '-',
                        'balance' => $balance < 0 ? abs($balance) . ' DB' : $balance . ' CR',
                    ];
                });

                $reports[] = [
                    'customer' => $customer,
                    'openingBalance' => $openingBalance,
                    'transactions' => $finalTransactions,
                    'totalBalance' => $balance,
                ];
            }
        });

        return $reports;
    }
    /***************************************************************/
    private function fetchTransactions(int $customerId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $transactions = collect();

        // Fetch sales with their payments in one query
        Sale::with(['customerPayments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }])
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('id', 'invoice_no', 'date', 'description', 'grand_total', 'paid_amount')
            ->chunk(500, function ($salesChunk) use (&$transactions, $startDate, $endDate) {
                foreach ($salesChunk as $sale) {
                    // Add sale transaction (debit)
                    $transactions->push([
                        'invoice_no' => $sale->invoice_no,
                        'date' => $sale->date,
                        'description' => $sale->description ?: 'Sale',
                        'amount' => round($sale->grand_total),
                        'type' => 'debit'
                    ]);

                    // Add initial payment if made at time of sale (credit)
                    if ($sale->paid_amount > 0) {
                        $transactions->push([
                            'invoice_no' => $sale->invoice_no,
                            'date' => $sale->date,
                            'description' => 'Invoice Payment',
                            'amount' => round($sale->paid_amount),
                            'type' => 'credit'
                        ]);
                    }

                    // Add additional payment transactions (credits)
                    foreach ($sale->customerPayments as $payment) {
                        $transactions->push([
                            'invoice_no' => $sale->invoice_no,
                            'date' => $payment->payment_date,
                            'description' => $payment->notes ?: 'Payment',
                            'amount' => round($payment->amount),
                            'type' => 'credit'
                        ]);
                    }
                }
            });

        // Fetch opening balance adjustments within date range
        CustomerOpeningBalance::where('customer_id', $customerId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('invoice_no', 'date', 'description', 'amount')
            ->chunk(500, function ($adjustmentsChunk) use (&$transactions) {
                foreach ($adjustmentsChunk as $adjustment) {
                    $transactions->push([
                        'invoice_no' => $adjustment->invoice_no,
                        'date' => $adjustment->date,
                        'description' => $adjustment->description ?: 'Balance Adjustment',
                        'amount' => $adjustment->amount,
                        'type' => 'credit'
                    ]);
                }
            });

        // Sort all transactions by date
        return $transactions->sortBy('date')->values();
    }
    private function fetchTransactionsxxxx(int $customerId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $transactions = collect();

        // Chunk sales, debits, and credits separately to avoid memory overload
        // Sale::select('invoice_no', 'date', 'description', 'paid_amount as amount', DB::raw("'debit' as type"))
        //     ->where('customer_id', $customerId)
        //     ->where('status', 1)
        //     ->where('payment_type', 'credit')
        //     ->whereBetween('date', [$startDate, $endDate])
        //     ->chunk(500, function ($salesChunk) use (&$transactions) {
        //         $transactions = $transactions->merge($salesChunk);
        //     });

        Sale::with(['customerPayments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }])
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->whereBetween('date', [$startDate, $endDate])
            ->chunk(500, function ($salesChunk) use (&$transactions) {
                foreach ($salesChunk as $sale) {
                    // Add sale transaction
                    $transactions->push([
                        'invoice_no' => $sale->invoice_no,
                        'date' => $sale->date,
                        'description' => $sale->description,
                        'amount' => $sale->grand_total,
                        'type' => 'debit'
                    ]);
                    // add sale paid amount
                    $transactions->push([
                        'invoice_no' => $sale->invoice_no,
                        'date' => $sale->date,
                        'description' => $sale->description,
                        'amount' => $sale->paid_amount,
                        'type' => 'credit'
                    ]);

                    // Add payment transactions
                    foreach ($sale->customerPayments as $payment) {
                        $transactions->push([
                            'invoice_no' => $sale->invoice_no,
                            'date' => $payment->payment_date,
                            'description' => $payment->notes,
                            'amount' => $payment->amount,
                            'type' => 'credit'
                        ]);
                    }
                }
            });
        // echo "<pre>"; print_r($transactions->toArray()); exit;
        // CustomerOpeningBalance::select('invoice_no', 'date', 'description', 'amount', DB::raw("'debit' as type"))
        //     ->where('customer_id', $customerId)
        //     ->where('type', 'debit')
        //     ->whereBetween('date', [$startDate, $endDate])
        //     ->chunk(500, function ($debitsChunk) use (&$transactions) {
        //         $transactions = $transactions->merge($debitsChunk);
        //     });

        // CustomerOpeningBalance::select('invoice_no', 'date', 'description', 'amount', DB::raw("'credit' as type"))
        //     ->where('customer_id', $customerId)
        //     // ->where('type', 'credit')
        //     ->whereBetween('date', [$startDate, $endDate])
        //     ->chunk(500, function ($creditsChunk) use (&$transactions) {
        //         $transactions = $transactions->merge($creditsChunk);
        //     });

        CustomerOpeningBalance::where('date', '<', $startDate)
            ->where('customer_id', $customerId)
            ->sum('amount');



        // Sort all transactions by date
        return $transactions->sortBy('date')->values();
    }
    /***************************************************************/
    public function customerOPBalance($startDate, $customer_id)
    {
        // Opening balance from CustomerOpeningBalance table
        $customerData = CustomerOpeningBalance::selectRaw("
            SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit,
            SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit
        ")
            ->where('date', '<', $startDate)
            ->where('customer_id', $customer_id)
            ->first();

        // Assign fetched values
        $customerDebitAmount = $customerData->total_debit ?? 0;
        $customerCreditAmount = $customerData->total_credit ?? 0;

        $customerPaidOpeningBalance = CustomerOpeningBalance::where('date', '<', $startDate)
            ->where('customer_id', $customer_id)
            ->sum('amount');

        $salesWithPayments = Sale::with(['customerPayments' => function ($query) use ($startDate) {
            $query->where('payment_date', '<', $startDate);
        }], 'amount')
            ->where('customer_id', $customer_id)
            ->where('status', 1)
            ->where('date', '<', $startDate)
            ->get();



        $saleAmount = $salesWithPayments->sum('grand_total');

        $customerPayment = $salesWithPayments->sum(function ($sale) {
            return $sale->customerPayments->sum('amount');
        });
        // dd($customerPayment);
        // Additional factors (optional)
        $totalSaleAmount = Sale::where('customer_id', $customer_id)
            ->where('status', 1)
            ->where('date', '<', $startDate)
            // ->where('payment_type', 'credit')
            ->sum('grand_total');

        $customerInitialBalance = Customer::find($customer_id)->opening_balance ?? 0;

        // Calculate the adjusted balance
        $customerOP_blnc = ($customerPaidOpeningBalance + $customerPayment) - ($saleAmount + $customerInitialBalance);

        return $customerOP_blnc;
    }
    public function areawise()
    {
        $this->authenticateRole('reports');
        $areas = Area::select('id', 'name')->get();
        return view('reports.sale.area_wise', compact('areas'));
    }
    public function areawisesalesList(Request $request)
    {
        if ($request->ajax()) {
            $areaId = $request->area_id;

            if (!$areaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area ID is required'
                ]);
            }

            $customers = Customer::select('id', 'name', 'area_id', 'mobile')
                ->where('area_id', $areaId)
                ->get();

            $areaCustomers = [];
            foreach ($customers as $key => $customer) {
                $customerBalance = $this->saleService->customerTotalBalance($customer->id);
                $areaCustomers[] = [
                    'id' => $customer->id,
                    'customerName' => $customer->name,
                    'customerMobile' => $customer->mobile,
                    'balance' => $customerBalance
                ];
            }

            return response()->json([
                'success' => true,
                'areaCustomers' => $areaCustomers,
                'area' => Area::find($areaId)->name ?? 'Unknown Area'
            ]);
        }

        // For initial page load
        $areas = Area::all();
        return view('reports.area-wise-sales', compact('areas'));
    }
}
