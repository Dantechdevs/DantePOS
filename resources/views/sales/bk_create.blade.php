@extends('layouts.layout')
@section('title', '| Add Sale')
@section('content')
    <style>
        .ui-autocomplete {
            position: absolute;
            z-index: 1000;
            cursor: default;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #ccc;
            background-color: #fff;
            padding: 5px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .ui-autocomplete .ui-menu-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .ui-autocomplete .ui-menu-item:hover {
            background-color: #f5f5f5;
        }

        .field_wrapper .form-control-sm {
            padding: 0.25rem 0.5rem;
            /* Adjust padding for smaller inputs */
            font-size: 0.875rem;
            /* Smaller font size */
        }

        .field_wrapper .btn-sm {
            padding: 0.25rem 0.5rem;
            /* Adjust padding for smaller buttons */
            font-size: 0.875rem;
            /* Smaller font size */
        }

        .table thead th {
            text-align: center;
            vertical-align: middle;
        }

        .table tbody td input {
            padding: 0.3rem 0.5rem;
            font-size: 0.875rem;
        }

        .quantity-decrease,
        .quantity-increase {
            width: 30px;
            height: 30px;
            line-height: 16px;
            text-align: center;
            padding: 0;
            border-radius: 50%;
        }

        .quantity {
            width: 60px;
            text-align: center;
        }

        .total-amount {
            font-weight: bold;
            color: #28a745;
        }

        .remove-item {
            background-color: #e74c3c;
            border: none;
            color: white;
        }
    </style>

    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Add Sale</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Add Sale</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Flash Messages -->
                @if (Session::has('flash_message_error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{!! session('flash_message_error') !!}</strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (Session::has('flash_message_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{!! session('flash_message_success') !!}</strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Form -->
                <form action="{{ url('/add-sale') }}" method="post" id="purchaseForm">
                    @csrf
                    <!-- Invoice Details -->
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Sale Details</h3>
                            <a href="{{ url('/sales') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-list"></i> Sales List
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="invoice_no">Invoice No# <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-success text-white" name="invoice_no"
                                        id="invoice_no" value="{{ $invoice_no }}" readonly>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker" name="date" id="datepicker"
                                        placeholder="DD-MM-YYYY">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="customerName">Customer <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Search Customer"
                                        name="customerName" id="customerName">
                                    <input type="hidden" name="customer_id" id="customer_id">
                                    <input type="hidden" name="area_id" id="area_id">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="chkCustomerBalance">Balance</label>
                                    <input type="text" class="form-control bg-success text-white" id="chkCustomerBalance"
                                        readonly>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control select2">
                                        <option disabled selected>Select Status</option>
                                        <option value="2">Pending</option>
                                        <option value="1">Confirmed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="details">Description</label>
                                <textarea class="form-control" id="details" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                </div>
                                <input type="text" class="form-control" id="searchItem"
                                    placeholder="Item name / Barcode / Itemcode">
                            </div>
                        </div>

                        <!-- Items Table -->
                        <table class="table table-sm table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="saleItems">

                            </tbody>
                        </table>

                        <!-- Summary Section -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="otherCharges">Other Charges</label>
                                    <input type="number" id="otherCharges"
                                        class="form-control form-control-sm text-right" placeholder="0.00">
                                </div>
                                <div class="form-group">
                                    <label for="globalDiscount">Discount on All</label>
                                    <div class="input-group">
                                        <input type="number" id="globalDiscount"
                                            class="form-control form-control-sm text-right" placeholder="0.00">
                                        <div class="input-group-append">
                                            <select id="discountType" class="form-control form-control-sm">
                                                <option value="percentage">%</option>
                                                <option value="fixed">Fixed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea id="note" class="form-control form-control-sm" rows="2" placeholder="Optional"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th class="text-right">Subtotal:</th>
                                        <td class="text-right"><span id="subtotal">2.50</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-right">Other Charges:</th>
                                        <td class="text-right"><span id="otherChargesTotal">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-right">Discount on All:</th>
                                        <td class="text-right"><span id="discountTotal">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-right">Grand Total:</th>
                                        <td class="text-right"><strong id="grandTotal">2.50</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>


                    <!-- Submit Button -->
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Sale</button>
                    </div>
                </form>
            </div>
        </section>
    </div>



@endsection

@push('custom-script')
    <!-- External JavaScript -->
    <script src="{{ asset('js/sales/new_sale.js') }}"></script>
@endpush
