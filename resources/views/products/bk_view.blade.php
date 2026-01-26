@extends('layouts.layout')
@section('title', '| View Products')
@section('content')
@section('custom_styles')
    <!-- DataTables -->
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
                    <h1>View Products</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Products</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Manage Products</h3>
                <a href="javascript:void(0);" data-url="{{ route('store.product') }}"
                    class="btn btn-success btn-sm ml-auto add-product">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>

            <div class="card-body">
                <!-- Flash Messages -->
                @include('flash_messages')

                <!-- Products Table -->
                <div class="table-responsive">
                    <table id="productsTable" data-url="{{ route('products.list') }}"
                        class="table table-bordered table-striped table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Stock</th>
                                {{-- <th>Cost</th>
                                <th>Selling Price</th> --}}
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<div id="unit-data" data-units='@json($units)' style="display:none;"></div>
@include('products.add_product_modal')
@endsection

@push('custom-script')
<!-- Rest of your scripts -->
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/products/product.js') }}"></script>
<script type="module" src="{{ asset('js/products/unit_information.js') }}"></script>
@endpush
