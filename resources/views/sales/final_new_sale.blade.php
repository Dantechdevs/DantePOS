@extends('layouts.layout')
@section('title', '| Add Sale')
@section('content')
    <style>
        /* General Styles */
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #212529;
        }

        .content-wrapper {
            padding: 20px;
            background-color: #f8f9fa;
        }

        /* .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        } */
        .card {
    border-radius: 12px 12px 0 0; /* Top-left and top-right rounded, bottom corners square */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }

        .card-body {
            padding: 20px;
            background-color: white;
            border-radius: 0 0 8px 8px;
        }

        .form-control {
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            color: #212529;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        /* Table Styles */
        .table {
            font-size: 0.85rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table thead th {
            text-align: center;
            background-color: #007bff;
            color: white;
            font-size: 14px;
            padding: 12px;
        }

        .table tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            font-size: 14px;
        }

        .table tbody td input {
            padding: 5px;
            font-size: 14px;
            text-align: center;
        }

        .quantity-decrease,
        .quantity-increase {
            width: 30px;
            height: 30px;
            padding: 0;
            text-align: center;
            border-radius: 50%;
        }

        .total-amount {
            font-weight: bold;
            color: #28a745;
        }

        /* Compact Summary Section */
        .summary-section {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-section .form-group label {
            font-size: 12px;
            font-weight: 600;
        }

        .summary-section .form-control-sm {
            font-size: 12px;
            padding: 5px;
        }

        .summary-table th,
        .summary-table td {
            font-size: 12px;
            padding: 4px;
            text-align: right;
        }

        .summary-table th {
            font-weight: bold;
            color: #495057;
        }

        .summary-table tr:last-child td {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
        }

        /* Autocomplete Styling */
        .ui-autocomplete {
            max-height: 150px;
            overflow-y: auto;
            overflow-x: hidden;
            font-size: 12px;
            border: 1px solid #ccc;
            background-color: #fff;
            padding: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .ui-autocomplete .ui-menu-item {
            padding: 5px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .ui-autocomplete .ui-menu-item:hover {
            background-color: #f5f5f5;
            transform: scale(1.02);
        }

        .ui-autocomplete .ui-menu-item span:first-child {
            font-weight: bold;
            color: #ff0000;
        }

        .ui-autocomplete .ui-menu-item span:nth-child(2) {
            font-weight: bold;
            color: green;
        }


        /* Footer Styling */
        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>

<div class="content-wrapper">
    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Form -->
            <form action="{{ url('/add-sale') }}" method="post" id="purchaseForm">
                @csrf

                <!-- Invoice Details -->
                <div class="card shadow mt-1">
                    <div class="card-body">
                        <!-- Small Title -->
                        <h5 class="mb-3">New Sale</h5>

                        <!-- Form Fields -->
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="invoice_no">Invoice No# <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm bg-success text-white" name="invoice_no" id="invoice_no" value="{{ $invoice_no }}" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm datepicker" name="date" id="datepicker" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="customerName">Customer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" placeholder="Search Customer" name="customerName" id="customerName">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="chkCustomerBalance">Balance</label>
                                <input type="text" class="form-control form-control-sm bg-success text-white" id="chkCustomerBalance" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control form-control-sm select2">
                                    <option disabled selected>Select Status</option>
                                    <option value="2">Pending</option>
                                    <option value="1">Confirmed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="card-body">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input type="text" class="form-control" id="searchItem" placeholder="Item name / Barcode / Itemcode">
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
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
                                <!-- Dynamic rows will be appended here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Section -->
                    <div class="row mt-4 summary-section">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="otherCharges">Other Charges</label>
                                <input type="number" id="otherCharges" class="form-control form-control-sm text-right" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label for="globalDiscount">Discount on All</label>
                                <div class="input-group">
                                    <input type="number" id="globalDiscount" class="form-control form-control-sm text-right" placeholder="0.00">
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
                            <table class="table table-borderless summary-table">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td><span id="subtotal">0.00</span></td>
                                </tr>
                                <tr>
                                    <th>Other Charges:</th>
                                    <td><span id="otherChargesTotal">0.00</span></td>
                                </tr>
                                <tr>
                                    <th>Discount on All:</th>
                                    <td><span id="discountTotal">0.00</span></td>
                                </tr>
                                <tr>
                                    <th>Grand Total:</th>
                                    <td><strong id="grandTotal">0.00</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group text-right mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Sale</button>
                </div>
            </form>
        </div>
    </section>
</div>

@endsection

@push('custom-script')
    <script src="{{ asset('js/sales/new_sale.js') }}"></script>
@endpush
