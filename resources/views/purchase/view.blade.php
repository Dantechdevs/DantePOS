@extends('layouts.layout')
@section('title', '| Purchase Orders')
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
                        <h1>Purchase Orders</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Purchase Orders</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">


                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Purchase Orders</h3>
                            <a href="{{ url('/add-purchase') }}" class="btn btn-block btn-success btn-sm"
                                style="width: 180px; float: right; display: inline-block;">New Order</a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                           @include('flash_messages')

                            <table id="AllPurchaseTable" data-url="{{ route('purchase.list') }}"
                                class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID #</th>
                                        <th>Date</th>
                                        <th>Invoice#</th>
                                        <th>Supplier</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>

@endsection
@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/purchase/purchase.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
@endpush
