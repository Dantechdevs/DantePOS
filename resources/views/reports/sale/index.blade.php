@extends('layouts.layout')
@section('title', '| Sale Report')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/css/report_page.css') }}">
@endsection
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sale Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Sale Report</li>
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
                    <form id="purchaseForm">
                        <div class="form-group row">
                            <div class="col-sm-6">
                                <label for="startDate" class="form-label">From Date</label>
                                <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm"
                                    id="startDate" value="{{ date('d-m-Y') }}" readonly>
                            </div>
                            <div class="col-sm-6">
                                <label for="endDate" class="form-label">To Date</label>
                                <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm"
                                    id="endDate" value="{{ date('d-m-Y') }}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label for="godown_id" class="form-label">Godowns</label>
                                <select id="godown_id" class="form-control select2 form-control-sm"
                                    style="width: 100%;">
                                    <option disabled="true">-select-</option>
                                    <option value="all" selected>All</option>
                                    @foreach ($godowns as $godown)
                                        <option value="{{ $godown['id'] }}">{{ $godown['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select id="customer_id" class="form-control select2 form-control-sm"
                                    style="width: 100%;">
                                    <option disabled="true">-select-</option>
                                    <option value="all" selected>All</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="user_id" class="form-label">Staff</label>
                                <select id="user_id" class="form-control select2 form-control-sm"
                                    style="width: 100%;">
                                    <option disabled="true">-select-</option>
                                    <option value="all" selected>All</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" class="form-control select2 form-control-sm"
                                    style="width: 100%;">
                                    <option value="" disabled>-select-</option>
                                    <option value="all" selected>All</option>
                                    <option value="cancel">Cancel</option>
                                    <option value="billed">Billed</option>
                                    <option value="pending">Pending</option>
                                    <option value="return">Return</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button type="button" id="searchButton" data-url="{{ route('report.sale.list') }}"
                                class="btn btn-info">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card card-dark mt-3">

                <div class="card-body table-responsive" id="resultsContainer" {{-- style="overflow-y: auto; max-height: 400px;" --}}>
                    <table class="table table-bordered table-striped table-sm" id="resultsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Invoice#</th>
                                <th>Customer</th>
                                <th>Mobile</th>
                                <th>Created By</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <!-- Dynamic Rows Will Be Appended Here -->
                        </tbody>
                    </table>
                    <!-- Loader -->
                    <div id="loader" class="text-center mt-3" style="display: none;">
                        <span class="spinner-border text-primary" role="status"></span>
                        <p>Loading more data...</p>
                    </div>
                </div>
                {{-- <div class="card-footer">
                    <div class="float-left">
                        <strong>Total Amount:</strong> <span id="totalAmount">0.00</span>
                    </div>

                </div> --}}
            </div>
        </div>
    </section>
</div>

@push('custom-script')
    <script src="{{ asset('js/common/global.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script type="module" src="{{ asset('js/reports/sale.js') }}"></script>
@endpush

@endsection
