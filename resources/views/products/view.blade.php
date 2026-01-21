@extends('layouts.layout')
@section('title', '| View Products')
@section('content')
@section('custom_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/datatables_styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trash.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu&display=swap" rel="stylesheet">
    <style>
        .urdu-input {
            font-family: 'Noto Nastaliq Urdu', 'Noori Nastaliq', sans-serif;
            direction: rtl;
            font-size: 18px;
            text-align: right;
        }
    </style>
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
                                <th>Supplier</th>
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
<script>
    $(function() {
        var cost = parseFloat($('#cost').val()) || 0;
        var qtyPerUnit = parseFloat($('#qtyPerUnit').val()) || 1;
        displayPurchasePrice(cost, qtyPerUnit);
        $(document).on('keyup', '#cost, #qtyPerUnit', function() {
            var cost = parseFloat($('#cost').val()) || 0;
            var qtyPerUnit = parseFloat($('#qtyPerUnit').val()) ||
            1; // default to 1 if value is invalid or empty

            displayPurchasePrice(cost, qtyPerUnit);
        });

        function displayPurchasePrice(cost, qtyPerUnit) {
            if (qtyPerUnit === 0) {
                // Prevent division by zero
                return;
            }

            var total = cost / qtyPerUnit;
            $('#item_cost').val(total.toFixed(2));
        }

        $(document).on('keyup', '#selling_price, #qtyPerUnit', function() {
            var selling_price = parseFloat($('#selling_price').val()) || 0;
            var qtyPerUnit = parseFloat($('#qtyPerUnit').val()) ||
            1; // default to 1 if value is invalid or empty

            if (qtyPerUnit === 0) {
                // Prevent division by zero
                return;
            }

            var total = selling_price / qtyPerUnit;
            $('#item_selling_price').val(total.toFixed(2));
        });

        $('#qtyPerUnit').on('input', function() {
            var value = $(this).val(); // Get the current value of the input

            // Check if the value is a valid number and greater than 0
            if ($.isNumeric(value) && parseFloat(value) > 0) {
                $("#qtyPerUnitValidation").text('');
                $(".saveProductBtn").prop('disabled', false);
            } else {
                $("#qtyPerUnitValidation").text('Qty Per Unit should be greater than 0');
                $(".saveProductBtn").prop('disabled', true);
            }
        });
    });
</script>

<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/products/product.js') }}"></script>
<script type="module" src="{{ asset('js/products/unit_information.js') }}"></script>
<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
@endpush
