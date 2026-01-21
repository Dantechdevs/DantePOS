@extends('layouts.layout')
@section('title', '| Daily Cash Movement Report')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/css/report_page.css') }}">
    <style>
        .balance-card {
            border-left: 4px solid #007bff !important;
        }

        .cash-in {
            color: #28a745;
        }

        .cash-out {
            color: #dc3545;
        }

        .transaction-table tbody tr {
            cursor: pointer;
        }

        .transaction-table tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Daily Cash Movement Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Daily Cash Movement Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Search Form -->
            <div class="card card-dark">
                <div class="card-body">
                    <form id="dailyCashForm">
                        <div class="form-group row">
                            <div class="col-sm-6">
                                <label for="date" class="form-label">Date</label>
                                <input type="text" placeholder="DD-MM-YYYY"
                                    class="form-control form-control-sm datepicker" id="date" name="date"
                                    value="{{ date('d-m-Y') }}" autocomplete="off">
                            </div>
                            <div class="form-group text-right mt-3">
                                <button type="button" id="searchButton"
                                    data-url="{{ route('report.daily.cash.list') }}" class="btn btn-info">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>

                                <button type="button" id="downloadPdfButton"
                                    data-url="{{ route('report.daily.cash.pdf') }}"
                                    class="btn btn-danger ml-2">
                                    <i class="fas fa-file-pdf"></i> Download PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Balance Summary -->
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card balance-card">
                        <div class="card-body">
                            <h5 class="card-title">Opening Balance</h5>
                            <h3 class="card-text" id="openingBalance">0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card balance-card">
                        <div class="card-body">
                            <h5 class="card-title">Closing Balance</h5>
                            <h3 class="card-text" id="closingBalance">0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card balance-card">
                        <div class="card-body">
                            <h5 class="card-title">Net Change</h5>
                            <h3 class="card-text" id="netChange">0.00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="card card-dark mt-3">
                <div class="card-header">
                    <h3 class="card-title">Daily Transactions - <span id="reportDate">{{ date('d-m-Y') }}</span></h3>
                </div>
                <div class="card-body table-responsive" id="resultsContainer">
                    <table class="table table-bordered table-striped table-sm transaction-table" id="resultsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Time</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <!-- Dynamic Rows Will Be Appended Here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Total Cash In:</th>
                                <th id="totalCashIn">0.00</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-right">Total Cash Out:</th>
                                <th id="totalCashOut">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

@push('custom-script')
    <script src="{{ asset('js/common/global.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script type="module" src="{{ asset('js/reports/daily_cash_movement.js') }}"></script>
@endpush

@endsection
