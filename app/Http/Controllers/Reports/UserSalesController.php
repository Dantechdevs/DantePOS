<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOpeningBalance;
use App\User;
use App\Models\Sale;
use Illuminate\Support\Facades\Session;
use PDF;

class UserSalesController extends Controller
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
        Session::put('page', 'userSaleReport');
        $users = User::get();
        return view('reports.user_sales.index', compact('users'));
    }
    /*=====================================================================*/
    public function getUsersSale(Request $request)
    {

        $data = $request->all();
        $time = date('H:i:s');

        $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' ' . '00:00:00'));
        $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' ' . $time));
        $userId = $request->user_id;

        $query = Sale::with(['customers:id,name', 'users:id,name'])
            ->select('id', 'date', 'invoice_no', 'customer_id', 'grand_total', 'createdBy');
        // ->where('status', 1);

        // Apply date filter
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Apply user filter if provided
        if ($userId && $userId !== 'all') {
            $query->where('createdBy', $userId);
        }

        $sales = $query->get();

        // Return the data as a JSON response
        return response()->json([
            'data' => $sales,
            'grandTotal' => $sales->sum('grand_total'), // Calculate total on the backend
        ]);
    }

    public function getUsersSalexx(Request $request)
    {
        $data = $request->all();
        $customerPayment = '';
        // echo"<pre>"; print_r($data); exit();
        $startDate = date('Y-m-d', strtotime($data['startDate']));
        $endDate = date('Y-m-d', strtotime($data['endDate']));
        // Customer Wise Else Start
        if ($data['user_id'] == 'all') { // Fetch Sales Report All Customer
            $customerPayment = Sale::with(['customers', 'users'])->where('status', 1)->whereBetween('date', [$startDate, $endDate])->get()->toArray();
        } else { // Fetch Sales Report by Customer
            $customerPayment = Sale::with(['customers', 'users'])->where('status', 1)->whereBetween('date', [$startDate, $endDate])->where('createdBy', $data['user_id'])->get()->toArray();
        }
        // Customer Wise Else Ends
        if ($customerPayment) {
            $html['thsource'] =  '<th>#</th>';
            $html['thsource'] .= '<th>Date</th>';
            $html['thsource'] .= '<th>Invoice#</th>';
            $html['thsource'] .= '<th>Customer Name</th>';
            $html['thsource'] .= '<th>Sale By</th>';
            $html['thsource'] .= '<th>Total Amount</th>';

            $html['tdsource'] = null;
            $totalAmount = 0;
            $returnTotalAmount = 0;

            foreach ($customerPayment as $key => $value) {
                // echo"<pre>"; print_r($value['amount']); exit();
                $totalAmount = $value['grand_total'] + $totalAmount;


                $html[$key]['tdsource'] = '<td>' . ($key + 1) . '</td>';
                $html[$key]['tdsource'] .= '<td>' . date('d M Y', strtotime($value['date'])) . '</td>';
                $html[$key]['tdsource'] .= '<td><a target="_blank" href="' . url("sale-invoice") . '/' . $value['id'] . '">' . $value['invoice_no'] . '</a></td>';
                $html[$key]['tdsource'] .= '<td>' . $value['customers']['name'] . '</td>';
                $html[$key]['tdsource'] .= '<td>' . $value['users']['name'] . '</td>';
                $html[$key]['tdsource'] .= '<td style="text-align: right;">' . number_format($value['grand_total'], 2) . '</td>';
            }
            $returnTotalAmount = $totalAmount;
            $html['tfootsource'] = '<tr style="background: gray; font-weight: bold; color:white;"><td colspan="5">Total</td><td style="text-align: right; font-weight: bold; color:white;">' . number_format($returnTotalAmount, 2) . '</td></tr>';

            return response(@$html);
            // return response()->json(@$html);
        } else {
            return "false";
        }
    }
    /*=====================================================================*/
    public function downloadUserSalesPdf(Request $request)
    {
        $data = $request->all();
        // echo "<pre>"; print_r($data); exit();
        $startDate = date('Y-m-d', strtotime($data['startDate']));
        $endDate = date('Y-m-d', strtotime($data['endDate']));
        // Customer Wise Else Start
        if ($data['user_id'] == 'all') { // Fetch Sales Report All Supplier
            $customerPayment = Sale::with(['customers', 'users'])->where('status', 1)->whereBetween('date', [$startDate, $endDate])->get()->toArray();
        } else {
            $customerPayment = Sale::with(['customers', 'users'])->where('status', 1)->whereBetween('date', [$startDate, $endDate])->where('added_by', $data['user_id'])->get()->toArray();
        }
        // Customer Wise Else Ends



        $pdf = PDF::loadView('reports.pdf.sales.user-sale-report', compact('customerPayment', 'startDate', 'endDate'));
        return $pdf->stream('sale-report.pdf');
    }
    /*=====================================================================*/

    public function loadPaymentsByStaff(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            $time = date('H:i:s');

            $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' ' . '00:00:00'));
            $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' ' . $time));
            // dd($startDate);

            $page = $data['page'] ?? 1; // Default to the first page

            $query = CustomerOpeningBalance::with([
                'customers:id,name', // Eager load only the 'id' and 'name' from customers
                'users:id,name'      // Eager load only the 'id' and 'name' from users
            ])
                ->select('id', 'invoice_no', 'date', 'description', 'amount', 'customer_id', 'createdBy', 'type')
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc');

            // Apply user-specific filter
            if ($data['user_id'] !== 'all') {
                $query->where('createdBy', $data['user_id']);
            }
            // $customerPayments = $query->get();
            // echo "<pre>"; print_r(CustomerOpeningBalance::count()); exit;


            // Paginate results
            $customerPayments = $query->paginate(10, ['*'], 'page', $page);

            // echo "<pre>"; print_r($customerPayments->count()); exit;
            // Separate debit and credit records
            // $customerPaymentDebit = $customerPayments->filter(function ($record) {
            //     return $record->type === 'debit';
            // });

            // $customerPaymentCredit = $customerPayments->filter(function ($record) {
            //     return $record->type === 'credit';
            // });

            // Return paginated response
            return response()->json([
                'data' => $customerPayments->items(),
                // 'debit' => $customerPaymentDebit->values(), // Reset array keys for the filtered collection
                // 'credit' => $customerPaymentCredit->values(),
                'pagination' => [
                    'total' => $customerPayments->total(),
                    'current_page' => $customerPayments->currentPage(),
                    'last_page' => $customerPayments->lastPage(),
                    'per_page' => $customerPayments->perPage(),
                ]
            ]);
        }
    }
    /***********************************************************************/
    public function compare_date($element1, $element2)
    {

        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }
    public function paymentsByStaff()
    {
        $this->authenticateRole($module_page = 'reports');
        Session::put('page', 'customerPaymentsByStaff');
        $users = User::get();
        return view('reports.user_sales.customerPaymentsByStaff', compact('users'));
    }
    /*=====================================================================*/
    // public function downloadPaymentsByStaffPdf(Request $request)
    // {
    //     $data = $request->all();
    //     // echo "<pre>"; print_r($data); exit();
    //     $startDate = date('Y-m-d', strtotime($data['startDate']));

    //     $endDate = date('Y-m-d', strtotime($data['endDate']));
    //     $customerPayment = '';

    //     if ($data['user_id'] == 'all') { // Fetch Sales Report All Customer
    //         $customerPaymentDebit = CustomerOpeningBalance::with(['customers', 'users'])->select('invoice_no', 'date', 'description', 'amount', 'created_at', 'customer_id', 'user_id')->whereBetween('date', [$startDate, $endDate])->where('type', 'debit')->get()->toArray();
    //         $customerPaymentCredit = CustomerOpeningBalance::with(['customers', 'users'])->select('invoice_no', 'date', 'description', 'amount', 'created_at', 'customer_id', 'user_id')->whereBetween('date', [$startDate, $endDate])->where('type', 'credit')->get()->toArray();
    //     } else {
    //         $customerPaymentDebit = CustomerOpeningBalance::with(['customers', 'users'])->select('invoice_no', 'date', 'description', 'amount', 'created_at', 'customer_id', 'user_id')->whereBetween('date', [$startDate, $endDate])->where('user_id', $request->user_id)->where('type', 'debit')->get()->toArray();
    //         $customerPaymentCredit = CustomerOpeningBalance::with(['customers', 'users'])->select('invoice_no', 'date', 'description', 'amount', 'created_at', 'customer_id', 'user_id')->whereBetween('date', [$startDate, $endDate])->where('user_id', $request->user_id)->where('type', 'credit')->get()->toArray();
    //     }
    //     // echo "<pre>"; print_r($customerPaymentDebit); exit();
    //     $arrDebitBalance = [];
    //     foreach ($customerPaymentDebit as $key => $value) {
    //         $arrDebitBalance[] = [
    //             'invoice_no' => "VCH-" . ($value['invoice_no']),
    //             'date' => $value['date'],
    //             'description' => $value['description'],
    //             'credit' => '',
    //             'debit' => $value['amount'],
    //             'created_at' => $value['created_at'],
    //             'customer' => $value['customers']['name'],
    //             'staff' => $value['users']['name']
    //         ];
    //     }

    //     $arrCreditBalance = [];
    //     foreach ($customerPaymentCredit as $key => $value) {
    //         $arrCreditBalance[] = [
    //             'invoice_no' => "VCH-" . ($value['invoice_no']),
    //             'date' => $value['date'],
    //             'description' => $value['description'],
    //             'credit' => $value['amount'],
    //             'debit' => '',
    //             'created_at' => $value['created_at'],
    //             'customer' => $value['customers']['name'],
    //             'staff' => $value['users']['name']
    //         ];
    //     }

    //     $debitCredit = array_merge($arrDebitBalance, $arrCreditBalance);
    //     // echo "<pre>"; print_r($debitCredit); exit();
    //     usort($debitCredit, array("App\Http\Controllers\Reports\UserSalesController", 'compare_date'));
    //     $pdf = PDF::loadView('reports.pdf.sales.customer-paymentsByStaff-report', compact('debitCredit', 'startDate', 'endDate'));
    //     return $pdf->stream('sale-report.pdf');
    // }

    public function downloadPaymentsByStaffPdf(Request $request)
    {
        $data = $request->all();
        $startDate = date('Y-m-d', strtotime($data['startDate']));
        $endDate = date('Y-m-d', strtotime($data['endDate']));
        $customerPaymentDebit = collect();
        $customerPaymentCredit = collect();

        // Use chunking to process large datasets
        if ($data['user_id'] == 'all') {
            CustomerOpeningBalance::with([
                'customers' => function ($query) {
                    // Specify columns for the related role model here if needed
                    $query->select('id', 'name');
                },
                'users' => function ($query) {
                    // Specify columns for the related role model here if needed
                    $query->select('id', 'name');
                }
            ])
                ->select('invoice_no', 'date', 'description', 'amount', 'created_at', 'customer_id', 'user_id', 'type')
                ->whereBetween('date', [$startDate, $endDate])
                ->chunk(1000, function ($records) use (&$customerPaymentDebit, &$customerPaymentCredit) {
                    foreach ($records as $record) {
                        if ($record->type == 'debit') {
                            $customerPaymentDebit->push($record);
                        } else {
                            $customerPaymentCredit->push($record);
                        }
                    }
                });
        } else {
            CustomerOpeningBalance::with([
                'customers' => function ($query) {
                    // Specify columns for the related role model here if needed
                    $query->select('id', 'name');
                },
                'users' => function ($query) {
                    // Specify columns for the related role model here if needed
                    $query->select('id', 'name');
                }
            ])
                ->select('invoice_no', 'date', 'description', 'amount', 'created_at', 'customer_id', 'user_id', 'type')
                ->whereBetween('date', [$startDate, $endDate])
                ->where('user_id', $data['user_id'])
                ->chunk(1000, function ($records) use (&$customerPaymentDebit, &$customerPaymentCredit) {
                    foreach ($records as $record) {
                        if ($record->type == 'debit') {
                            $customerPaymentDebit->push($record);
                        } else {
                            $customerPaymentCredit->push($record);
                        }
                    }
                });
        }

        $arrDebitBalance = $customerPaymentDebit->map(function ($value) {
            return [
                'invoice_no' => "VCH-" . ($value->invoice_no),
                'date' => $value->date,
                'description' => $value->description,
                'credit' => '',
                'debit' => $value->amount,
                'created_at' => $value->created_at,
                'customer' => $value->customers->name ?? '',
                'staff' => $value->users->name ?? '',
            ];
        })->toArray();

        $arrCreditBalance = $customerPaymentCredit->map(function ($value) {
            return [
                'invoice_no' => "VCH-" . ($value->invoice_no),
                'date' => $value->date,
                'description' => $value->description,
                'credit' => $value->amount,
                'debit' => '',
                'created_at' => $value->created_at,
                'customer' => $value->customers->name ?? '',
                'staff' => $value->users->name ?? '',
            ];
        })->toArray();

        // Merge and sort data
        $debitCredit = array_merge($arrDebitBalance, $arrCreditBalance);
        usort($debitCredit, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Generate PDF
        $pdf = PDF::loadView('reports.pdf.sales.customer-paymentsByStaff-report', compact('debitCredit', 'startDate', 'endDate'));

        // Return the PDF
        return $pdf->stream('sale-report.pdf');
    }
}
