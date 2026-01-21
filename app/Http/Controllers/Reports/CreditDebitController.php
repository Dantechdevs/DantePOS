<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use PDF;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\AdvanceCustomerPayment;
use App\Models\CustomerOpeningBalance;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CreditDebitController extends Controller
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
        $this->authenticateRole($module_page = 'reports');
        Session::put('page', 'creditDebitReport');
        $customers = Customer::get();
        $areas = Area::get();
        return view('reports.credit_debit_report.create', compact('customers', 'areas'));
    }
    /*=======================================================*/
    public function compare_date($element1, $element2)
    {

        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }
    public function creditDebitReport(Request $request)
    {
        if ($request->isMethod('post')) {

            $data = $request->all();
            // echo "<pre>"; print_r($data); exit();
            $startDate = date('Y-m-d', strtotime($data['startDate']));

            $endDate = date('Y-m-d', strtotime($data['endDate']));



            $newDate = date('Y-m-d', strtotime($data['startDate'] . '-1 days'));

            $oldDate = date('1990-01-01');

            $area = '';
            $areaID = '';
            $sale_type = $data['sale_type'];
            $receivable = '';
            $payable = '';
            $customerTotalBalance = '';
            $customerOPBlncInReport = '';
            $paymentDiscount = '';
            $customer = '';
            $debitCredit = '';
            $openingBalance = '';

            // echo "<pre>"; print_r($data); exit();
            if ($data['sale_type'] == 'areaWise') {
                $areaID = $data['area_id'];
                $area = Area::where('id', $data['area_id'])->first();
            } else {
                $customer_id = $data['customer_id'];
                $openingBalance = CustomerOpeningBalance::select('date', 'description', 'amount')->where('customer_id', $customer_id)->first();


                $totalAmount = Sale::select('invoice_no', 'date', 'description', 'amount', 'created_at')->where('status', 1)->where('customer_id', $customer_id)->whereBetween('date', [$startDate, $endDate])->get()->toArray();
                //   echo"<pre>"; print_r($totalAmount); exit();
                $arrSaleDebit = [];
                foreach ($totalAmount as $key => $value) {
                    $arrSaleDebit[] = [
                        'invoice_no' => "SN-" . ($value['invoice_no']),
                        'date' => $value['date'],
                        'description' => $value['description'],
                        'credit' => '',
                        'debit' => $value['amount'],
                        'created_at' => $value['created_at']
                    ];
                }
                // echo "<pre>"; print_r($totalAmount); exit();
                $totalPayable = CustomerOpeningBalance::select('invoice_no', 'date', 'description', 'amount', 'created_at')->whereBetween('date', [$startDate, $endDate])->where('customer_id', $customer_id)->where('type', 'debit')->get()->toArray();
                // $debitAmount = array_merge($totalPayable, $totalAmount);

                $arrDebitBalance = [];
                foreach ($totalPayable as $key => $value) {
                    $arrDebitBalance[] = [
                        'invoice_no' => "VCH-" . ($value['invoice_no']),
                        'date' => $value['date'],
                        'description' => $value['description'],
                        'credit' => '',
                        'debit' => $value['amount'],
                        'created_at' => $value['created_at']
                    ];
                }


                $totalAdvance = AdvanceCustomerPayment::select('invoice_no', 'date', 'description', 'amount', 'created_at')->whereBetween('date', [$startDate, $endDate])->where('customer_id', $customer_id)->get()->toArray();
                // echo "<pre>"; print_r($totalAdvance); exit();
                $arrCustomerPaymentCredit = [];
                foreach ($totalAdvance as $key => $value) {
                    $arrCustomerPaymentCredit[] = [
                        'invoice_no' => "CP-" . ($value['invoice_no']),
                        'date' => $value['date'],
                        'description' => $value['description'],
                        'credit' => $value['amount'],
                        'debit' => '',
                        'created_at' => $value['created_at']
                    ];
                }

                $totalReceivable = CustomerOpeningBalance::select('invoice_no', 'date', 'description', 'amount', 'created_at')->whereBetween('date', [$startDate, $endDate])->where('customer_id', $customer_id)->where('type', 'credit')->get()->toArray();
                // $creditAmount = array_merge($totalReceivable, $totalAdvance);

                $arrCreditBalance = [];
                foreach ($totalReceivable as $key => $value) {
                    $arrCreditBalance[] = [
                        'invoice_no' => "VCH-" . ($value['invoice_no']),
                        'date' => $value['date'],
                        'description' => $value['description'],
                        'credit' => $value['amount'],
                        'debit' => '',
                        'created_at' => $value['created_at']
                    ];
                }

                $debitCredit = array_merge($arrDebitBalance, $arrCreditBalance, $arrCustomerPaymentCredit, $arrSaleDebit);
                // echo "<pre>"; print_r($debitCredit); exit();

                // Comparison function


                // Sort the array
                // usort($debitCredit,function() {});
                // usort(array($this, 'compareDates'));
                // usort($debitCredit, array($this, "compare_date"));
                usort($debitCredit, array("App\Http\Controllers\Reports\CreditDebitController", 'compare_date'));
                // echo "<pre>"; print_r($debitCredit); exit();
                $paymentDiscount = AdvanceCustomerPayment::where('customer_id', $customer_id)->sum('payment_discount');


                $customerTotalBalance = ($this->paymentDicsount($customer_id) + $this->advanceAmount($customer_id) + $this->customerCreditAmount($customer_id)) - ($this->totalAmount($customer_id) + $this->customerDebitAmount($customer_id));

                $customerOPBlncInReport = $this->customerOPBalance($oldDate, $newDate, $customer_id);

                $customer = Customer::where('id', $request->customer_id)->first();
            }
            $sale_type = $data['sale_type'];
            $pdf = PDF::loadView('reports.pdf.credit_debit.report', compact('startDate', 'endDate', 'debitCredit', 'openingBalance', 'areaID', 'area', 'sale_type', 'customerTotalBalance', 'customerOPBlncInReport', 'customer', 'paymentDiscount'));
            return $pdf->stream('credit-debit-report.pdf');
        }
    }
    /****************************************************************/
    public function creditDebitDownloadPDF(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $saleType = $request->sale_type;

            if ($saleType === 'customerWise') {
                $customerId = $request->customer_id !== 'all' ? $request->customer_id : null;
                $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
                $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

                $reportData = $this->generateCustomerWiseReport($customerId, $startDate, $endDate);
                // echo "<pre>"; print_r($reportData); exit;

                if ($customerId) {
                    $customerReport = $reportData[0];
                    $customer = $customerReport['customer'];
                    $openingBalance = $customerReport['openingBalance'];
                    $transactions = $customerReport['transactions'];
                    $totalBalance = $customerReport['totalBalance'];

                    ini_set('max_execution_time', 300);
                    // Start measuring execution time
                    // $startTime = microtime(true);
                    $pdf = PDF::loadView('reports.pdf.credit_debit.report', compact('customerReport', 'customer', 'openingBalance', 'transactions', 'totalBalance', 'startDate', 'endDate'));
                    // Check the execution time
                    // $executionTime = microtime(true) - $startTime;
                    // dd($executionTime);
                    $file_name = $customer['name'] . '_' . date('d-m-Y h:i:s');
                } else {
                    $pdf = PDF::loadView('reports.pdf.credit_debit.all_customers', compact('reportData', 'startDate', 'endDate'));
                    $file_name = 'All_Customers' . '_' . date('d-m-Y h:i:s');
                }
            } elseif ($saleType === 'areaWise') {
                // Handle area-wise logic here
            }

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('file_name', $file_name);
        }
    }
    /****************************************************************/
    // public function creditDebitSearch(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = $request->all();
    //         $saleType = $request->sale_type;

    //         if ($saleType === 'customerWise') {
    //             $customerId = $request->customer_id !== 'all' ? $request->customer_id : null;
    //             $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
    //             $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

    //             $reportData = $this->generateCustomerWiseReport($customerId, $startDate, $endDate);

    //             if ($customerId) {
    //                 $customerReport = $reportData[0];
    //                 $customer = $customerReport['customer'];
    //                 $openingBalance = $customerReport['openingBalance'];
    //                 $transactions = $customerReport['transactions'];
    //                 $totalBalance = $customerReport['totalBalance'];

    //                 $html = view('reports.pdf.credit_debit.report', compact('customerReport', 'customer', 'openingBalance', 'transactions', 'totalBalance', 'startDate', 'endDate'))->render();
    //                 return response()->json(['html' => $html]);
    //             } else {
    //                 $html = view('reports.pdf.credit_debit.all_customers', compact('reportData', 'startDate', 'endDate'))->render();
    //                 return response()->json(['html' => $html]);
    //             }
    //         } elseif ($saleType === 'areaWise') {
    //             // Handle area-wise logic here
    //         }

    //         return response()->json(['html' => '<p>No report available.</p>']);
    //     }
    // }

    public function creditDebitSearch(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $saleType = $request->sale_type;

            if ($saleType === 'customerWise') {
                $customerId = $request->customer_id !== 'all' ? $request->customer_id : null;
                $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
                $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

                $reportData = $this->generateCustomerWiseReport($customerId, $startDate, $endDate);
                $headers = ['#', 'Invoice#', 'Date', 'Description', 'Debit', 'Credit', 'Balance']; // Dynamic headers
                if ($customerId) {
                    // Specific customer report
                    $customerReport = $reportData[0];
                    $customer = $customerReport['customer'];
                    $openingBalance = $customerReport['openingBalance'];
                    $transactions = $customerReport['transactions'];
                    $totalBalance = $customerReport['totalBalance'];

                    return response()->json([
                        'customerReport' => $customerReport,
                        'customer' => $customer,
                        'openingBalance' => $openingBalance,
                        'transactions' => $transactions,
                        'totalBalance' => $totalBalance,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'headers' => $headers,
                        'customer_type' => 'single'
                    ]);
                } else {
                    // All customers report

                    return response()->json([
                        'reportData' => $reportData,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'headers' => $headers,
                        'customer_type' => 'all'
                    ]);
                }
            } elseif ($saleType === 'areaWise') {
                // Handle area-wise logic
                return response()->json(['html' => '<p>Area-wise reports are under development.</p>']);
            }

            return response()->json(['html' => '<p>No report available.</p>']);
        }
    }



    public function generateCustomerWiseReport(?int $customerId, string $startDate, string $endDate): array
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

                // Fetch transactions
                $transactions = $this->fetchTransactions($customer->id, $startDate, $endDate);

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



    private function fetchTransactions(int $customerId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $transactions = collect();

        // Chunk sales, debits, and credits separately to avoid memory overload
        Sale::select('invoice_no', 'date', 'description', 'grand_total as amount', DB::raw("'credit' as type"))
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->whereBetween('date', [$startDate, $endDate])
            ->chunk(500, function ($salesChunk) use (&$transactions) {
                $transactions = $transactions->merge($salesChunk);
            });

        CustomerOpeningBalance::select('invoice_no', 'date', 'description', 'amount', DB::raw("'debit' as type"))
            ->where('customer_id', $customerId)
            ->where('type', 'debit')
            ->whereBetween('date', [$startDate, $endDate])
            ->chunk(500, function ($debitsChunk) use (&$transactions) {
                $transactions = $transactions->merge($debitsChunk);
            });

        CustomerOpeningBalance::select('invoice_no', 'date', 'description', 'amount', DB::raw("'credit' as type"))
            ->where('customer_id', $customerId)
            ->where('type', 'credit')
            ->whereBetween('date', [$startDate, $endDate])
            ->chunk(500, function ($creditsChunk) use (&$transactions) {
                $transactions = $transactions->merge($creditsChunk);
            });

        // Sort all transactions by date
        return $transactions->sortBy('date')->values();
    }

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

        // Additional factors (optional)
        $totalSaleAmount = Sale::where('customer_id', $customer_id)
            ->where('status', 1)
            ->where('date', '<', $startDate)
            ->sum('grand_total');

        // Calculate the adjusted balance
        $customerOP_blnc = $customerCreditAmount - ($totalSaleAmount + $customerDebitAmount);

        return $customerOP_blnc;
    }



    /*=======================================================*/
    public function customerOPBalancex($oldDate, $newDate, $customer_id)
    {

        $customerBlncReturn = NULL;
        $totalSaleAmount = Sale::whereBetween('date', [$oldDate, $newDate])->where('customer_id', $customer_id)->where('status', 1)->sum('amount');

        $customerPaymets = AdvanceCustomerPayment::where('customer_id', $customer_id)->whereBetween('date', [$oldDate, $newDate])->sum('amount');

        $customerDebitAmount = CustomerOpeningBalance::whereBetween('date', [$oldDate, $newDate])->where('customer_id', $customer_id)->where('type', 'debit')->sum('amount');

        $customerCreditAmount = CustomerOpeningBalance::whereBetween('date', [$oldDate, $newDate])->where('customer_id', $customer_id)->where('type', 'credit')->sum('amount');
        // dd($customerCreditAmount);
        $customerOP_blnc = ($this->paymentDicsount($customer_id) + $customerCreditAmount + $customerPaymets) - ($totalSaleAmount + $customerDebitAmount);

        if (!empty($customerOP_blnc)) {
            $customerBlncReturn = $customerOP_blnc;
        } else {
            $customerBlncReturn = 0;
        }

        return $customerBlncReturn;
    }


    /*=======================================================*/
}
