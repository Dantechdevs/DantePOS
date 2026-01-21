@php
    $currentRoute = Route::currentRouteName(); // Get the current route name
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
<a href="{{ url('/dashboard') }}" class="brand-link" style="display: flex; align-items: center; gap: 6px; padding-left: 10px;">
    <span style="font-weight: 700; font-size: 1.25rem; font-family: Arial, sans-serif;">
        <span style="color: #e80f91;">DANTE</span><span style="color: #ff6f00;">POS</span>
    </span>
</a>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('images/avatar5.png') }}" class="img-circle elevation-2" alt="User Image"
                    style="height: 40px; width: 45px;">
            </div>
            <div class="info">
                <a href="{{ url('/user-profile') }}" class="d-block">&nbsp;&nbsp; {{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2 text-sm">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item has-treeview">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>

                </li>



                <!-- MANAGE USERS--------------------------->
                @php
                    $usersAccess = checkRolePermission('users');
                    $rolesAccess = checkRolePermission('roles');
                @endphp
                @if (($usersAccess && $usersAccess->access == 1) || ($rolesAccess && $rolesAccess->access == 1))
                    <li
                        class="nav-item has-treeview  {{ in_array($currentRoute, ['users', 'user.create', 'user.update', 'roles', 'create.role', 'update.role']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link  {{ in_array($currentRoute, ['users', 'user.create', 'user.update', 'roles', 'create.role', 'update.role']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Manage Users
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if ($usersAccess && $usersAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ url('add-user') }}"
                                        class="nav-link  {{ $currentRoute === 'user.create' ? 'active' : '' }}">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>Add User</p>
                                    </a>
                                </li>
                                <!-- View Users--------------------------->

                                <li class="nav-item">
                                    <a href="{{ url('view-users') }}"
                                        class="nav-link {{ $currentRoute === 'users' ? 'active' : '' }}">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>View Users</p>
                                    </a>
                                </li>
                            @endif

                            <!-- View Roles--------------------------->
                            @if ($rolesAccess && $rolesAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ url('roles') }}"
                                        class="nav-link {{ $currentRoute === 'roles' || $currentRoute === 'create.role' || $currentRoute === 'update.role' ? 'active' : '' }}">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <li
                    class="nav-item has-treeview {{ in_array($currentRoute, ['godowns', 'areas', 'units']) ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ in_array($currentRoute, ['godowns', 'areas', 'units']) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-table"></i>
                        <p>
                            Master Data
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">


                        <li class="nav-item">
                            <a href="{{ route('godowns') }}"
                                class="nav-link {{ $currentRoute === 'godowns' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-warehouse"></i>

                                <p>Godowns</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('areas') }}"
                                class="nav-link {{ $currentRoute === 'areas' ? 'active' : '' }}">
                                <i class="fas fa-map-marker-alt nav-icon"></i>
                                <p>Areas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('units') }}"
                                class="nav-link {{ $currentRoute === 'units' ? 'active' : '' }}">
                                <i class="fas fa-ruler nav-icon"></i>
                                <p>Product Units</p>
                            </a>
                        </li>


                    </ul>
                </li>



                <!-- INVOICE MANAGEMENT--------------------------->
                @php
                    $salesAccess = checkRolePermission('sales');
                    $godownsAccess = checkRolePermission('godowns');
                @endphp


                @if ($salesAccess && $salesAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['sales', 'create.sale', 'edit.sale', 'sale.invoice']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['sales', 'create.sale', 'edit.sale', 'sale.invoice']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>
                                Sales
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">


                            <!-- View Invoice--------------------------->
                            @if ($salesAccess && $salesAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ route('create.sale') }}"
                                        class="nav-link {{ $currentRoute === 'create.sale' || $currentRoute === 'edit.sale' ? 'active' : '' }}">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>New Sales</p>
                                    </a>
                                </li>
                                <!-- Invoice List--------------------------->
                                <li class="nav-item">
                                    <a href="{{ route('sales') }}"
                                        class="nav-link {{ $currentRoute === 'sales' ? 'active' : '' }}">
                                        <i class="fas fa-list nav-icon"></i>

                                        <p>Sales List</p>
                                    </a>
                                </li>
                            @endif


                        </ul>
                    </li>
                @endif


                <!-- LUCKY DRAW MANAGEMENT--------------------------->

                <li class="nav-item">
                    <a href="{{ route('lucky-draws.index') }}"
                        class="nav-link {{ $currentRoute === 'lucky-draws.index' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-gift"></i> <!-- Changed icon -->
                        <p>Lucky Draw</p>
                    </a>
                </li>

                <!-- CUSTOMER MANAGEMENT--------------------------->
                @php
                    $customersAccess = checkRolePermission('customers');
                @endphp
                @if ($customersAccess && $customersAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['customers', 'customer.payments']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['customers', 'customer.payments']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Customers
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">

                            <!-- Customers List--------------------------->
                            @if ($customersAccess && $customersAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ route('customers') }}"
                                        class="nav-link {{ $currentRoute === 'customers' ? 'active' : '' }}">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>Customers</p>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                <!-- PURCHASE MANAGEMENT--------------------------->
                @php
                    $purchaseAccess = checkRolePermission('purchase');
                @endphp
                @if ($purchaseAccess && $purchaseAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['purchases', 'create.purchase', 'edit.purchase', 'purchase.invoice']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['purchases', 'create.purchase', 'edit.purchase', 'purchase.invoice']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-th-large"></i>
                            <p>
                                Purchase
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- Add Purchase Order --------------------------->
                            @if ($purchaseAccess && $purchaseAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ route('create.purchase') }}"
                                        class="nav-link {{ $currentRoute === 'create.purchase' ? 'active' : '' }}">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>New Purchase</p>
                                    </a>
                                </li>
                                <!-- Purchase Orders --------------------------->

                                <li class="nav-item">
                                    <a href="{{ route('purchases') }}"
                                        class="nav-link {{ $currentRoute === 'purchases' || $currentRoute === 'edit.purchase' ? 'active' : '' }}">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>Purchase List</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                <!-- SUPPLIER MANAGEMENT--------------------------->
                @php
                    $supplierAccess = checkRolePermission('supplier');
                    $suppliers_paymentAccess = checkRolePermission('suppliers_payment');
                @endphp
                @if (
                    ($supplierAccess && $supplierAccess->access == 1) ||
                        ($suppliers_paymentAccess && $suppliers_paymentAccess->access == 1))
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['suppliers', 'supplier.payments']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['suppliers', 'supplier.payments']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>
                                Suppliers

                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if ($supplierAccess && $supplierAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ url('/suppliers') }}"
                                        class="nav-link  {{ $currentRoute === 'suppliers' ? 'active' : '' }}">
                                        <i class="fas fa-list nav-icon"></i>
                                        <p>Suppliers List</p>
                                    </a>
                                </li>
                            @endif
                            @if ($suppliers_paymentAccess && $suppliers_paymentAccess->access == 1)
                                <li class="nav-item">
                                    <a href="{{ route('supplier.payments') }}"
                                        class="nav-link {{ $currentRoute === 'supplier.payments' ? 'active' : '' }}">
                                        <i class="fas fa-money-check-alt nav-icon"></i>
                                        <p>Supplier Payment</p>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                <!-- PRODUCT MANAGEMENT--------------------------->
                @php
                    $productAccess = checkRolePermission('product');
                @endphp
                @if ($productAccess && $productAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['products', 'create.product', 'edit.product']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['products', 'create.product', 'edit.product']) ? 'active' : '' }}">

                            <i class="nav-icon fas fa-cubes"></i>
                            <p>
                                Manage Products
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">

                            <!-- View Products--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('products') }}"
                                    class="nav-link {{ $currentRoute === 'products' ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i>
                                    <p>Products List</p>
                                </a>
                            </li>
                            <!-- Units List--------------------------->



                        </ul>
                    </li>
                @endif

                <!-- Employees MANAGEMENT--------------------------->
                @php
                    $employeesAccess = checkRolePermission('employees');
                @endphp
                @if ($employeesAccess && $employeesAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['employees', 'create.employee', 'update.employee', 'employee.details', 'employee.salaries', 'pay.salary', 'employee.increment']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['employees', 'create.employee', 'update.employee', 'employee.details', 'employee.salaries', 'pay.salary', 'employee.increment']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Emloyees
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- Create Employee--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('create.employee') }}"
                                    class="nav-link {{ $currentRoute === 'create.employee' || $currentRoute === 'update.employee' ? 'active' : '' }}">
                                    <i class="far fa-plus-square nav-icon"></i>
                                    <p>Create Employee</p>
                                </a>
                            </li>
                            <!-- Employee List--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('employees') }}"
                                    class="nav-link {{ $currentRoute === 'employees' || $currentRoute === 'employee.increment' ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i>
                                    <p>Employees List</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('employees.leaves') }}"
                                    class="nav-link {{ $currentRoute === 'employees.leaves' ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Leaves</p>
                                </a>
                            </li>


                            <li class="nav-item">
                                <a href="{{ route('employees.attendance') }}"
                                    class="nav-link {{ $currentRoute === 'employees.attendance' ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Attendance</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employee.salaries') }}"
                                    class="nav-link {{ $currentRoute === 'employee.salaries' || $currentRoute === 'pay.salary' ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Monthly Salry</p>
                                </a>
                            </li>


                        </ul>
                    </li>
                @endif

                <!-- EXPENSES MANAGEMENT--------------------------->
                @php
                    $expensesAccess = checkRolePermission('expenses');
                @endphp
                @if ($expensesAccess && $expensesAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['expenses', 'store.expense', 'update.expense', 'expense.categories', 'store.expense.category']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['expenses', 'store.expense', 'update.expense', 'expense.categories', 'store.expense.category']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-minus-circle"></i>
                            <p>
                                Expenses
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- View Categories--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('store.expense') }}"
                                    class="nav-link {{ $currentRoute === 'store.expense' || $currentRoute === 'update.expense' ? 'active' : '' }}">
                                    <i class="far fa-plus-square nav-icon"></i>
                                    <p>Add Expenses</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('expenses') }}"
                                    class="nav-link {{ $currentRoute === 'expenses' ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i>
                                    <p>Expenses</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('expense.categories') }}"
                                    class="nav-link {{ $currentRoute === 'expense.categories' || $currentRoute === 'store.expense.category' ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-th-list"></i>
                                    <p>Expense Categories</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- SETTINGS--------------------------->
                @php
                    $settingsAccess = checkRolePermission('settings');
                @endphp
                @if ($settingsAccess && $settingsAccess->access == 1)
                    <li
                        class="nav-item has-treeview {{ in_array($currentRoute, ['site.settings']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array($currentRoute, ['site.settings']) ? 'active' : '' }}">

                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Settings
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">


                            <li class="nav-item">
                                <a href="{{ route('site.settings') }}"
                                    class="nav-link {{ $currentRoute === 'site.settings' ? 'active' : '' }}">
                                    <i class="fas fa-wrench nav-icon"></i>
                                    <p>General Settings</p>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                <!-- Reports--------------------------->
                @php

                    $reportsAccess = checkRolePermission('reports');

                    if (
                        in_array($currentRoute, [
                            'report.daily.cash',
                            'report.profit.loss',
                            'report.purchase',
                            'report.purchase.items',
                            'report.supplier.payments',
                            'report.supplier.ledger',
                            'report.sale',
                            'report.customer.payments',
                            'report.customer.ledger',
                            'report.sold.items',
                            'report.stock',
                            'report.expense',
                        ])
                    ) {
                        $routes = [
                            '0' => 'menu-open',
                            '1' => 'active',
                        ];
                    }
                @endphp
                @if ($reportsAccess && $reportsAccess->access == 1)
                    <li class="nav-item has-treeview {{ $routes[0] ?? '' }}">
                        <a href="#" class="nav-link {{ $routes[1] ?? '' }}">
                            <i class="nav-icon fas fa-file-invoice"></i>
                            <p>
                                Reports
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- daily cash movement Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.daily.cash') }}"
                                    class="nav-link {{ $currentRoute === 'report.daily.cash' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Daily Cash Movement Report</p>
                                </a>
                            </li>

                            <!-- Profit and Loss Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.profit.loss') }}"
                                    class="nav-link {{ $currentRoute === 'report.profit.loss' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Profit & Loss Report</p>
                                </a>
                            </li>
                            <!-- Purchase Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.purchase') }}"
                                    class="nav-link {{ $currentRoute === 'report.purchase' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Purchase Report</p>
                                </a>
                            </li>

                             <!-- Purchase Items Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.purchase.items') }}"
                                    class="nav-link {{ $currentRoute === 'report.purchase.items' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Purchase Items Report</p>
                                </a>
                            </li>

                            <!-- Supplier Payments Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.supplier.payments') }}"
                                    class="nav-link {{ $currentRoute === 'report.supplier.payments' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Supplier Payments Report</p>
                                </a>
                            </li>

                            <!-- Supplier Ledger Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.supplier.ledger') }}"
                                    class="nav-link {{ $currentRoute === 'report.supplier.ledger' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Supplier Ledger Report</p>
                                </a>
                            </li>


                            <!-- Sales Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.sale') }}"
                                    class="nav-link {{ $currentRoute === 'report.sale' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Sales Report</p>
                                </a>
                            </li>

                            <!-- areawise Sales Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.areawise.sales') }}"
                                    class="nav-link {{ $currentRoute === 'report.areawise.sales' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Areawise Sales Report</p>
                                </a>
                            </li>

                            <!-- Customer Ledger Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.customer.ledger') }}"
                                    class="nav-link {{ $currentRoute === 'report.customer.ledger' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Customer Ledger Report</p>
                                </a>
                            </li>

                            <!-- Sold Items Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.sold.items') }}"
                                    class="nav-link {{ $currentRoute === 'report.sold.items' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Item Sales Report</p>
                                </a>
                            </li>

                            <!-- Stock Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.stock') }}"
                                    class="nav-link {{ $currentRoute === 'report.stock' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Stock Report</p>
                                </a>
                            </li>

                            <!-- Expense Report--------------------------->

                            <li class="nav-item">
                                <a href="{{ route('report.expense') }}"
                                    class="nav-link {{ $currentRoute === 'report.expense' ? 'active' : '' }}">
                                    <i class="far fa-file-alt nav-icon"></i>
                                    <p>Expense Report</p>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                <li class="nav-item">
                    <a href="{{ route('cache.clear') }}" class="nav-link"
                        onclick="return confirm('Are you sure you want to clear the cache?')">
                        <i class="nav-icon fas fa-broom"></i> <!-- Changed icon -->
                        <p>Clear Cache</p>
                    </a>
                </li>

                {{-- <li class="nav-item has-treeview">
                    <a href="{{ route('backup.page') }}"
                        class="nav-link {{ $currentRoute === 'backup.page' ? 'active' : '' }}">
                        <i class="nav-icon nav-icon fas fa-database"></i>
                        <p>
                            Backup
                        </p>
                    </a>

                </li> --}}

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
