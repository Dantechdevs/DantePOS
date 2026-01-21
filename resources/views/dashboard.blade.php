@extends('layouts.layout')
@section('title', '| Dashboard')
@section('content')

    @php
        $permissionCheck = checkRolePermission($module_page = 'dashboard');

    @endphp

@section('custom_styles')
<style>
:root{
    --pos-primary:#8b1d18;
    --pos-dark:#0f172a;
    --pos-bg:#f4f6f9;
    --success:#22c55e;
    --warning:#f59e0b;
    --info:#0ea5e9;
    --danger:#ef4444;
}

body{background:var(--pos-bg);}

/* POS CARDS */
.card{
    border:none;
    border-radius:16px;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    transition:.25s ease;
}
.card:hover{
    transform:translateY(-5px);
    box-shadow:0 16px 36px rgba(0,0,0,.14);
}

.card-header{
    background:#fff;
    border-bottom:1px solid #e5e7eb;
    border-radius:16px 16px 0 0;
}

/* LEFT COLOR STRIP (POS STYLE) */
.border-left-primary{border-left:6px solid var(--pos-primary)!important;}
.border-left-success{border-left:6px solid var(--success)!important;}
.border-left-warning{border-left:6px solid var(--warning)!important;}
.border-left-info{border-left:6px solid var(--info)!important;}
.border-left-danger{border-left:6px solid var(--danger)!important;}
.border-left-secondary{border-left:6px solid #64748b!important;}

/* ICON BADGES */
.card i{
    padding:14px;
    border-radius:14px;
    background:rgba(0,0,0,.05);
}

/* HEADINGS */
h1,h4,h5{
    font-weight:700;
    color:#111827;
}

/* TABLES */
.table thead th{
    font-size:.7rem;
    text-transform:uppercase;
    font-weight:700;
    letter-spacing:.05em;
    color:#6b7280;
    background:#f8fafc;
    border-bottom:none;
}

.table-hover tbody tr:hover{
    background:#f1f5f9;
}

/* BUTTONS */
.btn-outline-primary{
    border-radius:10px;
    border-color:var(--pos-primary);
    color:var(--pos-primary);
}
.btn-outline-primary.active,
.btn-outline-primary:hover{
    background:var(--pos-primary);
    color:#fff;
}

/* BADGES */
.badge-expired{
    background:var(--danger);
    color:#fff;
    border-radius:999px;
    font-size:.7rem;
}
.badge-low-stock{
    background:var(--warning);
    color:#111827;
    border-radius:999px;
    font-size:.7rem;
}
</style>
@endsection


<div class="content-wrapper">
    @include('flash_messages')
    @if ($permissionCheck->access == 1)
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                        <p class="text-muted mb-0">Key metrics of your Enterprise</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Financial Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-chart-pie text-primary mr-2"></i>
                            Financial Overview
                        </h4>
                    </div>
                </div>
                <div class="row" id="financialCardsRow">
                    <!-- Customers Cash -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-primary h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-primary mb-1">Customers Cash</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payables -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-warning h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-hand-holding-usd fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-warning mb-1">Payables</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        <small class="text-muted">PKR CR</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sales -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-success h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-shopping-cart fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-success mb-1">Total Sales</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        <small class="text-muted">PKR</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cash -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-info h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-info mb-1">Total Cash</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        <small class="text-muted">PKR</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Expenses -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-danger h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-coins fa-2x text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-danger mb-1">Total Expenses</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        <small class="text-muted">PKR</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Value -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-secondary h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-warehouse fa-2x text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-secondary mb-1">Stock Value</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                                        <small class="text-muted">PKR</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-tachometer-alt text-primary mr-2"></i>
                            Business Summary
                        </h4>
                    </div>
                </div>

                <!-- Add this section after the Business Summary section in your dashboard -->




                <div class="row mb-4" id="summaryCardsRow">
                    <!-- Sales -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-left-success h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-shopping-cart fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-success mb-1">Sales</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-left-primary h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-shopping-bag fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-primary mb-1">Purchase</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-left-info h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-users fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-info mb-1">Customers</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">24</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-left-warning h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-boxes fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-warning mb-1">Products</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">56</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employees -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-left-secondary h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-user-tie fa-2x text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-secondary mb-1">Employees</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suppliers -->
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card border-left-danger h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="fas fa-truck fa-2x text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-weight-bold text-danger mb-1">Suppliers</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier by Day Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-truck text-primary mr-2"></i>
                            Suppliers by Day
                        </h4>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header border-bottom-0 bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">Suppliers for Selected Day</h5>
                                    </div>
                                    <div class="col-md-6 text-md-right">
                                        <div class="form-inline justify-content-md-end">
                                            <label for="daySelect" class="mr-2 mb-2 mb-md-0">Select Day:</label>
                                            <select class="form-control form-control-sm" id="daySelect">
                                                <option value="monday">Monday</option>
                                                <option value="tuesday">Tuesday</option>
                                                <option value="wednesday">Wednesday</option>
                                                <option value="thursday">Thursday</option>
                                                <option value="friday">Friday</option>
                                                <option value="saturday">Saturday</option>
                                                <option value="sunday">Sunday</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="supplierTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Mobile</th>
                                                <th class="text-end">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody id="supplierTableBody">
                                            <!-- Supplier data will be loaded here based on selected day -->
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                    <p class="mt-2 mb-0">Loading suppliers...</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inactive Customers Section -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3">
            <i class="fas fa-user-clock text-warning mr-2"></i>
            Inactive Customers (No Purchase in 3 Days)
        </h4>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header border-bottom-0 bg-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Customers with No Recent Purchases</h5>
                        <small class="text-muted">Customers who haven't purchased in the last 3 days</small>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <button class="btn btn-sm btn-outline-primary" id="refreshInactiveCustomers">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="inactiveCustomersTable">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Mobile</th>
                                <th>Last Purchase Date</th>
                                <th>Days Since Last Purchase</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="inactiveCustomersTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border text-warning" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading inactive customers...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

                <!-- Employee and Product Alerts Section -->
                <div class="row">
                    <!-- Employee Advances -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header border-bottom-0 bg-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-users text-primary mr-2"></i>
                                    Employee Advances
                                </h4>
                                <div class="input-group input-group-sm mt-2 w-50">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Search employees...">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>EMPLOYEE</th>
                                                <th class="text-end">ADVANCE</th>
                                                <th class="text-end">RETURNED</th>
                                                <th class="text-end">BALANCE</th>
                                            </tr>
                                        </thead>
                                        <tbody id="employeeTableBody">
                                            <!-- Employee data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Due Sales -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100"> <!-- Added h-100 to make cards equal height -->
                            <div class="card-header border-bottom-0 bg-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                    Due Sales
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Fixed height and scroll -->
                                    <table class="table table-hover mb-0 table-sm" id="dueSalesTable"
                                        data-url="{{ route('due.sales') }}">
                                        <thead class="bg-light position-sticky" style="top: 0; z-index: 1;">
                                            <!-- Sticky header -->
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Invoice#</th>
                                                <th>Cusotmer</th>
                                                <th>Due</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Table content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Expired Products -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100"> <!-- Added h-100 to make cards equal height -->
                            <div class="card-header border-bottom-0 bg-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                                    Expired Products
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Fixed height and scroll -->
                                    <table class="table table-hover mb-0 table-sm" id="expiredProductsTable"
                                        data-url="{{ route('expired.products') }}">
                                        <thead class="bg-light position-sticky" style="top: 0; z-index: 1;">
                                            <!-- Sticky header -->
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>PRODUCT</th>
                                                <th class="text-end">EXPIRY DATE</th>
                                                <th class="text-end">QUANTITY</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Table content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Products -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100"> <!-- Added h-100 to make cards equal height -->
                            <div class="card-header border-bottom-0 bg-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-box-open text-warning mr-2"></i>
                                    Low Stock Products
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Fixed height and scroll -->
                                    <table class="table table-hover mb-0 table-sm" id="lowStockTable"
                                        data-url="{{ route('low.stock.products') }}">
                                        <thead class="bg-light position-sticky" style="top: 0; z-index: 1;">
                                            <!-- Sticky header -->
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>PRODUCT</th>
                                                <th class="text-end">CURRENT STOCK</th>
                                                <th class="text-end">REORDER LEVEL</th>
                                                <th>STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Table content will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Performance Chart -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header border-bottom-0 bg-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-chart-bar text-primary mr-2"></i>
                                    Monthly Performance
                                </h4>
                                <div class="btn-group btn-group-sm mt-2">
                                    <button class="btn btn-outline-primary active" data-period="month">Month</button>
                                    <button class="btn btn-outline-primary" data-period="week">Week</button>
                                    <button class="btn btn-outline-primary" data-period="year">Year</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height:300px;">
                                    <canvas id="salesPurchaseChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>

@endsection

@push('custom-script')
<!-- Include separate JS file -->
<script src="{{ asset('js/dashboard.js') }}"></script>
<script src="{{ asset('js/supplier_dashboard_data.js') }}"></script>
<script src="{{ asset('js/customer_dashboard_data.js') }}"></script>
<script type="module" src="{{ asset('js/stock_expiry_tables.js') }}"></script>
@endpush
