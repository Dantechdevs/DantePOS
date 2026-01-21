@extends('layouts.layout')
@section('title', '| View Products')
@section('content')
@section('custom_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/datatables_styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trash.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-keyboard@latest/build/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu&display=swap" rel="stylesheet">

    <style>

        .urdu-input {
    font-family: 'Noto Nastaliq Urdu', 'Arial Unicode MS';
    direction: rtl;
    font-size: 18px;
    text-align: right;
}

.keyboard-container {
    display: none;
    margin-top: 10px;
}

.hg-theme-default {
    font-family: 'Noto Nastaliq Urdu', 'Arial Unicode MS';
    direction: rtl;
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
<!-- Load jQuery first -->


<!-- Then SimpleKeyboard -->
<script src="https://cdn.jsdelivr.net/npm/simple-keyboard@latest/build/index.min.js"></script>

<script>
// Wait for both DOM and SimpleKeyboard to be ready
$(document).ready(function() {
    let keyboard;
    const $urduInput = $('#name_ur');
    const $keyboardContainer = $('#urdu-keyboard-container');

    // Pre-initialize keyboard when modal opens (if using modal)
    $('.add-product').on('click', function() {
        if (typeof window.SimpleKeyboard !== 'undefined' && !keyboard) {
            initializeKeyboard();
        }
    });

    function initializeKeyboard() {
        try {
            // Clear any existing keyboard
            $keyboardContainer.empty();

            keyboard = new window.SimpleKeyboard.default({
                onChange: input => $urduInput.val(input),
                onKeyPress: button => {
                    if (button === "{shift}" || button === "{lock}") handleShift();
                },
                layout: {
                    'default': [
                        'ق و ع ر ت ی ؤ پ ء ا س د ف گ ہ ج ک ل',
                        'ز ط ح خ ص ث ض ذ ش چ م ن ظ غ ۃ',
                        '{space} {bksp}'
                    ]
                },
                theme: "hg-theme-default",
                physicalKeyboardHighlight: true,
                preventMouseDownDefault: true
            });

            // Attach keyboard to container
            $keyboardContainer.append(keyboard.getContainer());
            return true;
        } catch (e) {
            console.error('Keyboard initialization failed:', e);
            $urduInput.removeAttr('readonly');
            return false;
        }
    }

    function handleShift() {
        if (!keyboard) return;
        const newLayout = keyboard.options.layoutName === "default" ? "shift" : "default";
        keyboard.setOptions({ layoutName: newLayout });
    }

    // Show keyboard when Urdu input is focused
    $urduInput.on('focus', function() {
        if (!keyboard) {
            if (!initializeKeyboard()) {
                return;
            }
        }
        $keyboardContainer.show();
    });

    // Hide keyboard when clicking elsewhere
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#name_ur, #urdu-keyboard-container').length) {
            $keyboardContainer.hide();
        }
    });
});
</script>

<!-- Rest of your scripts -->
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/products/product.js') }}"></script>
<script type="module" src="{{ asset('js/products/unit_information.js') }}"></script>
@endpush
