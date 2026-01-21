<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use App\Models\Purchase, App\Models\PurchasePayment;
use App\Models\Expense;
use App\Models\Sale, App\Models\SalePaymentDetail;
use App\Models\AdvanceCustomerPayment;
use App\Models\CustomerOpeningBalance;
use App\Models\ReceivablePayable;
use App\Models\SupplierPayment;
use App\Models\SupplierOpeningBalance;
use App\Models\MonthlySalary;
use App\Models\AdvanceHistory;
use App\Models\EmployeeReturnAdvance;
use App\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProfitLossController extends Controller
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
    public function profitLoss_04_09_25(Request $request)
    {
        $this->authenticateRole('reports');

        // 1. Date Range Handling
        $start = Carbon::parse($request->startDate ?? today())->startOfDay();
        $end = Carbon::parse($request->endDate ?? today())->endOfDay();

        // 2. Main Query Execution
        $results = DB::transaction(function () use ($start, $end) {
            return [
                // Financial Aggregates
                'payroll' => DB::table('monthly_salaries')->whereBetween('created_at', [$start, $end])->sum('amount'),
                'advances' => DB::table('advance_histories')->whereBetween('date', [$start, $end])->sum('current_paidAmount'),
                'advancesReturned' => DB::table('employee_return_advances')->whereBetween('date', [$start, $end])->sum('return_amount'),

                // Purchases with additional charges and discount
                'purchases' => DB::table('purchases')
                    ->selectRaw('
                    SUM(grand_total) as grand_total,
                    SUM(other_charges) as other_charges,
                    SUM(discount_amount) as discount_amount
                ')
                    ->where('status', 'received')
                    ->whereBetween('date', [$start, $end])
                    ->first(),

                'supplierPayments' => DB::table('supplier_payments')->whereBetween('date', [$start, $end])->sum('amount'),
                'expenses' => DB::table('expenses')->whereBetween('date', [$start, $end])->sum('amount'),

                // Sales with additional charges
                'salesAgg' => DB::table('sales')
                    ->selectRaw('
                    SUM(grand_total) as total_sales,
                    SUM(paid_amount) as paid_sales,
                    SUM(discount_amount) as discount_total,
                    SUM(other_charges) as other_charges
                ')
                    ->where('status', 1)
                    ->whereBetween('date', [$start, $end])
                    ->first(),

                'partialPaidInvoiceAmount' => DB::table('customer_payments')
                    ->whereBetween('payment_date', [$start, $end])
                    ->sum('amount'),

                // Customer Opening Balances (period-specific)
                'custOB' => DB::table('customer_opening_balances')

                    ->whereBetween('date', [$start, $end])
                    ->sum('amount'),

                // Total Customer Opening Balances (all-time)
                'totalCustOB' => DB::table('customers')
                    ->selectRaw('SUM(opening_balance) as total')
                    ->first(),

                // Total Supplier Opening Balances (all-time)
                'totalSuppOB' => DB::table('suppliers')
                    ->selectRaw('SUM(opening_balance) as total')
                    ->first()
            ];
        });

        // 3. COGS Calculation (Optimized)
        $cogs = Sale::where('status', 1)
            ->whereBetween('date', [$start, $end])
            ->get(['items_addon'])
            ->sum(function ($sale) {
                $items = unserialize($sale->items_addon);
                return is_array($items) ? collect($items)->sum('calculatedCost') : 0;
            });

        // 4. Profit Calculations with Opening Balances and Charges
        $totalSales = $results['salesAgg']->total_sales
            - $results['salesAgg']->discount_total
            + $results['salesAgg']->other_charges;

        $totalPurchases = $results['purchases']->grand_total
            + $results['purchases']->other_charges
            - $results['purchases']->discount_amount; // Subtract purchase discount


        $grossProfit = ($results['salesAgg']->total_sales - ($results['salesAgg']->other_charges - $results['salesAgg']->discount_total)) - $cogs;
        // $grossProfit = ($results['salesAgg']->total_sales - $results['salesAgg']->discount_total) - $cogs;
        // dd($results['salesAgg']->other_charges - $results['salesAgg']->other_charges);
        // dd($results['payroll']);
        $netPayroll = $results['payroll'] + $results['advances'] - $results['advancesReturned'];
        $operatingExpense = $netPayroll + $results['expenses'];
        $netProfit = $grossProfit - $operatingExpense;

        // 5. Cash Flow Calculation Including Opening Balances and Charges
        $cashFromCustomers = $results['salesAgg']->paid_sales
            + ($results['custOB'] ?? 0)
            + ($results['partialPaidInvoiceAmount'] ?? 0);
        // + ($results['totalCustOB']->total ?? 0);

        $cashToSuppliers = $results['supplierPayments'];
        // + ($results['totalSuppOB']->total ?? 0);
        // dd($results['custOB']->credit);

        // dd($results['partialPaidInvoiceAmount']);
        $netCashMovement = ($cashFromCustomers) - ($cashToSuppliers + $operatingExpense);

        // 6. Get Additional Data
        $salesRows = Sale::where('status', 1)
            ->whereBetween('date', [$start, $end])
            ->get(['id', 'date', 'invoice_no', 'items_addon', 'other_charges']);

        $settings = SiteSetting::pluck('value', 'key');

        return view('reports.profit_loss.index', [
            // Core Metrics
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'netCashMovement' => $netCashMovement,
            'operatingExpense' => $operatingExpense,

            // Detailed Data
            'sales' => $salesRows,
            'salesAgg' => $results['salesAgg'],
            'purchases' => $results['purchases'],
            'custOpening' => $results['custOB'],
            'totalCustOpening' => $results['totalCustOB']->total ?? 0,
            'totalSuppOpening' => $results['totalSuppOB']->total ?? 0,
            'payroll' => $netPayroll,
            'expenses' => $results['expenses'],
            'supplierPayments' => $results['supplierPayments'],

            // Legacy View Variables
            'getTotalPurchaseAmount' => $results['purchases']->grand_total,
            'employees_salarie' => $results['payroll'],
            'getCashSalesAmount' => $results['salesAgg']->paid_sales,
            // 'getCreditSalesAmount' => $results['salesAgg']->credit_sales,
            'getTotalSalesAmount' => $results['salesAgg']->total_sales,
            'salesDiscount' => $results['salesAgg']->discount_total,
            'returnGrossProfit' => $grossProfit,

            // Additional Charges
            'salesOtherCharges' => $results['salesAgg']->other_charges,
            'purchaseOtherCharges' => $results['purchases']->other_charges,
            'purchaseDiscount' => $results['purchases']->discount_amount,

            // Metadata
            'startDate' => $start,
            'endDate' => $end,
            'settings' => $settings,
            'currency' => $settings['currency'] ?? '',
        ]);
    }

    public function profitLoss(Request $request)
    {
        $this->authenticateRole('reports');

        // 1. Date Range Handling
        $start = Carbon::parse($request->startDate ?? today())->startOfDay();
        $end = Carbon::parse($request->endDate ?? today())->endOfDay();

        // 2. Main Query Execution with optimized queries
        $results = DB::transaction(function () {
            return [
                // Financial Aggregates
                'payroll' => DB::table('monthly_salaries')
                    ->sum('amount'),

                'advances' => DB::table('advance_histories')
                    ->sum('current_paidAmount'),

                'advancesReturned' => DB::table('employee_return_advances')
                    ->sum('return_amount'),

                // Purchases with additional charges and discount
                'purchases' => DB::table('purchases')
                    ->selectRaw('
                    SUM(grand_total) as grand_total,
                    SUM(other_charges) as other_charges,
                    SUM(discount_amount) as discount_amount
                ')
                    ->where('status', 'received')
                    ->first(),

                'supplierPayments' => DB::table('supplier_payments')
                    ->sum('amount'),

                'expenses' => DB::table('expenses')
                    ->sum('amount'),

                // Sales with additional charges
                'salesAgg' => DB::table('sales')
                    ->selectRaw('
                    SUM(grand_total) as total_sales,
                    SUM(paid_amount) as paid_sales,
                    SUM(discount_amount) as discount_total,
                    SUM(other_charges) as other_charges
                ')
                    ->where('status', 1)
                    ->first(),

                'partialPaidInvoiceAmount' => DB::table('customer_payments')
                    ->sum('amount'),

                // Customer Opening Balances (period-specific)
                'custOB' => DB::table('customer_opening_balances')
                    ->sum('amount'),

                // Total Customer Opening Balances (all-time)
                'totalCustOB' => DB::table('customers')
                    ->sum('opening_balance'),

                // Total Supplier Opening Balances (all-time)
                'totalSuppOB' => DB::table('suppliers')
                    ->sum('opening_balance')
            ];
        });
        $customerRemainingOPBlnc = $results['totalCustOB'] - $results['custOB'];

        // 3. COGS Calculation (Optimized with direct query)
        $cogs = DB::table('sales')
            ->where('status', 1)
            ->get(['items_addon'])
            ->reduce(function ($carry, $sale) {
                $items = unserialize($sale->items_addon);
                return $carry + (is_array($items) ? collect($items)->sum('calculatedCost') : 0);
            }, 0);

        // 4. Profit Calculations with Opening Balances and Charges
        $totalSales = ($results['salesAgg']->total_sales ?? 0);

        $totalPurchases = ($results['purchases']->grand_total ?? 0);
// dd($cogs);
        $grossProfit = $totalSales - $cogs;
        $netPayroll = ($results['payroll'] ?? 0) + ($results['advances'] ?? 0) - ($results['advancesReturned'] ?? 0);
        $operatingExpense = $netPayroll + ($results['expenses'] ?? 0);
        $netProfit = $grossProfit - $operatingExpense;

        // 5. Cash Flow Calculation Including Opening Balances and Charges
        $cashFromCustomers = ($results['salesAgg']->paid_sales ?? 0)
            + ($results['custOB'] ?? 0)
            + ($results['partialPaidInvoiceAmount'] ?? 0);

        $cashToSuppliers = $results['supplierPayments'] ?? 0;
        $netCashMovement = $cashFromCustomers - ($cashToSuppliers + $operatingExpense);

        // 6. Get Additional Data (only needed fields)
        $sales = Sale::where('status', 1)
            ->get(['id', 'date', 'invoice_no', 'items_addon']);

        $settings = SiteSetting::pluck('value', 'key');


        return view('reports.profit_loss.index', compact(
            'grossProfit',
            'netProfit',
            'netCashMovement',
            'operatingExpense',
            'cogs',
            'totalSales',
            'totalPurchases',
            'netPayroll',
            'start',
            'end',
            'settings'
        ) + [
            'startDate' => $start,
            'endDate' => $end,
            'sales' => $sales,
            'customerRemainingOPBlnc' => $customerRemainingOPBlnc,
            'salesAgg' => $results['salesAgg'],
            'purchases' => $results['purchases'],
            'custOpening' => $results['custOB'],
            'totalCustOpening' => $results['totalCustOB'],
            'totalSuppOpening' => $results['totalSuppOB'],
            'expenses' => $results['expenses'],
            'supplierPayments' => $results['supplierPayments'],
            'getTotalPurchaseAmount' => $results['purchases']->grand_total ?? 0,
            'employees_salarie' => $results['payroll'],
            'getCashSalesAmount' => $results['salesAgg']->paid_sales ?? 0,
            'getTotalSalesAmount' => $results['salesAgg']->total_sales ?? 0,
            'salesDiscount' => $results['salesAgg']->discount_total ?? 0,
            'returnGrossProfit' => $grossProfit,
            'salesOtherCharges' => $results['salesAgg']->other_charges ?? 0,
            'purchaseOtherCharges' => $results['purchases']->other_charges ?? 0,
            'purchaseDiscount' => $results['purchases']->discount_amount ?? 0,
            'currency' => $settings['currency'] ?? ''
        ]);
    }

    public function profitLoss_05_07_2025(Request $request)
    {
        $this->authenticateRole('reports');

        // Set date range
        $startDate = $request->startDate ? date('Y-m-d 00:00:00', strtotime($request->startDate)) : date('Y-m-d 00:00:00');
        $endDate = $request->endDate ? date('Y-m-d 23:59:59', strtotime($request->endDate)) : date('Y-m-d 23:59:59');

        // Fetch all required amounts in a single step
        $data = [
            'employees_salarie'        => MonthlySalary::whereBetween('date', [$startDate, $endDate])->sum('amount'),
            'employees_advance'       => AdvanceHistory::whereBetween('date', [$startDate, $endDate])->sum('current_paidAmount'),
            'employees_return_advance' => EmployeeReturnAdvance::whereBetween('date', [$startDate, $endDate])->sum('return_amount'),
            'getTotalPurchaseAmount'   => Purchase::whereBetween('date', [$startDate, $endDate])->where('status', 'received')->sum('grand_total'),
            'suppliersPayment'         => SupplierPayment::whereBetween('date', [$startDate, $endDate])->sum('amount'),
            'expenses'                 => Expense::whereBetween('date', [$startDate, $endDate])->sum('amount'),
            'getTotalSalesAmount'      => Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->sum('grand_total'),
            'getCashSalesAmount'       => Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->where('payment_type', 'cash')->sum('grand_total'),
            'getCreditSalesAmount'     => Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->where('payment_type', 'credit')->sum('grand_total'),
            'salesDiscount'            => Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->sum('discount_amount'),
        ];

        // Fetch customer opening balances in a single query
        $customerBalances = CustomerOpeningBalance::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as credit_amount')
            ->selectRaw('SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as debit_amount')
            ->first();

        $data['customers_credit_amount'] = $customerBalances->credit_amount ?? 0;
        $data['customers_debit_amount'] = $customerBalances->debit_amount ?? 0;

        // Fetch sales data efficiently
        $sales = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->pluck('items_addon');

        $getCalculatedCostAmount = 0;
        $getItemsTotalAmount = 0;

        foreach ($sales as $itemsAddon) {
            $product_addons = unserialize($itemsAddon);

            foreach ($product_addons as $addon) {
                $getCalculatedCostAmount += $addon['calculatedCost'];
                $getItemsTotalAmount += $addon['amount'];
            }
        }

        // Gross profit calculation
        $totalGrossProfit = $getItemsTotalAmount - $getCalculatedCostAmount;
        $returnGrossProfit = $totalGrossProfit - $data['salesDiscount'];

        // Net profit calculation
        $dataCashOut = $data['suppliersPayment'] + $data['customers_debit_amount'] + $data['expenses'];
        // dd($returnGrossProfit);
        $netProfit = $returnGrossProfit - (float)$dataCashOut;

        // Fetch products related to sales
        $data['sales'] = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->get(['id', 'date', 'invoice_no', 'items_addon'])
            ->toArray();

        // Fetch site settings
        $settings = SiteSetting::pluck('value', 'key');

        return view('reports.profit_loss.index', $data, compact('returnGrossProfit', 'netProfit', 'startDate', 'endDate', 'settings'));
    }

    public function profitLossxxxxxxxxxxxxxxxxxxx(Request $request)
    {
        $this->authenticateRole('reports');

        $startDate = $request->startDate ? date('Y-m-d 00:00:00', strtotime($request->startDate)) : date('Y-m-d 00:00:00');
        $endDate = $request->endDate ? date('Y-m-d 23:59:59', strtotime($request->endDate)) : date('Y-m-d 00:00:00');

        // Fetch all required amounts in a single step
        $data = [
            'employees_salarie'            => MonthlySalary::whereBetween('date', [$startDate, $endDate])->sum('amount'),
            'employees_advance'            => AdvanceHistory::whereBetween('date', [$startDate, $endDate])->sum('current_paidAmount'),
            'employees_return_advance'     => EmployeeReturnAdvance::whereBetween('date', [$startDate, $endDate])->sum('return_amount'),
            'getTotalPurchaseAmount'       => Purchase::whereBetween('date', [$startDate, $endDate])->where('status', 'received')->sum('grand_total'),
            'suppliersPayment'             => SupplierPayment::whereBetween('date', [$startDate, $endDate])->sum('amount'),
            'customers_credit_amount'      => CustomerOpeningBalance::whereBetween('date', [$startDate, $endDate])->where('type', 'credit')->sum('amount'),
            'customers_debit_amount'       => CustomerOpeningBalance::whereBetween('date', [$startDate, $endDate])->where('type', 'debit')->sum('amount'),
            'expenses'                     => Expense::whereBetween('date', [$startDate, $endDate])->sum('amount'),
            'getTotalSalesAmount'          => Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->sum('grand_total'),
            'salesDiscount'                => Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->sum('discount_amount'),
        ];

        // Fetch sales data efficiently
        $sales = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->pluck('items_addon'); // Fetch only the necessary column
        // echo "<pre>"; print_r($sales); exit;

        $getCalculatedCostAmount = 0;
        $getItemsTotalAmount = 0;

        foreach ($sales as $itemsAddon) {
            $product_addons = unserialize($itemsAddon);

            foreach ($product_addons as $addon) {
                $getCalculatedCostAmount += $addon['calculatedCost'];
                $getItemsTotalAmount += $addon['amount'];
            }
        }

        // Gross profit calculation
        $totalGrossProfit = $getItemsTotalAmount - $getCalculatedCostAmount;
        $returnGrossProfit = $totalGrossProfit - $data['salesDiscount'];

        // Net profit calculation
        $dataCashOut = $data['suppliersPayment'] + $data['customers_debit_amount'] + $data['expenses'];
        $netProfit = $data['customers_credit_amount'] - (float)$dataCashOut;

        // Fetch products related to sales
        $data['sales'] = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', 1)
            ->get(['id', 'date', 'invoice_no', 'items_addon']) // Fetch only needed fields
            ->toArray();
        $settings = SiteSetting::pluck('value', 'key');
        return view('reports.profit_loss.index', $data, compact('returnGrossProfit', 'netProfit', 'startDate', 'endDate', 'settings'));
    }

    public function profitLossxx(Request $request)
    {
        $this->authenticateRole($module_page = 'reports');
        Session::put('page', 'profitLoss');
        $data = $request->all();
        $startDate = date('Y-m-d H:i:s', strtotime($data['startDate'] . ' 00:00:00'));
        $endDate = date('Y-m-d H:i:s', strtotime($data['endDate'] . ' 23:59:59'));

        /*SUM Employees Salarie*/
        $data['employees_salarie'] = MonthlySalary::whereBetween('date', [$startDate, $endDate])->sum('amount'); //DB Amount
        /*SUM Employees Advance*/
        $data['employees_advance']  = AdvanceHistory::whereBetween('date', [$startDate, $endDate])->sum('current_paidAmount'); //DB Amount
        /*SUM Employees Return Advance*/
        $data['employees_return_advance'] = EmployeeReturnAdvance::whereBetween('date', [$startDate, $endDate])->sum('return_amount'); //CR Amount


        /*Fetch Total Amount of Purchase*/
        $data['getTotalPurchaseAmount'] = Purchase::whereBetween('date', [$startDate, $endDate])->where('status', 'received')->sum('amount');

        /*SUM Suppliers CR/DB Amounts*/
        $data['suppliersPayment'] = SupplierPayment::whereBetween('date', [$startDate, $endDate])->sum('amount'); //DB



        /*SUM Customers CR/DB Amount*/

        $data['customers_credit_amount'] = CustomerOpeningBalance::whereBetween('date', [$startDate, $endDate])->where('type', 'credit')->sum('amount'); //CR
        $data['customers_debit_amount'] = CustomerOpeningBalance::whereBetween('date', [$startDate, $endDate])->where('type', 'debit')->sum('amount'); //DB


        /*Fetch Expenses*/
        $data['expenses'] = Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');

        /*Fetch Total Amount of Sales*/
        $data['getTotalSalesAmount'] = Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->sum('amount');
        /*Fetch Total Paid Amount of Sales*/
        $data['salesDiscount'] = Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->sum('discount');

        $data['customerPaymentDiscount'] = AdvanceCustomerPayment::whereBetween('date', [$startDate, $endDate])->sum('payment_discount');




        $getItemsTotalAmount = 0;
        $returnItemsTotalAmount = 0;

        $getCalculatedCostAmount = 0;
        $returnCalculatedCostAmount = 0;

        $grossProfit = 0;
        $totalGrossProfit = 0;
        $returnGrossProfit = 0;


        /*Fetch Calculations of Sales*/
        $getSales = Sale::where('status', 1)->get()->toArray();
        // echo "<pre>"; print_r($getSales); exit();


        foreach ($getSales as $key => $value) {
            //     $customerSale = $value['pos_sale'];

            // foreach ($customerSale as $key => $value) {

            /*Fetch Calculated Cost & Items Total for Gross Profit*/
            $product_addons = unserialize($value['items_addon']);

            foreach ($product_addons as $key => $addons) {
                $calculatedCost = $addons['calculatedCost'];
                $itemsTotal = $addons['amount'];
                $quantity = $addons['quantity'];
                $getCalculatedCostAmount = $calculatedCost + $getCalculatedCostAmount;
                $getItemsTotalAmount = $itemsTotal + $getItemsTotalAmount;

                $grossProfit = $getItemsTotalAmount - $getCalculatedCostAmount;
                $totalGrossProfit = $grossProfit;
            }
        }
        // }

        /*Calculate Gross Profit*/
        $returnGrossProfit = $totalGrossProfit - ($data['salesDiscount'] + $data['customerPaymentDiscount']);


        /*Calculate Net Profit*/
        $dataCashOut = $data['suppliersPayment'] + $data['customers_debit_amount'] + $data['expenses'];

        $netProfit = ($data['customers_credit_amount']) - (float)$dataCashOut;
        // dd($netProfit);
        /*Fetch Items wise Profit*/
        $data['products'] = Sale::whereBetween('date', [$startDate, $endDate])->where('status', 1)->get()->toArray();
        // echo "<pre>"; print_r($data['products'][0]['items_addon']); exit();

        $settings = SiteSetting::pluck('value', 'key');
        return view('reports.profit_loss.index', $data, compact('returnGrossProfit', 'netProfit', 'startDate', 'endDate', 'settings'));
    }
    /*==============================================================================*/
}
