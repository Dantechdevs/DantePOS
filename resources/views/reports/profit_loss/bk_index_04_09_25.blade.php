@extends('layouts.layout')
@section('title', '| Profit & Loss')

@push('styles')
<style>
    .financial-card {
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    .financial-header {
        font-weight: 600;
        padding: 0.75rem 1rem;
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
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1>Profit & Loss Report</h1>
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profit & Loss</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Report Filters</h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('report.profit.loss') }}" method="GET" class="mb-4">
    <div class="row align-items-end">  <!-- Added align-items-end here -->
        <div class="col-md-3">
            <div class="form-group mb-0">  <!-- Added mb-0 to remove bottom margin -->
                <label>From Date</label>
                <input type="text" class="form-control form-control-sm"
                       name="startDate" id="startDate" value="{{ $startDate->format('d-m-Y') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-0">  <!-- Added mb-0 to remove bottom margin -->
                <label>To Date</label>
                <input type="text" class="form-control form-control-sm" id="endDate"
                       name="endDate" value="{{ $endDate->format('d-m-Y') }}" required>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-info btn-sm btn-block">
                <i class="fas fa-search mr-1"></i> Generate
            </button>
        </div>
    </div>
</form>

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card financial-card border-top-primary">
                                <div class="card-header financial-header bg-primary text-white">
                                    Total Sales
                                </div>
                                <div class="card-body">
                                    <h3 class="text-center">{{ $currency }} {{ number_format($getTotalSalesAmount, 2) }}</h3>
                                    <div class="d-flex justify-content-between">
                                        <span>Cash: {{ $currency }} {{ number_format($getCashSalesAmount, 2) }}</span>
                                        <span>Credit: {{ $currency }} 0</span>
                                    </div>
                                    <div class="mt-2">
                                        <small>
                                            Other Charges: {{ $currency }} {{ number_format($salesOtherCharges, 2) }}
                                        </small>

                                    </div>
                                    <div class="mt-2">
                                        <small>Discount: <span class="text-discount">{{ $currency }} {{ number_format($salesDiscount, 2) }}</span></small>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card financial-card border-top-success">
                                <div class="card-header financial-header bg-success text-white">
                                    Gross Profit
                                </div>
                                <div class="card-body">
                                    <h3 class="text-center {{ $grossProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ $currency }} {{ number_format($grossProfit, 2) }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card financial-card border-top-info">
                                <div class="card-header financial-header bg-info text-white">
                                    Net Profit
                                </div>
                                <div class="card-body">
                                    <h3 class="text-center {{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ $currency }} {{ number_format($netProfit, 2) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Breakdown -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Income & Expenses</h3>
                                </div>
                                <div class="card-body p-0">
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
                                                <td class="text-right text-discount">{{ $currency }} {{ number_format($salesDiscount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Net Income</strong></td>
                                                <td class="text-right font-weight-bold">{{ $currency }} {{ number_format($getTotalSalesAmount , 2) }}</td>
                                            </tr>

                                            <tr class="bg-light">
                                                <td colspan="2"><strong>Cost of Goods Sold</strong></td>
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
                                                <td class="text-right text-success">-{{ $currency }} {{ number_format($purchaseDiscount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Net Purchases</strong></td>
                                                <td class="text-right font-weight-bold">{{ $currency }} {{ number_format($getTotalPurchaseAmount, 2) }}</td>
                                            </tr>


                                            <tr class="bg-light">
                                                <td colspan="2"><strong>Opening Balances</strong></td>
                                            </tr>
                                            {{-- <tr>
                                                <td>Customers (Debit)</td>
                                                <td class="text-right">{{ $currency }} {{ number_format($custOpening->debit ?? 0, 2) }}</td>
                                            </tr> --}}

                                            <tr>
                                                <td>Customers Opening Balance</td>
                                                <td class="text-right">{{ $currency }} {{ number_format($totalCustOpening, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Customers Opening Balance Due</td>
                                                <td class="text-right">{{ $currency }} {{ number_format($custOpening->credit ?? 0, 2) }}</td>
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

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Profit Analysis</h3>
                                </div>
                                <div class="card-body p-0">
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
                                            <tr>
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
                            </div>
                        </div>
                    </div>

                    <!-- Item-wise Profit Table -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-header bg-black">
                                    <h3 class="card-title"><strong>Item-wise Profit</strong></h3>
                                </div>
                                <div class="box-body table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="bg-blue">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice #</th>
                                                <th>Item Name</th>
                                                <th>Qty</th>
                                                <th class="text-right">Sales {{ $currency }}</th>
                                                <th class="text-right">Cost {{ $currency }}</th>
                                                <th class="text-right">Profit {{ $currency }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $counter = 0; $totalProfit = 0; @endphp
                                            @foreach($sales as $sale)
                                                @php
                                                    $items = unserialize($sale->items_addon);
                                                    foreach($items as $item) {
                                                        $counter++;
                                                        $profit = $item['amount'] - $item['calculatedCost'];
                                                        $totalProfit += $profit;
                                                @endphp
                                                <tr>
                                                    <td>{{ $counter }}</td>
                                                    <td><a href="{{ route('sale.invoice', $sale->id) }}" target="_blank">{{ $sale->invoice_no }}</a></td>
                                                    <td>{{ $item['productName'] }}</td>
                                                    <td>{{ $item['quantity'] }}</td>
                                                    <td class="text-right">{{ number_format($item['amount'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($item['calculatedCost'], 2) }}</td>
                                                    <td class="text-right {{ $profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                                        {{ number_format($profit, 2) }}
                                                    </td>
                                                </tr>
                                                @php } @endphp
                                            @endforeach
                                            <tr class="bg-gray text-white font-weight-bold">
                                                <td colspan="6" class="text-right">Total Gross Profit</td>
                                                <td class="text-right">{{ $currency }} {{ number_format($totalProfit, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
