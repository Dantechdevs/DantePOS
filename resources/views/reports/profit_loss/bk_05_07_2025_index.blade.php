@extends('layouts.layout')
@section('title', '| Profit Loss')
@section('content')

<style>
    .customerLoader {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profit & Loss Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Profit & Loss Report</h3>
                </div>
                <div class="card-body box-profile">
                    <form action="{{ route('report.profit.loss') }}" method="GET">
                        <div class="form-group row">
                            <label class="col-sm-1 col-form-label">From Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control form-control-sm" name="startDate" id="startDate" placeholder="DD-MM-YYYY" value="{{ date('d-m-Y', strtotime($startDate)) }}" readonly>
                            </div>
                            <label class="col-sm-1 col-form-label">To Date</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control form-control-sm" name="endDate" id="endDate" placeholder="DD-MM-YYYY" value="{{ date('d-m-Y', strtotime($endDate)) }}" readonly>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-info btn-sm">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    <tr><td colspan="2" class="text-bold text-primary">Purchase</td></tr>
                                    <tr><td>Total Purchase</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ $getTotalPurchaseAmount }}</td></tr>
                                    <tr><td class="text-bold text-primary">Employees Salary</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ $employees_salarie }}</td></tr>
                                    <tr><td class="text-bold text-primary">Total Expense</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ $expenses }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    <tr><td colspan="2" class="text-bold text-primary">Sales</td></tr>
                                    <tr><td>Cash Total Sales</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ number_format($getCashSalesAmount,2) }}</td></tr>
                                    <tr><td>Credit Total Sales</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ number_format($getCreditSalesAmount,2) }}</td></tr>
                                    <tr><td>Total Sales</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ number_format($getTotalSalesAmount,2) }}</td></tr>
                                    <tr><td>Total Discount on Sales</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ number_format($salesDiscount,2) }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-bordered table-hover">
                            <tbody>
                                <tr><td class="text-bold text-danger">Gross Profit</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ $returnGrossProfit }}</td></tr>
                                <tr><td class="text-bold text-success">Net Profit</td><td class="text-right text-bold">{{ optional($settings)['currency'] }} {{ $netProfit }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header bg-black">
                                <h3 class="card-title"><strong>Item Wise Profit</strong></h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead>
                                        <tr class="bg-blue">
                                            <th>#</th>
                                            <th>Invoice #</th>
                                            <th>Item Name</th>
                                            <th>Sales Quantity</th>
                                            <th>Sales Price</th>
                                            <th>Purchase Price</th>
                                            <th>Item Gross Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $counter = 0; $totalGrossProfitAmount = 0; @endphp
                                        @foreach($sales as $sale)
                                            @php
                                                $saleID = $sale['id'];
                                                $invoice_no = $sale['invoice_no'];
                                                $date = date('d-m-Y', strtotime($sale['date']));
                                                $variations = unserialize($sale['items_addon']);
                                            @endphp
                                            @foreach($variations as $variation)
                                                @php
                                                    $counter++;
                                                    $calculatedGrossProfit = $variation['amount'] - $variation['calculatedCost'];
                                                    $totalGrossProfitAmount += $calculatedGrossProfit;
                                                @endphp
                                                <tr>
                                                    <td>{{ $counter }}</td>
                                                    <td><a href="{{ route('sale.invoice', $saleID) }}" target="_blank">{{ $invoice_no }}</a></td>
                                                    <td>{{ $variation['productName'] }}</td>
                                                    <td class="text-center">{{ $variation['quantity'] }}</td>
                                                    <td class="text-right">{{ $variation['amount'] }}</td>
                                                    <td class="text-right">{{ $variation['calculatedCost'] }}</td>
                                                    <td class="text-right">{{ $calculatedGrossProfit }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        @php
                                            $totalAmount = $totalGrossProfitAmount - $salesDiscount;
                                        @endphp
                                        <tr>
                                            <td colspan="6" class="text-right bg-gray text-white font-weight-bold">Total Gross Profit</td>
                                            <td class="text-right bg-gray text-white font-weight-bold">{{ optional($settings)['currency'] }} {{ $totalGrossProfitAmount }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
