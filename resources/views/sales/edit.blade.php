@extends('layouts.layout')
@section('title', '| Update Sale')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/sale/sales.css') }}">
    <style>
        /* Compact styles */
        .compact-form .form-group {
            margin-bottom: 0.5rem;
        }

        .compact-form .form-control,
        .compact-form .input-group-text,
        .compact-form .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            height: 30px;
        }

        .compact-form .table th,
        .compact-form .table td {
            padding: 0.3rem;
            font-size: 0.8rem;
        }

        .compact-form .card-body {
            padding: 0.75rem;
        }

        .compact-form .select2-container .select2-selection--single {
            height: 30px !important;
        }

        /* Header bar styles */
        .header-info-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
        }

        .invoice-search-container {
            flex: 1;
            margin-right: 10px;
        }

        .user-info-container {
            display: flex;
            align-items: center;
        }

        .user-name {
            margin-right: 15px;
            font-weight: bold;
            color: #333;
        }

        .live-clock {
            font-family: monospace;
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            min-width: 100px;
            text-align: center;
        }

        .customer-balance-container {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .customerBalance {
            font-size: 0.8rem;
            padding: 0.25em 0.4em;
            font-weight: 600;
        }

        /* Marquee effect for stock */
        .marquee-input {
            overflow: hidden;
            white-space: nowrap;
            width: 60px;
            animation: marquee 8s linear infinite;
        }

        @keyframes marquee {
            0% {
                text-indent: 100%
            }

            100% {
                text-indent: -100%
            }
        }
    </style>
@endsection

<div class="content-wrapper">
    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid renderSalesForm" data-loadFormUrl="{{ route('create.sale') }}">
            <div id="customerModalContainer"></div>
            <!-- Form -->
            <form action="{{ route('update.sale', $updateSale['id']) }}" method="post" id="saleForm">

                <input type="hidden" name="sale_type" value="update">
                <!-- Invoice Details -->
                {{-- <div class="card shadow mt-1">
                    <div class="card-body">
                        <!-- Small Title -->
                        <h5 class="mb-3">Update Sale</h5>

                        <!-- Form Fields -->
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="invoice_no">Invoice No# <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm text-black"
                                    style="background-color: #D8FDCD;" name="invoice_no" id="invoice_no"
                                    value="{{ $updateSale['invoice_no'] }}" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="datepicker">Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm datepicker" name="date"
                                    id="datepicker" value="{{ date('d-m-Y', strtotime($updateSale['date'])) }}"
                                    placeholder="DD-MM-YYYY" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="searchCustomer">Customer <span class="text-danger">*</span></label>
                                <div class="input-group searchUsers">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Search Customer"
                                        value="{{ optional($updateSale['customers'])['name'] }}" name="customerName"
                                        id="searchCustomer">
                                    <input type="hidden" id="customer_id" value="{{ $updateSale['customer_id'] }}"
                                        name="customer_id">
                                    <input type="hidden" id="area_id" value="{{ $updateSale['area_id'] }}"
                                        name="area_id">
                                    <div class="input-group-append input-group-append-btn">
                                        <button class="btn btn-sm btn-success" type="button"
                                            data-url="{{ route('load.customer.form') }}"
                                            data-saveCustomerUrl="{{ route('create.customer') }}" id="addCustomer">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="customerBalance">Balance</label>
                                <input type="text" class="form-control form-control-sm text-black"
                                    style="background-color: #D8FDCD;" value="{{ $customerBalance }}"
                                    id="customerBalance" readonly>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control form-control-sm select2">
                                    <option disabled {{ !isset($updateSale['status']) ? 'selected' : '' }}>Select
                                    </option>
                                    @foreach (['0' => 'Cancel', '1' => 'Billed', '2' => 'Pending', '3' => 'Return'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ isset($updateSale['status']) && $updateSale['status'] == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                    </div>
                </div> --}}

                <div class="card shadow mt-1">
                    <div class="card-body p-2">
                        <!-- Small Title -->
                        {{-- <h5 class="mb-3">New Sale</h5> --}}

                        <!-- Form Fields -->
                        <div class="row">
                            <!-- Invoice No -->
                            <div class="form-group col-6 col-md-3">
                                <label for="invoice_no" class="small font-weight-bold">Invoice# <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm text-black"
                                    style="background-color: #D8FDCD;" name="invoice_no" id="invoice_no"
                                    value="{{ $updateSale['invoice_no'] }}" readonly>
                            </div>

                            <!-- Date -->
                            <div class="form-group col-6 col-md-3">
                                <label for="datepicker" class="small font-weight-bold">Invoice Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm datepicker" name="date"
                                    id="datepicker" placeholder="DD-MM-YYYY"
                                    value="{{ date('d-m-Y', strtotime($updateSale['date'])) }}" readonly>
                            </div>

                            <!-- Date -->
                            <div class="form-group col-6 col-md-3">
                                <label for="datepicker" class="small font-weight-bold">Due Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm datepicker" name="due_date"
                                    id="endDate" placeholder="DD-MM-YYYY" value="{{ $due_date }}" readonly>
                            </div>

                            <!-- Status -->
                            <div class="form-group col-6 col-md-3">
                                <label for="status" class="small font-weight-bold">Status <span
                                        class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control form-control-sm select2">
                                    <option disabled {{ !isset($updateSale['status']) ? 'selected' : '' }}>Select
                                    </option>
                                    @foreach (['0' => 'Cancel', '1' => 'Billed', '2' => 'Pending', '3' => 'Return'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ isset($updateSale['status']) && $updateSale['status'] == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Customer Balance (Hidden on small screens) -->
                            <div class="form-group col-md-4 d-none d-md-block">
                                <label for="customerBalance" class="small font-weight-bold">Godowns <span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-control-sm select2" id="godown_id" name="godown_id">
                                    <option value="">-select-</option>
                                    @foreach ($godowns as $godown)
                                        <option value="{{ $godown->id }}"
                                            {{ $updateSale['godown_id'] === $godown->id ? 'selected' : '' }}>
                                            {{ $godown->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Customer Search (Full width on mobile) -->
                            <div class="form-group col-12 col-md-4">
                                <label for="searchCustomer" class="small font-weight-bold">Customer <span
                                        class="text-danger">*</span></label>
                                <div class="input-group input-group-sm searchUsers">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Search Customer" name="customerName" id="searchCustomer"
                                        value="{{ optional($updateSale['customers'])['name'] }}">
                                    <input type="hidden" id="customer_id" name="customer_id"
                                        value="{{ $updateSale['customer_id'] }}">
                                    <input type="hidden" id="area_id" name="area_id"
                                        value="{{ $updateSale['area_id'] }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-success addCustomer" type="button"
                                            data-url="{{ route('load.customer.form') }}"
                                            data-saveCustomerUrl="{{ route('create.customer') }}" id="addCustomer">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Customer balance display -->
                                <div class="customer-balance-container mt-1">
                                    <small class="text-muted">Balance: </small>
                                    <span class="customerBalance badge badge-info">{{ $customerBalance }}</span>
                                </div>
                            </div>



                            <div class="form-group col-6 col-md-3">
                                <label for="status" class="small font-weight-bold">Customer Type <span
                                        class="text-danger">*</span></label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="customerType" value="retail" required checked>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Retail</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="customerType" value="wholesale" required>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Wholesale</span>
                                    </label>

                                </div>
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
                            <input type="text" class="form-control" id="searchItem"
                                placeholder="Item name / Barcode / Itemcode">
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive table-container">
                        <table class="table table-bordered table-sm">
                            <thead class="bg-primary text-white">
                                <tr>

                                    <th width="5%">#</th>
                                    <th width="20%">Item</th>
                                    <th width="10%" class="text-center">Stock</th>
                                    <th width="20%" class="text-center">Qty</th>
                                    <th width="15%" class="text-center">Unit</th>
                                    <th width="10%" class="text-right">Price</th>
                                    <th width="10%" class="text-right">Total</th>
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="saleItems">
                                 @php
                                    $counter = 0;
                                @endphp
                                @foreach ($enhancedItems as $item)
                                @php
                                        $counter++;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span
                                                class="item-counter badge badge-secondary mr-2">{{ $counter }}</span>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control-plaintext text-left"
                                                name="productName[]" value="{{ $item['productName'] }}" readonly>
                                            <input type="hidden" class="productID" name="product_id[]"
                                                value="{{ $item['productID'] }}">

                                            <input type="hidden" class="cost" name="cost[]"
                                                value="{{ $item['cost'] }}">
                                            <input type="hidden" class="calculatedCost" name="calculatedCost[]"
                                                value="{{ $item['calculatedCost'] }}">
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control form-control-sm bg-warning text-center marquee-input"
                                                style="width:100%;" value="{{ $item['stock'] }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger quantity-decrease">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="text" name="quantity[]"
                                                class="form-control form-control-sm quantity text-center d-inline-block"
                                                value="{{ $item['productQty'] }}" min="1"
                                                style="width: 50px;">
                                            <button type="button" class="btn btn-sm btn-success quantity-increase">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-sm unit-select" name="unit[]">
                                                @foreach ($item['unitInfo'] as $unit)
                                                    <option value="{{ $unit['unit_id'] }}"
                                                        data-purchase-price="{{ $unit['purchase_price'] }}"
                                                        data-selling-price="{{ $unit['selling_price'] }}"
                                                        {{ $unit['unit_id'] == $item['unit_id'] ? 'selected' : '' }}>
                                                        {{ $unit['unit'] }}
                                                        ({{ $unit['purchase_price'] }}/{{ $unit['selling_price'] }})
                                                    </option>
                                                @endforeach
                                            </select>

                                        </td>
                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm text-right unit-price unitPrice"
                                                name="selling_price[]" value="{{ @$item['sellingPrice'] }}"
                                                step="0.01">
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control-plaintext total-amount text-right" name="amount[]"
                                                value="{{ $item['amount'] }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Dynamic rows will be appended here -->
                            </tbody>

                        </table>
                    </div>

                    <!-- Summary Section -->
                    <!-- Summary Section -->
                    <div class="row ">
                        <div class="col-md-6 order-2 order-md-1">
                            {{-- <h5>Summary</h5> --}}
                            <div class="row">
                                <div class="form-group col-6 col-md-6">
                                    <label for="otherCharges" class="small font-weight-bold">Other Charges</label>
                                    <input type="number" id="otherCharges"
                                        class="form-control form-control-sm text-right"
                                        value="{{ $updateSale['other_charges'] }}"
                                        placeholder="Enter additional charges">
                                </div>
                                <div class="form-group col-6 col-md-6">
                                    <label for="globalDiscount" class="small font-weight-bold">Discount</label>
                                    <div class="input-group">
                                        <input type="number" id="globalDiscount"
                                            class="form-control form-control-sm text-right" name="discount"
                                            placeholder="Enter discount" value="{{ $updateSale['discount'] }}">
                                        <div class="input-group-append">
                                            <select id="discountType" name="discount_type"
                                                class="form-control form-control-sm">
                                                @php
                                                    // Map the database values to dropdown labels
                                                    $discountTypes = [
                                                        'percentage' => '%',
                                                        'fixed' => 'Fixed',
                                                    ];
                                                @endphp

                                                @foreach ($discountTypes as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ isset($updateSale['discount_type']) && $updateSale['discount_type'] == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label for="note" class="small font-weight-bold">Notes</label>
                                <textarea id="note" class="form-control form-control-sm" rows="2" name="description"
                                    placeholder="Optional notes">{!! $updateSale['description'] !!}</textarea>
                            </div>

                            <div id="invoicePaymentHistory">
                                <!-- Payment History Section -->
                                <div class="payment-history mt-3">
                                    <h6 class="font-weight-bold border-bottom pb-2">Payment History</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered" id="paymentHistoryTable">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th class="text-right">Amount (PKR)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="paymentHistoryBody">
                                                <!-- This will be populated dynamically -->
                                                @forelse ($invoicePayments as $payment)
                                                    <tr>
                                                        <td class="text-left">
                                                            {{ date('d-m-Y | h:i A', strtotime($payment['payment_date'])) }}
                                                        </td>
                                                        <td class="text-right">{{ $payment['amount'] }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">No payments recorded
                                                        </td>
                                                    </tr>
                                                @endforelse

                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                            </div>

                        </div>
                        <!------ old summary table ---------------->
                        {{-- <div class="col-md-6">
                            <table class="table summary-table">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td><span id="subtotal">{{ $updateSale['sub_total'] }}</span><input
                                            type="hidden" id="subtotalVal" name="sub_total"
                                            value="{{ $updateSale['sub_total'] }}"></td>
                                </tr>
                                <tr>
                                    <th>Other Charges:</th>
                                    <td><span id="otherChargesTotal">{{ $updateSale['other_charges'] }}</span><input
                                            type="hidden" id="otherChargesTotalVal" name="other_charges"
                                            value="{{ $updateSale['other_charges'] }}"></td>
                                </tr>
                                <tr>
                                    <th>Discount:</th>
                                    <td><span id="discountTotal">{{ $updateSale['discount_amount'] }}</span><input
                                            type="hidden" id="discountVal" name="discount_amount"
                                            value="{{ $updateSale['discount_amount'] }}"></td>
                                </tr>
                                <tr>
                                    <th>Grand Total:</th>
                                    <td><strong id="grandTotal">{{ $updateSale['grand_total'] }}</strong><input
                                            type="hidden" id="grandTotalVal" name="grand_total"
                                            value="{{ $updateSale['grand_total'] }}"></td>
                                </tr>
                            </table>
                            <div class="col-md-12 text-right mt-3">
                                <button type="submit" class="btn btn-info add-sale-button btn-sm"><i
                                        class="fas fa-save"></i>
                                    Update Sale</button>
                            </div>
                        </div> --}}
                        <!------ old summary table ---------------->

                        <!-- Right Column - Summary -->
                        <div class="col-md-6 order-1 order-md-2 mb-3 mb-md-0">
                            <div class="card">
                                <div class="card-body p-2">
                                    <table class="table table-sm summary-table mb-0">
                                        <tr>
                                            <th class="small text-left">Subtotal:</th>
                                            <td class="text-right"><span
                                                    id="subtotal">{{ $updateSale['sub_total'] }}</span><input
                                                    type="hidden" id="subtotalVal" name="sub_total"
                                                    value="{{ $updateSale['sub_total'] }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="small text-left">Other Charges:</th>
                                            <td class="text-right"><span
                                                    id="otherChargesTotal">{{ $updateSale['other_charges'] }}</span><input
                                                    type="hidden" id="otherChargesTotalVal" name="other_charges"
                                                    value="{{ $updateSale['other_charges'] }}"></td>
                                        </tr>
                                        <tr>
                                            <th class="small text-left">Discount:</th>
                                            <td class="text-right"><span
                                                    id="discountTotal">{{ $updateSale['discount_amount'] }}</span><input
                                                    type="hidden" id="discountVal" name="discount_amount"
                                                    value="{{ $updateSale['discount_amount'] }}"></td>
                                        </tr>
                                        <tr class="font-weight-bold">
                                            <th class="small text-left">Total Payable:</th>
                                            <td class="text-right"><span
                                                    id="grandTotal">{{ $updateSale['grand_total'] }}</span><input
                                                    type="hidden" id="grandTotalVal" name="grand_total"
                                                    value="{{ $updateSale['grand_total'] }}"></td>
                                        </tr>


                                    </table>
                                    <!-- Payment Section -->
                                    <div class="p-3 border-top">
                                        <div class="form-group mb-2">
                                            <label for="amountPaid" class="small font-weight-bold">Amount Paid
                                            </label>
                                            <input type="number"
                                                class="form-control form-control-lg text-right shadow-sm"
                                                id="amountPaid" name="paid_amount" placeholder="0.00"
                                                value="{{ $paidAmount }}" min="0" step="0.01"
                                                autocomplete="off">
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="small font-weight-medium">Balance Due:</span>
                                            <span class="font-weight-bold text-danger"
                                                id="balanceAmount">{{ $updateSale['balance_amount'] }}</span>
                                            <input type="hidden" id="balanceAmountValue" name="balance_amount"
                                                value="0">
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="small font-weight-medium">Change:</span>
                                            <span class="font-weight-bold text-success"
                                                id="changeAmount">{{ $updateSale['change_amount'] }}</span>
                                            <input type="hidden" id="changeAmountValue" name="change_amount"
                                                value="0">
                                        </div>

                                        <button type="submit" class="btn btn-info btn-block py-2 font-weight-bold"
                                            id="saveSaleBtn">
                                            <i class="fas fa-save mr-2"></i> UPDATE SALE
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!------ end right summary column ---------->

                    </div>

                </div>


            </form>
        </div>
    </section>
</div>

@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/customer/customer.js') }}"></script>
<script type="module" src="{{ asset('js/sales/new_sale.js') }}"></script>
<script type="module" src="{{ asset('js/sales/sale_calculations.js') }}"></script>
@endpush
