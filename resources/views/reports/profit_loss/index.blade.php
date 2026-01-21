@extends('layouts.layout')
@section('title', '| Profit & Loss')

@section('custom_styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .financial-card {
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s;
        border-top: 4px solid transparent;
    }
    .financial-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .financial-header {
        font-weight: 600;
        padding: 0.75rem 1rem;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    .profit-positive {
        color: #28a745;
        font-weight: 600;
    }
    .profit-negative {
        color: #dc3545;
        font-weight: 600;
    }
    .table-profit {
        font-size: 0.875rem;
    }
    .table-profit th {
        white-space: nowrap;
        font-weight: 600;
    }
    .bg-blue {
        background-color: #007bff;
        color: white;
    }
    .bg-gray {
        background-color: #6c757d;
        color: white;
    }
    .text-discount {
        color: #dc3545;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .hover-row:hover {
        background-color: #f8f9fa;
    }
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .chart-container {
        position: relative;
        height: 250px;
        margin-bottom: 1.5rem;
    }
    .badge-lg {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    .card-tool {
        cursor: pointer;
    }
    .info-tooltip {
        cursor: help;
        border-bottom: 1px dotted #007bff;
    }
    .section-title {
        border-left: 4px solid #007bff;
        padding-left: 10px;
        margin: 1.5rem 0 1rem 0;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1>Profit & Loss Report</h1>
                <div>
                    <button id="printReport" class="btn btn-sm btn-outline-secondary mr-2">
                        <i class="fas fa-print"></i> Print
                    </button>
                    {{-- <button id="exportReport" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-export"></i> Export
                    </button> --}}
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Report Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    {{-- <form action="{{ route('report.profit.loss') }}" method="GET" class="mb-4" id="reportForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>From Date</label>
                                    <input type="text" class="form-control form-control-sm datepicker"
                                           name="startDate" id="startDate" value="{{ $startDate->format('d-m-Y') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>To Date</label>
                                    <input type="text" class="form-control form-control-sm datepicker" id="endDate"
                                           name="endDate" value="{{ $endDate->format('d-m-Y') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-sm btn-block" id="generateBtn">
                                    <i class="fas fa-search mr-1"></i> Generate
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="resetBtn">
                                    <i class="fas fa-redo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form> --}}

                    <!-- Summary Cards -->
                    <div class="row" id="summaryCards">
                        {{-- <div class="col-md-4">
                            <div class="card financial-card border-top-primary">
                                <div class="card-header financial-header bg-primary text-white d-flex justify-content-between">
                                    <span>Total Sales</span>
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="card-body">
                                    <div class="summary-value text-center">{{ $currency }} {{ number_format($getTotalSalesAmount, 2) }}</div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="badge bg-success badge-lg">Cash: {{ $currency }} {{ number_format($getCashSalesAmount, 2) }}</span>
                                        <span class="badge bg-warning text-dark badge-lg">Credit: {{ $currency }} 0</span>
                                    </div>
                                    <div class="mt-2 small">
                                        <div>Other Charges: {{ $currency }} {{ number_format($salesOtherCharges, 2) }}</div>
                                        <div>Discount: <span class="text-discount">{{ $currency }} {{ number_format($salesDiscount, 2) }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <div class="card financial-card border-top-success">
                                <div class="card-header financial-header bg-success text-white d-flex justify-content-between">
                                    <span>Gross Profit</span>
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="card-body">
                                    <div class="summary-value text-center {{ $grossProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ $currency }} {{ number_format($grossProfit, 2) }}
                                    </div>
                                    <div class="mt-2 small text-center">
                                        Margin: {{ $getTotalSalesAmount > 0 ? number_format(($grossProfit / $getTotalSalesAmount) * 100, 2) : 0 }}%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card financial-card border-top-info">
                                <div class="card-header financial-header bg-info text-white d-flex justify-content-between">
                                    <span>Net Profit</span>
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="card-body">
                                    <div class="summary-value text-center {{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ $currency }} {{ number_format($netProfit, 2) }}
                                    </div>
                                    <div class="mt-2 small text-center">
                                        Margin: {{ $getTotalSalesAmount > 0 ? number_format(($netProfit / $getTotalSalesAmount) * 100, 2) : 0 }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <h4 class="section-title">Performance Overview</h4>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Profit Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="profitChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Income vs Expenses</h3>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="incomeExpenseChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Breakdown -->
                    <h4 class="section-title">Financial Details</h4>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">Income & Expenses</h3>
                                    {{-- <span class="badge bg-primary">Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</span> --}}
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-profit table-sm">
                                            <tbody>
                                                <tr class="bg-light">
                                                    <td colspan="2"><strong>Income</strong></td>
                                                </tr>
                                                {{-- <tr>
                                                    <td>Total Sales</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($getTotalSalesAmount, 2) }}</td>
                                                </tr> --}}
                                                <tr>
                                                    <td>Sales Other Charges</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($salesOtherCharges, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Sales Discount</td>
                                                    <td class="text-right text-discount">-{{ $currency }} {{ number_format($salesDiscount, 2) }}</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td><strong>Net Income</strong></td>
                                                    <td class="text-right font-weight-bold">{{ $currency }} {{ number_format($getTotalSalesAmount, 2) }}</td>
                                                </tr>

                                                <tr class="bg-light">
                                                    <td><strong>Cost of Goods Sold</strong></td>
                                                    <td class="text-right font-weight-bold">{{ $currency }} {{ number_format($cogs, 2) }}</td>

                                                </tr>
                                                {{-- <tr>
                                                    <td>Total Purchases</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($getTotalPurchaseAmount, 2) }}</td>
                                                </tr> --}}
                                                <tr>
                                                    <td>Purchase Other Charges</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($purchaseOtherCharges, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Purchase Discount</td>
                                                    <td class="text-right text-discount">-{{ $currency }} {{ number_format($purchaseDiscount, 2) }}</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td><strong>Net Purchases</strong></td>
                                                    <td class="text-right font-weight-bold">{{ $currency }} {{ number_format($getTotalPurchaseAmount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cost of Goods Sold</strong></td>
                                                    <td class="text-right font-weight-bold">{{ $currency }} {{ number_format($cogs, 2) }}</td>
                                                </tr>

                                                <tr class="bg-light">
                                                    <td colspan="2"><strong>Opening Balances</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Customers Opening Balance</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($totalCustOpening, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Customers Opening Balance Due</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($customerRemainingOPBlnc, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Suppliers Opening Balance</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($totalSuppOpening, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">Profit Analysis</h3>
                                    {{-- <span class="badge bg-primary">Operating Expenses: {{ $currency }} {{ number_format($operatingExpense, 2) }}</span> --}}
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-profit table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Gross Profit</strong></td>
                                                    <td class="text-right {{ $grossProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                        <strong>{{ $currency }} {{ number_format($grossProfit, 2) }}</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Operating Expenses</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($operatingExpense, 2) }}</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td><strong>Net Profit</strong></td>
                                                    <td class="text-right {{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                        <strong>{{ $currency }} {{ number_format($netProfit, 2) }}</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Net Cash Movement</td>
                                                    <td class="text-right {{ $netCashMovement >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                        {{ $currency }} {{ number_format($netCashMovement, 2) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Profitability Indicators -->
                                    <div class="p-3 border-top">
                                        <h5 class="mb-2">Profitability Indicators</h5>
                                        <div class="d-flex justify-content-between">
                                            <div class="text-center">
                                                <div class="font-weight-bold">Gross Margin</div>
                                                <div class="{{ $grossProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                    {{ $getTotalSalesAmount > 0 ? number_format(($grossProfit / $getTotalSalesAmount) * 100, 2) : 0 }}%
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div class="font-weight-bold">Net Margin</div>
                                                <div class="{{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                    {{ $getTotalSalesAmount > 0 ? number_format(($netProfit / $getTotalSalesAmount) * 100, 2) : 0 }}%
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div class="font-weight-bold">Expense Ratio</div>
                                                <div>
                                                    {{ $getTotalSalesAmount > 0 ? number_format(($operatingExpense / $getTotalSalesAmount) * 100, 2) : 0 }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item-wise Profit Table -->
                    <h4 class="section-title">Item-wise Profit Analysis</h4>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h3 class="card-title mb-0"><strong>Item-wise Profit</strong></h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm" id="itemProfitTable">
                                            <thead class="bg-blue sticky-header">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice #</th>
                                                    <th>Date</th>
                                                    <th>Item Name</th>
                                                    <th>Qty</th>
                                                    <th class="text-right">Sales {{ $currency }}</th>
                                                    <th class="text-right">Cost {{ $currency }}</th>
                                                    <th class="text-right">Profit {{ $currency }}</th>
                                                    <th class="text-right">Margin %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $counter = 0;
                                                    $totalProfit = 0;
                                                    $totalSalesValue = 0;
                                                    $totalCostValue = 0;
                                                @endphp
                                                @foreach($sales as $sale)
                                                    @php
                                                        $items = unserialize($sale->items_addon);
                                                        foreach($items as $item) {
                                                            $counter++;
                                                            $profit = $item['amount'] - $item['calculatedCost'];
                                                            $margin = $item['amount'] > 0 ? ($profit / $item['amount']) * 100 : 0;
                                                            $totalProfit += $profit;
                                                            $totalSalesValue += $item['amount'];
                                                            $totalCostValue += $item['calculatedCost'];
                                                    @endphp
                                                    <tr class="hover-row">
                                                        <td>{{ $counter }}</td>
                                                        <td><a href="{{ route('sale.invoice', $sale->id) }}" target="_blank">{{ $sale->invoice_no }}</a></td>
                                                        <td>{{ date('M d, Y',strtotime($sale->date)) }}</td>
                                                        <td>{{ $item['productName'] }}</td>
                                                        <td>{{ $item['quantity'] }}</td>
                                                        <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($item['calculatedCost'], 2) }}</td>
                                                        <td class="text-right {{ $profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                            {{ number_format($profit, 2) }}
                                                        </td>
                                                        <td class="text-right {{ $margin >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                            {{ number_format($margin, 2) }}%
                                                        </td>
                                                    </tr>
                                                    @php } @endphp
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-gray text-white font-weight-bold">
                                                <tr>
                                                    <td colspan="5" class="text-right">Totals</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($totalSalesValue, 2) }}</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($totalCostValue, 2) }}</td>
                                                    <td class="text-right">{{ $currency }} {{ number_format($totalProfit, 2) }}</td>
                                                    <td class="text-right">{{ $totalSalesValue > 0 ? number_format(($totalProfit / $totalSalesValue) * 100, 2) : 0 }}%</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-muted">
                    <small>Report generated on {{ now()->format('M d, Y h:i A') }}</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="ml-2">Generating report...</div>
    </div>
</div>
@endsection

@push('custom-script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Initialize datepickers
        flatpickr(".datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true
        });

        // Initialize DataTable for item profit table
//         $('#itemProfitTable').DataTable({
//     paging: false, // This disables pagination
//     pageLength: 10,
//     lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
//     order: [[7, 'desc']],
//     dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
//          '<"row"<"col-sm-12"tr>>' +
//          '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
//     buttons: [
//         'copy', 'csv', 'excel', 'pdf', 'print'
//     ]
// });

        // Create charts
        const profitCtx = document.getElementById('profitChart').getContext('2d');
        const profitChart = new Chart(profitCtx, {
            type: 'bar',
            data: {
                labels: ['Gross Profit', 'Net Profit'],
                datasets: [{
                    label: 'Amount ({{ $currency }})',
                    data: [{{ $grossProfit }}, {{ $netProfit }}],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(0, 123, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(0, 123, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ $currency }} ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
        const incomeExpenseChart = new Chart(incomeExpenseCtx, {
            type: 'doughnut',
            data: {
                labels: ['Income', 'COGS', 'Operating Expenses'],
                datasets: [{
                    data: [{{ $getTotalSalesAmount }}, {{ $cogs }}, {{ $operatingExpense }}],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': {{ $currency }} ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Print report
        $('#printReport').click(function() {
            window.print();
        });

        // Export report
        $('#exportReport').click(function() {
            // This would typically trigger a server-side export
            alert('Export functionality would be implemented here');
        });

        // Reset form
        $('#resetBtn').click(function() {
            $('#startDate').val('{{ today()->format('d-m-Y') }}');
            $('#endDate').val('{{ today()->format('d-m-Y') }}');
        });

        // Show loading on form submission
        $('#reportForm').on('submit', function() {
            $('#loadingOverlay').css('display', 'flex');
        });

        // Hide loading when page fully loads
        $(window).on('load', function() {
            $('#loadingOverlay').hide();
        });
    });
</script>
@endpush
