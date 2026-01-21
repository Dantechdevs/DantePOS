@extends('layouts.layout')
@section('title', '| Staff Sales Report')
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
                    <h1>Staff Sales Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Staff Sales Report</li>
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
                {{-- <div class="card-header">
                    <h3 class="card-title">Search Filters</h3>
                </div> --}}
                <div class="card-body">
                    <form id="customerPaymentByStaffForm">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">From Date</label>
                            <div class="col-sm-4">
                                <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm"
                                    id="startDate" readonly>
                            </div>
                            <label class="col-sm-2 col-form-label">To Date</label>
                            <div class="col-sm-4">
                                <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm"
                                    id="endDate" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Staff</label>
                            <div class="col-sm-4">
                                <select id="user_id" class="form-control select2 form-control-sm"
                                    style="width: 100%;">
                                    <option selected="selected" disabled="true">-Select-</option>
                                    <option value="all">All</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="button" id="searchButton" class="btn btn-info">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <div class="card card-dark mt-3">

                <div class="card-body table-responsive" id="resultsContainer" {{-- style="overflow-y: auto; max-height: 400px;" --}}>
                    <table class="table table-bordered table-striped" id="resultsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Invoice#</th>
                                <th>Customer Name</th>
                                <th>Created By</th>
                                <th>Total Amount</th>
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
                <div class="card-footer">
                    <div class="float-left">
                        <strong>Total Amount:</strong> <span id="totalAmount">0.00</span>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

@push('custom-script')
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="{{ asset('js/reports/staff_sales.js') }}"></script>
@endpush

@endsection
