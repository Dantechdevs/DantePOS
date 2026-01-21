<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\SiteSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    if (Auth::check()) {
        // Redirect to dashboard if logged in
        return redirect()->route('dashboard');
    }
    $settings = SiteSetting::pluck('value', 'key')->toArray();
    return view('login', compact('settings'));
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/cache-clear', function () {
    if (app()->environment('production')) {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
    } else {
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('key:generate');
    }


    return redirect()->route('dashboard')->with('flash_message_success', 'Cache cleared successfully');
})->name('cache.clear');

Route::post('/login', 'AdminController@login');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/dashboard', 'AdminController@dashboard')->name('dashboard');
    Route::get('/dashboard/data', 'AdminController@dashboardData')->name('dashboard.data');
    Route::get('/dashboard/chart-data', 'AdminController@getChartDataByPeriod')->name('dashboard.chart.data');
    Route::get('/logout', 'AdminController@logout');

    // Daily Activity ....
    Route::get('daily/activity', 'DailyActivityController@index');
    Route::get('backup', 'BackupController@index')->name('backup.page');
    Route::post('backup', 'BackupController@backup')->name('take.backup');
    Route::post('restore', 'BackupController@restore')->name('restore');

    //User Roles Routes...
    Route::get('roles', 'GroupController@index')->name('roles');
    Route::get('roles/list', 'GroupController@rolesList')->name('roles.list');
    Route::match(['get', 'post'], '/add-role', 'GroupController@addRole')->name('create.role');
    Route::match(['get', 'post'], '/edit-role/{id}', 'GroupController@editRole')->name('update.role');
    Route::delete('delete-role/{id}', 'GroupController@deleteRole')->name('delete.role');
    // Users Route.....
    Route::get('/view-users', 'AdminController@viewUsers')->name('users');
    Route::get('/users/list', 'AdminController@usersList')->name('users.list');
    Route::match(['get', 'post'], '/add-user', 'AdminController@addUser')->name('user.create');
    Route::match(['get', 'post'], '/edit-user/{id}', 'AdminController@editUser')->name('user.update');
    Route::delete('/delete-user/{id}', 'AdminController@deleteUser')->name('user.delete');
    // User Profile Route.....
    Route::get('/user-profile/{userId?}', 'ProfileController@index')->name('user.profile');
    Route::match(['get', 'post'], '/edit-user-profile/{id}', 'ProfileController@editUser')->name('edit.user.profile');
    Route::match(['get', 'post'], '/update-password', 'ProfileController@updatePassword');
    Route::post('/check-current-pwd', 'ProfileController@checkCurrentPassword');



    // Customers Routes.......
    Route::get('/customers', 'CustomerController@index')->name('customers');
    Route::get('/customers/list', 'CustomerController@customersList')->name('customers.list');
    Route::get('/customer/view/{id}', 'CustomerController@customerView')->name('customer.view');
    Route::get('load-customer-form/{id?}', 'CustomerController@loadCustomerFrom')->name('load.customer.form');
    Route::post('/add-customer/{id?}', 'CustomerController@addCustomer')->name('create.customer');
    Route::get('/customer-auto-complete', 'CustomerController@searchCustomer');
    Route::delete('/delete-customer/{id}', 'CustomerController@deleteCustomer')->name('delete.customer');

    //Customer Payment Route....
    Route::get('/customer/payments', 'CustomerController@customerPayments')->name('customer.payments');
    Route::get('/customer/payments/list', 'CustomerController@customerPaymentsList')->name('customer.payments.list');
    Route::get('/customer/payments/view/{id}', 'CustomerController@customerPaymentsView')->name('customer.payments.view');
    Route::get('load-customer-payment-form/{id?}', 'CustomerController@loadCustomerPaymentFrom')->name('load.customer.payment.form');
    Route::post('/store/customer/payment/{id?}', 'CustomerController@storeCustomerPayment')->name('store.customer.payment');
    Route::delete('/delete-customer-payment/{id}', 'CustomerController@deleteCustomerPayment')->name('delete.customer.payment');
    Route::get('/customer/payment-modal/{id}', 'CustomerController@showPaymentModal')->name('customer.payment.modal');
    Route::post('/customer/payment-submit', 'CustomerController@submitPayment')->name('customer.payment.submit');
    Route::get('/customer/{id}/attachments', 'CustomerController@getAttachments')->name('customer.attachments');
    Route::get('/inactive-customers', 'CustomerController@getInactiveCustomers');

    // Suppliers Routes.......
    Route::get('/suppliers', 'SupplierController@index')->name('suppliers');
    Route::get('/suppliers/list', 'SupplierController@suppliersList')->name('suppliers.list');
    Route::get('/supplier/view/{id}', 'SupplierController@supplierView')->name('supplier.view');
    Route::get('load-supplier-form/{id?}', 'SupplierController@loadSupplierFrom')->name('load.supplier.form');
    Route::post('create-supplier/{id?}', 'SupplierController@createSupplier')->name('create.supplier');
    Route::delete('/delete-supplier/{id}', 'SupplierController@deleteSupplier')->name('delete.supplier');
    Route::get('/supplier-auto-complete', 'SupplierController@searchSupplier');
    Route::get('/suppliers-by-day/{day}', 'SupplierController@getSuppliersByDay');

    //Supplier Payment Route....
    Route::get('supplier/payments', 'SupplierController@supplierPayment')->name('supplier.payments');
    Route::get('/supplier/payments/list', 'SupplierController@supplierPaymentsList')->name('supplier.payments.list');
    Route::get('/supplier/payments/view/{id}', 'SupplierController@supplierPaymentsView')->name('supplier.payments.view');
    Route::get('load-supplier-payment-form/{id?}', 'SupplierController@loadSupplierPaymentFrom')->name('load.supplier.payment.form');
    Route::post('/add-supplier-payment/{id?}', 'SupplierController@addSupplierPayment')->name('store.supplier.payment');
    Route::delete('/delete-supplier-payment/{id}', 'SupplierController@deleteSupplierPayment')->name('delete.supplier.payment');


    // Expenses Routes.....
    Route::get('/expenses', 'ExpenseController@index')->name('expenses');
    Route::get('/expenses/list', 'ExpenseController@expensesList')->name('expenses.list');
    Route::match(['get', 'post'], '/add-expense', 'ExpenseController@addExpenses')->name('store.expense');
    Route::match(['get', 'post'], '/edit-expense/{id}', 'ExpenseController@editExpenses')->name('update.expense');
    Route::get('/expense-categories', 'ExpenseController@expenseCategories')->name('expense.categories');
    Route::get('/expense/categories/list', 'ExpenseController@expenseCategoriesList')->name('expense.categories.list');
    Route::match(['get', 'post'], '/add-expense-category', 'ExpenseController@addExpCat')->name('store.expense.category');
    Route::delete('/delete-expense/{id}', 'ExpenseController@deleteExpense')->name('delete.expense');
    Route::delete('/delete-expCate/{id}', 'ExpenseController@deleteExpCategory')->name('delete.expense.category');
    // Unit Routes.....
    Route::get('/units', 'UnitController@units')->name('units');
    Route::get('/units/list', 'UnitController@unitsList')->name('units.list');
    Route::get('/unit/{id}', 'UnitController@singleUnit')->name('single.unit');
    Route::post('/unit/store/{id?}', 'UnitController@unitStore')->name('unit.store');
    Route::delete('/unit/delete/{id}', 'UnitController@unitDelete')->name('unit.delete');

    // Settings Routes.....
    Route::get('general/site-settings', 'GeneralSettingsController@index')->name('site.settings');
    Route::post('general/update-site-settings', 'GeneralSettingsController@updateSiteSettings')->name('update.site.settings');

    // Area Routes.....
    Route::get('/areas', 'AreaController@areas')->name('areas');
    Route::get('/areas/list', 'AreaController@areasList')->name('areas.list');
    Route::get('/area/{id}', 'AreaController@singleArea')->name('single.area');
    Route::post('/area/store/{id?}', 'AreaController@areasStore')->name('area.store');
    Route::delete('/area/delete/{id}', 'AreaController@areaDelete')->name('area.delete');

    /* Employees Routes Starts Here */
    Route::namespace('Employee')->group(function () {
        Route::get('/employees', 'EmployeeController@index')->name('employees');
        Route::get('/employees/list', 'EmployeeController@employeesList')->name('employees.list');
        Route::match(['get', 'post'], '/create-employee', 'EmployeeController@create')->name('create.employee');
        Route::match(['get', 'post'], '/edit-employee/{id}', 'EmployeeController@edit')->name('update.employee');
        Route::get('/employee-details/{id}', 'EmployeeController@employeeDetails')->name('employee.details');
        Route::delete('/delete-employee/{id}', 'EmployeeController@deleteEmployee')->name('delete.employee');
        //Employee Advance Route....
        // Route::match(['get','post'],'/employee-advance-salary/{id}','EmployeeAdvanceController@advanceSalary');
        Route::post('/employee-advance-salary/{id}', 'EmployeeAdvanceController@advanceSalary')->name('employee.advance');
        Route::get('/employee-advances/{employee_id}', 'EmployeeAdvanceController@getEmployeeAdvances')->name('employee.advances');
        Route::delete('/delete-advance/{id}', 'EmployeeAdvanceController@deleteAdvance')->name('advance.delete');
        // Monthly Employee Salary Increment Route....
        Route::match(['get', 'post'], '/monthly-employee-salary-increment/{id}', 'MonthlyEmployeeIncrementController@increment')->name('employee.increment');
        // Monthly Employee Return Advance Route...
        // Route::match(['get','post'],'/monthly-employee-return-advance/{id}','EmployeeController@returnAdvance');
        Route::post('/monthly-employee-return-advance/{id}', 'EmployeeController@returnAdvance')->name('employee.return.advance');
        Route::get('/employee-return-advances/{employee_id}', 'EmployeeController@getEmployeeReturnAdvances')->name('employee.return.advances');
        Route::delete('/delete-return-advance/{id}', 'EmployeeController@deleteReturnAdvance')->name('return.advance.delete');

        /* Employee Leave Routes Starts */
        Route::get('/employees-leaves', 'LeaveController@index')->name('employees.leaves');
        Route::match(['get', 'post'], '/add-employee-leave', 'LeaveController@addLeave');
        Route::match(['get', 'post'], '/edit-employee-leave/{id}', 'LeaveController@editLeave');
        /* Employee Leave Routes Ends */

        /* Employee Attendance Routes Starts */
        Route::get('/employees-attendance', 'AttendanceController@index')->name('employees.attendance');
        Route::match(['get', 'post'], '/add-employee-attendance', 'AttendanceController@create');
        Route::match(['get', 'post'], '/edit-employee-attendance/{date}', 'AttendanceController@editAttendance');
        Route::get('/employee-attendance-details/{date}', 'AttendanceController@attendanceDetails');

        /* Employee Attendance Routes Ends */

        /* Employee Monthly Salary Routes Starts */
        Route::get('/employee-monthly-salary', 'MonthlySalaryController@view')->name('employee.salaries');
        Route::get('/employee-monthly-salary/list', 'MonthlySalaryController@salaryList')->name('employee.salary.list');
        Route::get('/employee-monthly-salary-datewise-get', 'MonthlySalaryController@monthlySalaryDatewiseGet');
        Route::get('/employee-monthly-salary-payslip/{employee_id}', 'MonthlySalaryController@paySlip');
        Route::match(['get', 'post'], '/pay-employee-mothly-salary', 'MonthlySalaryController@paySalary')->name('pay.salary');

        /* Employee Monthly Salary Routes Starts */
    });
    /* Employees Routes Ends Here */

    /* Products Routes Starts Here */
    Route::get('/products', 'ProductController@index')->name('products');
    Route::get('/open/product/modal', 'ProductController@openProductModal')->name('open.product.modal');
    Route::get('/products/list', 'ProductController@productsList')->name('products.list');
    Route::get('/add-product', 'ProductController@addProduct')->name('create.product');
    Route::post('/store-product', 'ProductController@storeProduct')->name('store.product');
    Route::get('/edit-product/{id}', 'ProductController@editProduct')->name('edit.product');
    Route::put('/update-product/{id}', 'ProductController@updateProduct')->name('update.product');
    Route::delete('/delete-product/{id}', 'ProductController@deleteProduct')->name('delete.product');
    Route::get('low-stock-products/list', 'ProductController@lowStockProductsList')->name('low.stock.products');
    Route::get('expired-products/list', 'ProductController@expiredProductsList')->name('expired.products');
    /* Products Routes Starts Here */

    /*Check Product Stock with Ajax Route*/
    Route::get('/check-product-stock', 'SalesController@checkProductStock');
    /*Check Product Stock with Ajax Route Ends*/

    /* Purchase Functionality Routes Starts Here */
    Route::get('/purchases', 'PurchaseController@index')->name('purchases');
    Route::get('/purchase/list', 'PurchaseController@purchaseList')->name('purchase.list');
    Route::get('/add-purchase', 'PurchaseController@addPurchase')->name('create.purchase');
    Route::post('/add-purchase', 'PurchaseController@postPurchase')->name('post.purchase');
    Route::get('/edit-purchase/{id}', 'PurchaseController@editPurchase')->name('edit.purchase');
    Route::post('/update-purchase/{id}', 'PurchaseController@updatePurchase')->name('update.purchase');
    Route::delete('/delete-purchase/{id}', 'PurchaseController@deletePurchase')->name('delete.purchase');
    Route::get('/purchase-invoice/{id}', 'PurchaseController@purchaseInvoice')->name('purchase.invoice');
    Route::get('/print-purchase-invoice/{id}', 'PurchaseController@printPurchaseInvoice')->name('print.purchase.invoice');
    Route::get('/search-supplier', 'PurchaseController@serachSupplier');
    Route::get('/search-raw-product', 'PurchaseController@searchRawProducts')->name('search.raw.product');
    //Supplier Payment ....
    Route::match(['get', 'post'], '/supplier-payment/{purchase_id}', 'PurchaseController@supplierPayment');
    /* Purchase Functionality Routes Ends Here */

    /* Sales Routes Starts Here */
    Route::get('/sales', 'SalesController@index')->name('sales');
    Route::get('/sales/list', 'SalesController@salesList')->name('sales.list');

    Route::get('pos', 'SalesController@openPos')->name('pos');
    Route::get('add-sale', 'SalesController@addSale')->name('create.sale');
    Route::get('/search-invoice', 'SalesController@searchInvoice')->name('search.invoice');
    Route::get('download/bulk/sale/sample', 'SalesController@downloadSample')->name('download.bulk.sale.sample');
    Route::post('sales/import', 'SalesController@salesImport')->name('sales.import');
    Route::post('post-sale', 'SalesController@postSale')->name('post.sale');
    Route::get('edit-sale/{id}', 'SalesController@editSale')->name('edit.sale');
    Route::post('update-sale/{id}', 'SalesController@updateSale')->name('update.sale');
    Route::delete('/delete-sale/{id}', 'SalesController@deleteSale')->name('delete.sale');
    Route::get('/sale-invoice/{id}', 'SalesController@saleInvoice')->name('sale.invoice');
    Route::get('/print-sale-invoice/{id}', 'SalesController@printSaleInvoice')->name('print.invoice');
    Route::get('/bulk-print-sales', 'SalesController@bulkPrintSales')->name('bulk.print.sales');
    Route::get('/pos-sale-invoice/{id}', 'SalesController@posSaleInvoice')->name('pos.invoice');
    Route::get('/invoice/payment-modal/{sale_id}', 'SalesController@showInvoicePaymentModal')->name('invoice.payment.modal');
    Route::post('/invoice/payment-submit', 'SalesController@submitInvoicePayment')->name('invoice.payment.submit');
    Route::delete('/invoice/payment-delete/{payment_id}', 'SalesController@deleteInvoicePayment')->name('invoice.payment.delete');
    Route::get('due-sales', 'SalesController@dueSales')->name('due.sales');

    Route::get('/search-product', 'ProductController@searchProducts');
    Route::get('/search-product-unit', 'ProductController@searchProductUnit');
    /* Sales Routes Ends Here */

    // Lucky Draws Routes
    // Route::get('/lucky/draws', 'LuckyDrawController@index')->name('lucky.draws');
    // Route::get('/lucky/draws/list', 'LuckyDrawController@luckyDrawList')->name('lucky.draws.list');

    Route::get('/lucky-draws', 'LuckyDrawController@index')->name('lucky-draws.index');
    Route::get('/lucky-draws/list', 'LuckyDrawController@luckyDrawList')->name('lucky-draws.list');
    Route::post('/lucky-draws', 'LuckyDrawController@store')->name('lucky-draws.store');
    Route::get('/lucky-draws/participants/{id}', 'LuckyDrawController@getParticipants')->name('lucky-draws.participants');
    Route::post('/lucky-draws/spin/{id}', 'LuckyDrawController@spinDraw')->name('lucky-draws.spin');
    Route::post('/lucky-draws/toggle-status/{id}', 'LuckyDrawController@toggleStatus')->name('lucky-draws.toggle-status');


    Route::get('/godowns', 'GodownController@godowns')->name('godowns');
    Route::get('/godowns/list', 'GodownController@godownsList')->name('godowns.list');
    Route::get('/godown/{id}', 'GodownController@singleGodown')->name('single.godown');
    Route::post('/godown/store/{id?}', 'GodownController@godownsStore')->name('godown.store');
    Route::delete('/godow/delete/{id}', 'GodownController@godownDelete')->name('godown.delete');
    Route::get('/godown-search', 'GodownController@searchgodowns')->name('godown.search');

    /* Reports Controllers Starts Here */
    Route::namespace('Reports')->group(function () {
        /* Profit && Loss Report */
        Route::get('/report-profit-loss', 'ProfitLossController@profitLoss')->name('report.profit.loss');

        // Daily Cash Report
        Route::get('/report/daily-cash', 'DailyCashController@index')->name('report.daily.cash');
        Route::post('/report/daily-cash/list', 'DailyCashController@dailyCashList')->name('report.daily.cash.list');
        Route::post('/report/daily-cash/pdf', 'DailyCashController@downloadDailyCashPdf')->name('report.daily.cash.pdf');

        /* Purchase Report */
        Route::get('/report/purchase', 'PurchaseReportController@index')->name('report.purchase');
        Route::get('/report/purchase/list', 'PurchaseReportController@purchaseReportList')->name('report.purchase.list');
        Route::get('/report/supplier/payments', 'PurchaseReportController@supplierPayments')->name('report.supplier.payments');
        Route::get('/report/supplier/payments/list', 'PurchaseReportController@supplierPaymentsList')->name('report.supplier.payments.list');
        Route::get('/report/supplier/ledger', 'PurchaseReportController@supplierLedger')->name('report.supplier.ledger');
        Route::get('/report/supplier/ledger/list', 'PurchaseReportController@supplierLedgerList')->name('report.supplier.ledger.list');

        Route::get('/report/purchase-items', 'ItemPurchaseHistoryController@index')->name('report.purchase.items');
        Route::post('/reports/purchase-items/data', 'ItemPurchaseHistoryController@getPurchaseItemsData')->name('purchase.items.report.data');
        // Route::post('/report-purchase-pdf', 'PurchaseReportController@downloadPurchasePdf');
        /* Supplier Credit/Debit Report */
        // Route::get('/report-supplier-credit-debit', 'SupplierCreditDebitController@index');
        // Route::post('/report-supplier-credit-debit-pdf', 'SupplierCreditDebitController@supplierCreditDebitReport');

        /* Sales Report */
        Route::get('/report/sale', 'SaleReportController@index')->name('report.sale');
        Route::get('/report/sale/list', 'SaleReportController@saleReportList')->name('report.sale.list');
        Route::get('/report/customer/payments', 'SaleReportController@customerPayments')->name('report.customer.payments');
        Route::get('/report/customer/payments/list', 'SaleReportController@customerPaymentsList')->name('report.customer.payments.list');
        Route::get('/report/customer/ledger', 'SaleReportController@customerLedger')->name('report.customer.ledger');
        Route::get('/report/customer/ledger/list', 'SaleReportController@customerLedgerList')->name('report.customer.ledger.list');
        Route::get('/report/sales', 'UserSalesController@index')->name('report.sales');
        Route::get('/report/areawise-sales', 'SaleReportController@areawise')->name('report.areawise.sales');
        Route::get('/report/areawise/sales/list', 'SaleReportController@areawisesalesList')->name('report.areawise.sales.list');
        // Route::get('/report-get-staff-sales', 'UserSalesController@getUsersSale');
        // Route::post('/report-salesByStaff-pdf', 'UserSalesController@downloadUserSalesPdf');

        /* Customer Payments By Staff Report */
        // Route::get('load-payments-by-staff', 'UserSalesController@loadPaymentsByStaff');
        // Route::get('/report/customer/payments', 'UserSalesController@paymentsByStaff')->name('report.customer.payments');
        // Route::post('/report-paymentsByStaff-pdf', 'UserSalesController@downloadPaymentsByStaffPdf');


        /* Customer Credit/Debit Report */
        // Route::get('/report/customer/ledger', 'CreditDebitController@index')->name('report.customer.ledger');
        // Route::post('/report-credit-debit-pdf', 'CreditDebitController@creditDebitReport');
        // Route::get('/report-credit-debit/search', 'CreditDebitController@creditDebitSearch');
        // Route::post('/report-credit-debit/download', 'CreditDebitController@creditDebitDownloadPDF')->name('credit.debit.download');



        /* old Sales Report */
        // Route::get('/report-sales', 'SalesController@index');
        // Route::get('/report-get-sales', 'SalesController@getSales');
        // Route::post('/report-sales-pdf', 'SalesController@downloadSalesPdf');

        /* Sold Items Report */
        Route::get('/report-sold-items', 'SoldItemsController@index')->name('report.sold.items');
        Route::get('/get-sold-products', 'SoldItemsController@getSoldProducts');
        Route::post('/sold-items-report-pdf', 'SoldItemsController@downloadSoldItemsPdf');

        /* Stock Report */
        Route::get('/report-stock', 'StockController@index')->name('report.stock');
        Route::get('/get-product-stock', 'StockController@getStock');
        Route::post('/report-stock-pdf', 'StockController@downloadStockPdf');

        /* Expense Report */
        Route::get('/report-expense', 'ExpenseReportController@index')->name('report.expense');
        Route::get('/get-expense', 'ExpenseReportController@getExpense');
        Route::post('/report-expense-pdf', 'ExpenseReportController@downloadExpensePdf');
    });
    /* Reports Controllers Starts Here */
});
