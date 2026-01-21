@extends('layouts.layout')
@section('title', '| Customer Payments By Staff Report')
@section('content')
    <style>
        .table-header,
        .table-body>.row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }

        .table-header {
            font-weight: bold;
            background-color: #f4f4f4;
            padding: 15px 0;
        }

        .table-body>.row:hover {
            background-color: #f9f9f9;
        }

        .col {
            flex: 1;
            text-align: left;
            padding: 0 10px;
        }

        .text-danger {
            color: red;
        }

        .text-success {
            color: green;
        }

        #resultsContainer {
            overflow-y: auto;
            /* Enable scrolling */
            max-height: 400px;
            /* Fixed height for scroll area */
            border: 1px solid #ddd;
            padding: 10px;
        }

        #resultsContainer::-webkit-scrollbar {
            display: none;
            /* Hide scrollbar for modern browsers */
        }
        .select2-container .select2-selection--single {
        height: 30px !important;
        /* Match the input field size */
        font-size: 14px;
        /* Ensure the font size matches other inputs */
        line-height: 30px !important;
        /* Align the text vertically */
        padding: 2px 8px;
        /* Add some padding */
        border-radius: 6px;
        /* Match the rounded corners */
        border: 1px solid #ced4da;
        /* Default border */
        background-color: #fff;
        /* Background color */
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        /* Align text with the input */
        font-size: 14px;
        /* Font size consistency */
        color: #212529;
        /* Text color */
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 30px !important;
        /* Match the select height */
        width: 30px;
        /* Maintain consistent size */
    }

    .select2-container--default .select2-selection--single {
        border-color: #ced4da !important;
        /* Default border for Select2 */
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #007bff !important;
        /* Blue border on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        /* Add focus effect */
    }
    </style>
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Customer Payments By Staff Report</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Customer Payments By Staff Report</li>
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
                    <div class="card-header">
                        <h3 class="card-title">Search Filters</h3>
                    </div>
                    <div class="card-body">
                        <form id="customerPaymentByStaffForm">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">From Date</label>
                                <div class="col-sm-4">
                                    <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm" id="startDate"
                                        readonly>
                                </div>
                                <label class="col-sm-2 col-form-label">To Date</label>
                                <div class="col-sm-4">
                                    <input type="text" placeholder="DD-MM-YYYY" class="form-control form-control-sm" id="endDate"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Staff</label>
                                <div class="col-sm-4">
                                    <select id="user_id" class="form-control select2 form-control-sm" style="width: 100%;">
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

                <!-- Results Table -->
                {{-- <div class="card card-dark mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Search Results</h3>
                    </div>
                    <div id="resultsContainer" class="card-body" style="overflow-y: auto; max-height: 400px;">
                        <div class="table-header d-flex">
                            <div class="col">Invoice No</div>
                            <div class="col">Date</div>
                            <div class="col">Customer</div>
                            <div class="col">Staff</div>
                            <div class="col">Type</div>
                            <div class="col">Description</div>
                            <div class="col">Amount</div>
                        </div>
                        <div id="resultsBody" class="table-body">
                            <!-- Dynamic Rows Will Be Appended Here -->
                        </div>
                        <!-- Loader -->
                        <div id="loader" class="text-center mt-3" style="display: none;">
                            <span class="spinner-border text-primary" role="status"></span>
                            <p>Loading more data...</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div><strong>Total Amount:</strong> <span id="totalAmount">0.00</span></div>
                            <button type="button" id="exportPdf" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Generate PDF
                            </button>
                        </div>
                    </div>
                </div> --}}


                <div class="card card-dark mt-3">
                    {{-- <div class="card-header">
                        <h3 class="card-title">Search Results</h3>
                    </div> --}}
                    <div class="card-body table-responsive" id="resultsContainer"
                        style="overflow-y: auto; max-height: 400px;">
                        <table class="table table-bordered table-striped" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Staff</th>
                                    <th>Type</th>
                                    <th>Description</th>
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
        <script src="{{ asset('js/reports/customer_payment_by_staff.js') }}"></script>
    @endpush

@endsection
