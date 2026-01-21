@extends('layouts.layout')
@section('title', '| Items Purchase Report')
@section('content')

    <style type="text/css">
        .customerLoader {
            display: none;
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, -50%);
        }

        .select2-container .select2-selection--single {
            height: 30px !important;
            font-size: 14px;
            line-height: 30px !important;
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            background-color: #fff;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            font-size: 14px;
            color: #212529;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
            width: 30px;
        }

        .select2-container--default .select2-selection--single {
            border-color: #ced4da !important;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        }

        .report-summary {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .summary-label {
            font-weight: 600;
            color: #495057;
        }

        .summary-value {
            font-weight: 700;
            color: #007bff;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }
    </style>

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Items Purchase Report</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Items Purchase Report</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Report Filters Card -->
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title">Purchase Report Filters</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="startDate" class="col-sm-2 col-form-label">From Date</label>
                            <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" name="startDate" id="startDate"
                                       value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <label for="endDate" class="col-sm-2 col-form-label">To Date</label>
                            <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" name="endDate" id="endDate"
                                       value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="supplier_id" class="col-sm-2 col-form-label">Supplier</label>
                            <div class="col-sm-4">
                                <select name="supplier_id" id="supplier_id" class="form-control form-control-sm select2">
                                    <option value="all">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="product_id" class="col-sm-2 col-form-label">Product</label>
                            <div class="col-sm-4">
                                <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                    <option value="all">All Products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="status" class="col-sm-2 col-form-label">Status</label>
                            <div class="col-sm-4">
                                <select name="status" id="status" class="form-control form-control-sm">
                                    <option value="all">All Status</option>
                                    <option value="received">Received</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" id="show_report" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <button type="button" id="reset_filters" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Filters
                        </button>
                        <button type="button" id="export_excel" class="btn btn-success float-right">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>

                <!-- Report Summary Card -->
                <div class="card card-info" id="report_summary" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Report Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="report-summary">
                                    <div class="summary-item">
                                        <span class="summary-label">Total Purchases:</span>
                                        <span class="summary-value" id="total_purchases">0</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Total Quantity:</span>
                                        <span class="summary-value" id="total_quantity">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="report-summary">
                                    <div class="summary-item">
                                        <span class="summary-label">Total Amount:</span>
                                        <span class="summary-value" id="total_amount">$0.00</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Average Cost:</span>
                                        <span class="summary-value" id="average_cost">$0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="report-summary">
                                    <div class="summary-item">
                                        <span class="summary-label">Total Discount:</span>
                                        <span class="summary-value" id="total_discount">$0.00</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Net Amount:</span>
                                        <span class="summary-value" id="net_amount">$0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="report-summary">
                                    <div class="summary-item">
                                        <span class="summary-label">Date Range:</span>
                                        <span class="summary-value" id="date_range">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Generated On:</span>
                                        <span class="summary-value" id="generated_on">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Generating report...</p>
                </div>

                <!-- Report Results Card -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Purchase Items Report</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="purchaseItemReportTable" class="table table-bordered table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr class="bg-gradient-dark">
                                        <th>#</th>
                                        <th>Purchase Date</th>
                                        <th>Purchase No</th>
                                        <th>Supplier</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th>Total Cost</th>
                                        <th>Discount</th>
                                        <th>Net Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <th colspan="5" style="text-align:right">Grand Total:</th>
                                        <th id="footer_total_quantity">0</th>
                                        <th></th>
                                        <th id="footer_total_cost">$0.00</th>
                                        <th id="footer_total_discount">$0.00</th>
                                        <th id="footer_net_amount">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
@endsection

@push('custom-script')
    <script src="{{ asset('js/common/global.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script type="module" src="{{ asset('js/reports/purchase_item_report.js') }}"></script>
@endpush
