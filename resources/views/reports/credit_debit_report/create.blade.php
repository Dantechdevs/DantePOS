@extends('layouts.layout')
@section('title', '| Credit/Debit Report')
@section('content')

@section('custom_styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
@endsection

<style>
    /* Style for the loader */
    #loader {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        z-index: 9999;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .loader-wrapper {
        text-align: center;
    }

    .spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border-left-color: #4caf50;
        /* Green Spinner */
        animation: spin 1s ease infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    #loader p {
        font-size: 16px;
        margin-top: 10px;
        color: #4caf50;
        /* Same color as the spinner */
        font-weight: bold;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Credit/Debit Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Credit/Debit Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Search Filters</h3>
                </div>
                <div class="card-body">
                    <form id="creditDebitForm" action="{{ route('credit.debit.download') }}" method="post">@csrf
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Report Type</label>
                            <div class="col-sm-4">
                                <select id="sale_type" name="sale_type" class="form-control select2">
                                    <option value="" disabled selected>-Select-</option>
                                    <option value="customerWise">Customer Wise</option>
                                    <option value="areaWise">Area Wise</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="customerField" style="display:none;">
                            <label class="col-sm-2 col-form-label">Customer</label>
                            <div class="col-sm-4">
                                <select id="customer_id" name="customer_id" class="form-control select2">
                                    <option value="" disabled selected>-Select-</option>
                                    <option value="all">All</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="areaField" style="display:none;">
                            <label class="col-sm-2 col-form-label">Area</label>
                            <div class="col-sm-4">
                                <select id="area_id" name="area_id" class="form-control select2">
                                    <option value="" disabled selected>-Select-</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row dateRange" style="display: none;">
                            <label class="col-sm-2 col-form-label">From Date</label>
                            <div class="col-sm-4">
                                <input type="text" id="startDate" name="startDate" class="form-control datepicker"
                                    placeholder="DD-MM-YYYY" readonly>
                            </div>
                            <label class="col-sm-2 col-form-label">To Date</label>
                            <div class="col-sm-4">
                                <input type="text" id="endDate" name="endDate" class="form-control datepicker"
                                    placeholder="DD-MM-YYYY" value="{{ date('d-m-Y') }}" readonly>
                            </div>
                        </div>


                        <button type="button" class="btn btn-primary" id="searchButton"
                            style="margin-right: 5px; float:right;">
                            <i class="fas fa-search"></i> Search
                        </button>

                    </form>
                </div>
            </div>
            <div id="loader" style="display: none;">
                <div class="loader-wrapper">
                    <div class="spinner"></div>
                    <p>Generating your report...</p>
                </div>
            </div>

            <div class="card card-dark mt-3" id="resultsContainer" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">Search Results</h3>
                </div>
                <div class="card-body table-responsive">
                    <table id="resultsTable" class="table table-bordered table-striped">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>



        </div>
    </section>
</div>

@endsection

@push('custom-script')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
<script src="{{ asset('js/common/global.js') }}"></script>
<script src="{{ asset('js/reports/customer_credit_debit.js') }}"></script>
@endpush
