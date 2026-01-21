<?php

namespace App\Http\Controllers;

use App\CustomerPayment;
use Illuminate\Http\Request;
use App\User;
use App\Group;
use App\Models\Customer;
use App\Models\CustomerOpeningBalance;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\SiteSetting;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class AdminController extends Controller
{

    public function sendInvoice()
    {
        // Replace with your access token, phone number ID, and recipient
        $accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $recipient = '+923126712667'; // Replace with the recipient's WhatsApp number

        // Example dynamic data
        $customerName = 'John Doe';
        $invoiceNumber = 'INV-12345';
        $date = date('d F Y'); // Current date

        // Array of items
        $items = [
            ['name' => 'Product A', 'price' => 10.00],
            ['name' => 'Product B', 'price' => 20.50],
            ['name' => 'Product C', 'price' => 5.75],
        ];

        // Calculate total amount
        $totalAmount = array_sum(array_column($items, 'price'));

        // Format items into a numbered list
        $itemLines = "";
        foreach ($items as $index => $item) {
            $itemLines .= ($index + 1) . ". " . $item['name'] . " - $" . number_format($item['price'], 2) . "\n";
        }

        // Generate the message
        $message = "*Invoice Details*\n"
            . "-----------------\n"
            . "*Customer Name*: $customerName\n"
            . "*Invoice Number*: $invoiceNumber\n"
            . "*Date*: $date\n\n"
            . "*Items:*\n"
            . $itemLines
            . "\n*Total Amount*: $" . number_format($totalAmount, 2) . "\n"
            . "-----------------\n"
            . "Thank you for your purchase!";

        // API URL
        $url = "https://graph.facebook.com/v16.0/$phoneNumberId/messages";

        // Initialize Guzzle Client
        $client = new Client();

        // Send POST request
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ],
        ]);
        $responseBody = json_decode($response->getBody(), true);
        // dd($responseBody);

        return $response->getBody();
    }
    /*====================================*/
    public function authenticateRole($module_page)
    {
        // dd($module_page);
        $permissionCheck =  checkRolePermission($module_page);
        if ($permissionCheck->access == 0) {
            return redirect()->to('/dashboard')->send()->with('error', 'You have no permission!');
        }
    }
    /*===================================================*/
    public function login(Request $request)
    {

        $data = $request->input();
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'status' => '1'])) {
            return redirect('/dashboard')->with('success', 'Welcome  ' . Auth::user()->name);
        } else {
            return redirect('/')->with('flash_message_error', 'Invalid username or password');
        }
    }
    /*===================================================*/
    public function dashboard()
    {
        $permissionCheck = checkRolePermission($module_page = 'dashboard');
        $page_dashboard = "dashboard";
        Session::put('page', 'dashboard');

        $settings = SiteSetting::pluck('value', 'key');

        return view('dashboard', compact('page_dashboard', 'permissionCheck', 'settings'));
    }

    public function dashboardData()
    {
        [
            'countSales' => $countSales = Sale::count(),
            'countPurchase' => $countPurchase = Purchase::where('status', 'received')->count(),
            'countSupplier' => $countSupplier = Supplier::count(),
            'countCustomers' => $countCustomers = Customer::count(),
            'countProducts' => $countProducts = Product::count(),
            'countEmployees' => $countEmployees = Employee::count(),
        ];

        // Calculate financial aggregates
        [
            'sale_total' => $sale_total = Sale::where('status', 1)->sum('grand_total'),
            'sale_paid_amount' => $sale_paid_amount = Sale::where('status', 1)->sum('paid_amount'),
            'purchaseAmount' => $purchaseAmount = Purchase::where('status', 'received')->sum('grand_total'),
            'total_expenses' => $total_expenses = Expense::sum('amount'),
            'customerCreditAmount' => $customerCreditAmount = CustomerOpeningBalance::sum('amount'),
            'invoicePayments' => $invoicePayments = CustomerPayment::sum('amount'),
            'supplierPayments' => $supplierPayments = SupplierPayment::sum('amount'),
            'supplierOpeningBalance' => $supplierOpeningBalance = Supplier::sum('opening_balance'),
            'customerOpeningBalance' => $customerOpeningBalance = Customer::sum('opening_balance'),
        ];

        // Calculate product stock value (optimized)
        $totalAmount = Product::get()->reduce(function ($carry, $product) {
            $units = collect($product->unit_info);

            if ($units->isNotEmpty()) {
                $lastUnit = $units->last();
                return $carry + ($product->quantity * $lastUnit['purchase_price']);
            }

            return $carry;
        }, 0);

        // Calculate cash positions with clearer logic
        $customersCash = ($sale_total + $customerOpeningBalance) - ($customerCreditAmount + $sale_paid_amount + $invoicePayments); // 1500
        // dd($invoicePayments);
        $suppliersCash = ($purchaseAmount + $supplierOpeningBalance) - $supplierPayments; // 500
        //   dd($customerOpeningBalance);
        $operatingCash = $customersCash - $suppliersCash;  //  1500 - 500 = 1000
        $totalCash = $operatingCash - $total_expenses; // 1000 - 200 = 800

        // dd($totalAmount);

        // Calculate cash positions
        // $customersCash = ($sale_total + $customerDebitAmount) - $customerCreditAmount;
        // $suppliersCash = $purchaseAmount - $supplierPaymets;
        // $totalCash = ($totalAmount + $suppliersCash + $customersCash) - $total_expenses;

        // Get chart data
        $chartData = $this->getChartData('month');

        // Get employee data with their advances
        $employees = Employee::with(['advanceHistories', 'returnAdvances'])->get()->map(function ($employee) {
            $advance = $employee->advanceHistories->sum('current_paidAmount');
            $returned = $employee->returnAdvances->sum('return_amount');

            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'advance' => $advance,
                'returned' => $returned
            ];
        });

        return response()->json([
            'financialData' => [
                'sale_total' => $sale_total,
                'customerCreditAmount' => $customerCreditAmount,
                'total_expenses' => $total_expenses,
                'supplierPaymets' => $supplierPayments,
                'purchaseAmount' => $purchaseAmount,
                'totalAmount' => $totalAmount,
                'customersCash' => $customersCash,
                'suppliersCash' => $suppliersCash,
                'totalCash' => $totalCash,
                'currency' => optional(SiteSetting::where('key', 'currency')->first())->value ?? ''
            ],
            'summaryData' => [
                'countSales' => $countSales,
                'countPurchase' => $countPurchase,
                'countSupplier' => $countSupplier,
                'countCustomers' => $countCustomers,
                'countProducts' => $countProducts,
                'countEmployees' => $countEmployees,
            ],
            'employees' => $employees,
            'chartData' => $chartData
        ]);
    }

    public function getChartDataByPeriod(Request $request)
    {
        $period = $request->query('period', 'month');
        $chartData = $this->getChartData($period);

        return response()->json($chartData);
    }

    private function getChartData($period = 'month')
    {
        if ($period === 'year') {
            return $this->getYearlyChartData();
        } elseif ($period === 'week') {
            return $this->getWeeklyChartData();
        } else {
            return $this->getMonthlyChartData();
        }
    }

    private function getMonthlyChartData()
    {
        // Your existing monthly chart data calculation
        $purchase = Purchase::where('status', 'received')
            ->select(DB::raw("sum(grand_total) as sum"))
            ->whereYear('date', date('Y'))
            ->groupBy(DB::raw("Month(date)"))
            ->pluck('sum');

        $purchaseMonth = Purchase::where('status', 'received')
            ->select(DB::raw("Month(date) as month"))
            ->whereYear('date', date('Y'))
            ->groupBy(DB::raw("Month(date)"))
            ->pluck('month');

        $purchaseData = [];
        $purMonthNum = range(1, 12);
        $purJoiner = array_combine($purchaseMonth->toArray(), $purchase->toArray());

        foreach ($purMonthNum as $number) {
            $purchaseData[] = $purJoiner[$number] ?? 0;
        }

        // Calculate Sales Data
        $sales = Sale::where('status', 1)
            ->select(DB::raw("sum(grand_total) as sum"))
            ->whereYear('date', date('Y'))
            ->groupBy(DB::raw("Month(date)"))
            ->pluck('sum');

        $months = Sale::where('status', 1)
            ->select(DB::raw("Month(date) as month"))
            ->whereYear('date', date('Y'))
            ->groupBy(DB::raw("Month(date)"))
            ->pluck('month');

        $salesData = [];
        $monthNum = range(1, 12);
        $joiner = array_combine($months->toArray(), $sales->toArray());

        foreach ($monthNum as $number) {
            $salesData[] = $joiner[$number] ?? 0;
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'salesData' => $salesData,
            'purchaseData' => $purchaseData
        ];
    }

    private function getWeeklyChartData()
    {
        // Initialize arrays for all 52 weeks
        $weekLabels = [];
        $salesData = array_fill(0, 52, 0);
        $purchaseData = array_fill(0, 52, 0);

        // Get sales data grouped by week
        $sales = Sale::where('status', 1)
            ->select(
                DB::raw("WEEK(date, 1) as week"),
                DB::raw("sum(grand_total) as sum")
            )
            ->whereYear('date', date('Y'))
            ->groupBy(DB::raw("WEEK(date, 1)"))
            ->get();

        // Get purchase data grouped by week
        $purchases = Purchase::where('status', 'received')
            ->select(
                DB::raw("WEEK(date, 1) as week"),
                DB::raw("sum(grand_total) as sum")
            )
            ->whereYear('date', date('Y'))
            ->groupBy(DB::raw("WEEK(date, 1)"))
            ->get();

        // Fill sales data
        foreach ($sales as $sale) {
            if ($sale->week <= 52) {  // Ensure we don't exceed 52 weeks
                $salesData[$sale->week - 1] = $sale->sum;
            }
        }

        // Fill purchase data
        foreach ($purchases as $purchase) {
            if ($purchase->week <= 52) {  // Ensure we don't exceed 52 weeks
                $purchaseData[$purchase->week - 1] = $purchase->sum;
            }
        }

        // Generate week labels for all 52 weeks
        for ($i = 1; $i <= 52; $i++) {
            $weekLabels[] = "Week " . str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        return [
            'labels' => $weekLabels,
            'salesData' => $salesData,
            'purchaseData' => $purchaseData
        ];
    }

    private function getYearlyChartData()
    {
        // Get data for the last 5 years
        $currentYear = date('Y');
        $years = range($currentYear - 4, $currentYear);

        // Initialize arrays
        $salesData = [];
        $purchaseData = [];

        // Get sales data grouped by year
        $sales = Sale::where('status', 1)
            ->select(
                DB::raw("YEAR(date) as year"),
                DB::raw("sum(grand_total) as sum")
            )
            ->whereBetween('date', [($currentYear - 4) . '-01-01', $currentYear . '-12-31'])
            ->groupBy(DB::raw("YEAR(date)"))
            ->get()
            ->keyBy('year');

        // Get purchase data grouped by year
        $purchases = Purchase::where('status', 'received')
            ->select(
                DB::raw("YEAR(date) as year"),
                DB::raw("sum(grand_total) as sum")
            )
            ->whereBetween('date', [($currentYear - 4) . '-01-01', $currentYear . '-12-31'])
            ->groupBy(DB::raw("YEAR(date)"))
            ->get()
            ->keyBy('year');

        // Fill data arrays
        foreach ($years as $year) {
            $salesData[] = $sales[$year]->sum ?? 0;
            $purchaseData[] = $purchases[$year]->sum ?? 0;
        }

        return [
            'labels' => $years,
            'salesData' => $salesData,
            'purchaseData' => $purchaseData
        ];
    }
    public function dashboardxxx()
    {
        // $this->sendInvoice();
        $permissionCheck =  checkRolePermission($module_page = 'dashboard');

        $page_dashboard = "dashboard";
        Session::put('page', 'dashboard');

        /* Calculate Purchase Start */
        $purchase = Purchase::where('status', 'received')->select(DB::raw("sum(grand_total) as sum"))->whereYear('date', date('Y'))->groupBy(DB::raw("Month(date)"))->pluck('sum');

        $purchaseMonth = Purchase::where('status', 'received')->select(DB::raw("Month(date) as month"))->whereYear('date', date('Y'))->groupBy(DB::raw("Month(date)"))->pluck('month');

        $purchaseData = [];
        $purMonthNum = range(1, 12);
        $purJoiner = array_combine($purchaseMonth->toArray(), $purchase->toArray());
        foreach ($purMonthNum as $number) {
            $checkPurMonth = isset($purJoiner[$number]) ? $purJoiner[$number] : 0;
            if (isset($checkPurMonth) && ($checkPurMonth > 0)) {
                $purchaseData[] = $checkPurMonth;
            } else {
                $purchaseData[] = 0;
            }
        }
        /* Calculate Purchase Ends */

        /* Calculate Sale Start */
        $sales = Sale::where('status', 1)->select(DB::raw("sum(grand_total) as sum"))->whereYear('date', date('Y'))->groupBy(DB::raw("Month(date)"))->pluck('sum');

        $months = Sale::where('status', 1)->select(DB::raw("Month(date) as month"))->whereYear('date', date('Y'))->groupBy(DB::raw("Month(date)"))->pluck('month');

        $datas = [];
        $monthNum = range(1, 12);
        $joiner = array_combine($months->toArray(), $sales->toArray());
        foreach ($monthNum as $number) {
            $checkMonth = isset($joiner[$number]) ? $joiner[$number] : 0;
            if (isset($checkMonth) && ($checkMonth > 0)) {
                $datas[] = $checkMonth;
            } else {
                $datas[] = 0;
            }
        }
        /* Calculate Sale Ends */
        $settings = SiteSetting::pluck('value', 'key');
        $employees = Employee::get()->toArray();

        return view('dashboard', compact('page_dashboard', 'datas', 'purchaseData', 'employees', 'permissionCheck', 'settings'));
    }
    /*===================================================*/
    public function logout()
    {
        Session::flush();
        return redirect('/')->with('flash_message_success', 'Logout Successfully');
    }
    /*===================================================*/
    public function  viewUsers()
    {
        $this->authenticateRole($module_page = 'users');
        try {
            Session::put('page', 'viewUser');

            return view('users.view');
        } catch (Exception $e) {
            Session::flash('flash_message_error', "Oops, Something went wrong. Try again");
            return redirect()->back()->with($e->getMessage());
        }
    }
    /********************************************************************/
    public function usersList(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with(['user:id,name', 'group:id,name'])
                ->select(
                    'users.id', // Explicitly specify the table for 'id'
                    'users.group_id',
                    'users.name',
                    'users.email',
                    'users.mobile',
                    'users.status',
                    'users.createdBy'
                )
                ->orderBy('users.id', 'desc'); // Order by 'id' in descending order
            // ->get();

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('role', function ($row) {
                    return $row->group ? $row->group->name : '';
                })

                ->addColumn('status', function ($row) {
                    return $row->status == 1  ? 'Active' : 'InActive';
                })

                ->addColumn('createdBy', function ($row) {
                    return $row->user ? $row->user->name : '';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('user.profile', $row->id) . '" class="btn btn-info btn-sm" title="Profile">
                            <i class="fas fa-user"></i>
                        </a>
                        <a href="' . route('user.update', $row->id) . '" class="btn btn-info btn-sm" title="Edit?">
                            <i class="fas fa-pen"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm delete" data-url="' . route('user.delete', $row->id) . '" data-id="' . $row->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>';
                })
                ->rawColumns(['action']) // Ensure raw HTML rendering for the 'action' column
                ->make(true);
        }
    }
    /*===================================================*/
    public function addUser(Request $request)
    {
        $this->authenticateRole($module_page = 'users');
        if ($request->isMethod('post')) {

            $data = $request->all();
            /*Laravel Validation Start...................*/
            $rules = [
                'name' => 'required|regex:/^[\pL\s\-]+$/u',
                'email' => 'required|unique:users'
            ];
            $customMessage = [
                'name.required' => 'user name is required',
                'name.regex' => 'valid user name is required',
                'email.required' => 'email is required',
                'email.unique' => 'email must be unique'

            ];
            $this->validate($request, $rules, $customMessage);
            /*Laravel Validation End...................*/
            try {
                $admin = new User();
                $admin->name = $data['name'];
                // $admin->username = $data['username'] ?? Str::slug($data['name']);
                $admin->group_id = $data['group_id'];
                $admin->email  = $data['email'];
                $admin->address  = $data['address'];
                $admin->password = Hash::make($data['password']);
                $admin->code = $data['password'];

                $admin->mobile = $data['mobile'];
                $admin->save();
                return redirect('/view-users')->with('success', 'User Successfully Added!');
            } catch (Exception $e) {
                Session::flash('flash_message_error', $e->getMessage());
                return redirect()->back()->with($e->getMessage());
            }
        } else {
            $groups = Group::get();
            Session::put('page', 'addUser');
            return view('users.create', compact('groups'));
        }
    }
    /*===================================================*/
    public function editUser(Request $request, $id = null)
    {

        $this->authenticateRole($module_page = 'users');
        if ($request->isMethod('post')) {

            $data = $request->all();
            try {
            $editAdmin = User::find($id);
            $editAdmin->name = $data['name'];
            $editAdmin->group_id = $data['group_id'];
            $editAdmin->mobile = $data['mobile'];
            $editAdmin->address = $data['address'];
            $editAdmin->save();
            return redirect('/view-users')->with('success', 'User Successfully Updated!');
            } catch (Exception $e) {
                Session::flash('flash_message_error', $e->getMessage());
                return redirect()->back()->with($e->getMessage());
            }
        } else {

            $editAdmin = User::find($id);
            $groups = Group::get();
            // echo "<pre>"; print_r($editAdmin->toArray()); exit();
            return view('users.create', compact('editAdmin', 'groups'));
        }
    }
    /*===================================================*/
    public function deleteUser(Request $request, $id = null)
    {
        if ($request->ajax()) {
            try {
                $user = User::find($id);
                $user->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'User Deleted Successfully'
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops, something went wrong. Try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
}
