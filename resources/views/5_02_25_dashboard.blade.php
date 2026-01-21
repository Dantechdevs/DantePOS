@extends('layouts.layout')
@section('title', '| Dashboard')
@section('content')

<?php
use App\Models\AdvanceHistory;
use App\Models\EmployeeReturnAdvance;

/* Calculations for Dashboard Metrics */
$sale_total = App\Models\Sale::where('status', 1)->sum('grand_total');
$customerCreditAmount = App\Models\CustomerOpeningBalance::where('type', 'credit')->sum('amount');
$customerDebitAmount = App\Models\CustomerOpeningBalance::where('type', 'debit')->sum('amount');
$total_expenses = App\Models\Expense::sum('amount');
$supplierPaymets = App\Models\SupplierPayment::sum('amount');
$purchaseAmount = App\Models\Purchase::where('status', 'received')->sum('grand_total');

$countSales = App\Models\Sale::count('id');
$countPurchase = App\Models\Purchase::count('id');
$countSupplier = App\Models\Supplier::count('id');
$countCustomers = App\Models\Customer::count('id');

$products = App\Models\Product::get();
$totalAmount = 0;

foreach ($products as $product) {
    $quantity = $product->quantity;
    $cost = $product->qtyPerUnit > 0 ? $product->item_cost : $product->cost;
    $totalAmount += $quantity * $cost;
}

$customersCash = ($sale_total + $customerDebitAmount) - ($customerCreditAmount);
$suppliersCash = ($supplierPaymets) - ($purchaseAmount);
$totalCash = ($totalAmount + $suppliersCash + $customersCash) - $total_expenses;

/* Helper Functions */
function testinggetMonthlySalary($employee_id) {
    $advanceRecords = AdvanceHistory::where('employee_id', $employee_id)->pluck('current_paidAmount');
    return $advanceRecords->sum();
}

function returnMonthlySalary($employee_id) {
    $returnRecords = EmployeeReturnAdvance::where('employee_id', $employee_id)->pluck('return_amount');
    return $returnRecords->sum();
}
?>

<!-- Dashboard Content -->
<div class="content-wrapper">
    <!-- Header Section -->
    <div class="content-header">
        <div class="container-fluid">
            {{-- <form action="{{ route('restore') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="backup_file">Select Backup File:</label>
                <input type="file" name="backup_file" id="backup_file" required>
                <button type="submit">Restore</button>
            </form> --}}
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                    <small>Overall Information on Single Screen</small>
                </div>
                <div class="col-sm-6 text-end">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Financial Cards -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-warning text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-3x mb-2"></i>
                            <h5>Customers Cash</h5>
                            <p class="card-text fs-4"><strong>{{ $customersCash < 0 ? number_format(abs($customersCash),2) . ' CR' : number_format($customersCash,2) . ' DB' }}</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-info text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-handshake fa-3x mb-2"></i>
                            <h5>Suppliers Cash</h5>
                            <p class="card-text fs-4">
                                <strong>
                                    {{ $suppliersCash < 0 ? number_format(abs($suppliersCash), 2) . ' CR' : number_format($suppliersCash, 2) . ' DB' }}
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-success text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                            <h5>Total Sales Amount</h5>
                            <p class="card-text fs-4"><strong>{{ number_format($sale_total) }} PKR</strong></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-dark text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-warehouse fa-3x mb-2"></i>
                            <h5>Stock Value</h5>
                            <p class="card-text fs-4"><strong>{{number_format($totalAmount)}} PKR</strong></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-secondary text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill fa-3x mb-2"></i>
                            <h5>Total Cash</h5>
                            <p class="card-text fs-4"><strong>{{ number_format($totalCash) }} PKR</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-danger text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-coins fa-3x mb-2"></i>
                            <h5>Total Expenses</h5>
                            <p class="card-text fs-4"><strong>{{ number_format($total_expenses) }} PKR</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice and Supplier Cards -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-info text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-bag fa-3x mb-2"></i>
                            <h5>Purchase Invoice</h5>
                            <p class="card-text fs-4"><strong>{{ $countPurchase }}</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-success text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar fa-3x mb-2"></i>
                            <h5>Sales Invoice</h5>
                            <p class="card-text fs-4"><strong>{{ $countSales }}</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-warning text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x mb-2"></i>
                            <h5>Suppliers</h5>
                            <p class="card-text fs-4"><strong>{{ $countSupplier }}</strong></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card shadow bg-danger text-white animate-card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-friends fa-3x mb-2"></i>
                            <h5>Customers</h5>
                            <p class="card-text fs-4"><strong>{{ $countCustomers }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Table and Chart in One Row -->
            <div class="row">
                <!-- Employee Table -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-gradient-secondary text-white">
                            <h5>Employee Advance Summary</h5>
                            <input type="text" id="searchInput" class="form-control mt-2" placeholder="Search Employee...">
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-hover" id="employeeTable" style="min-width: auto; width: 100%;">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Advance</th>
                                        <th>Returned</th>
                                        <th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $key => $employee)
                                        <?php
                                        $advance = testinggetMonthlySalary($employee['id']);
                                        $returned = returnMonthlySalary($employee['id']);
                                        $remaining = $advance - $returned;
                                        ?>
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $employee['name'] }}</td>
                                            <td>{{ number_format($advance) }}</td>
                                            <td>{{ number_format($returned) }}</td>
                                            <td>{{ number_format($remaining) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-gradient-info text-white">
                            <h5>Monthly Sales vs Purchases</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesPurchaseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesPurchaseChart').getContext('2d');
    const salesPurchaseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Sales',
                    data: @json($datas),
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                },
                {
                    label: 'Purchases',
                    data: @json($purchaseData),
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                }
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });

    // Searchable Employee Table
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function () {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#employeeTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>

<!-- Add Hover Animation for Cards -->
<style>
    .animate-card {
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .animate-card:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
</style>
@endsection
