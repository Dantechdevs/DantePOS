@extends('layouts.layout')
@section('title', '| View Sales')
@section('content')

@section('custom_styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/datatables_styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trash.css') }}">
@endsection

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sales</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Sales List</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-12">
                <div id="invoiceModalContainer"></div>
                <div id="alert-container"></div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">View Sales</h3>
                        <div class="float-right">
                            <button id="print-bulk" class="btn btn-danger btn-sm"
                                data-url="{{ route('bulk.print.sales') }}" style="display: none;">Print Bulk</button>
                            {{-- <button class="btn btn-primary btn-sm" id="downloadSample" style="width: 180px;"
                                data-url="{{ route('download.bulk.sale.sample') }}">
                                <i class="fas fa-download"></i> Download Sample
                            </button> --}}
                            {{-- <button class="btn btn-info btn-sm" id="bulkSaleImort" style="width: 180px;">
                                <i class="fas fa-upload"></i> Import Bulk
                            </button> --}}
                            <a href="{{ route('create.sale') }}" class="btn btn-success btn-sm" style="width: 180px;">
                                <i class="fas fa-plus-square"></i> New Sale
                            </a>

                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="AllSalesTable" data-url="{{ route('sales.list') }}"
                                class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Invoice#</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th>Payment Status</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>
                        </div>

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    @include('sales.includes.import')
    @include('sales.includes.select_logo')
    <!-- /.content -->
</div>

@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/sales/sale.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
@endpush
