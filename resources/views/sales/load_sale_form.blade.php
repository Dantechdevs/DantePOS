<style>
    .header-info-bar {
        gap: 10px;
    }

    .user-clock-container {
        white-space: nowrap;
    }

    .live-clock {
        font-family: monospace;
    }

    @media (max-width: 576px) {
        .search-container {
            order: -1;
            margin-bottom: 8px;
        }

        .user-clock-container {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
<!-- Header Info Bar -->
<div class="header-info-bar d-flex flex-wrap align-items-center justify-content-between p-2 bg-white border-bottom">

    <!-- Invoice Search - Right aligned -->
    <div class="search-container mt-2 mt-sm-0" style="min-width: 200px; width: 100%; max-width: 400px;">
        <div class="input-group">
            <input type="text" class="form-control border-end-0" placeholder="Search invoice..." id="invoiceSearch">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary border-start-0" type="button" id="searchInvoiceBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- User Info and Clock - Left aligned -->
    <div class="user-clock-container d-flex align-items-center me-auto">
        <div class="user-name fw-medium me-3">
            {{ @$username }}
        </div>
        <div class="live-clock px-2" id="liveClock">
            11:22:33 AM
        </div>
    </div>


</div>
<div id="customerModalContainer"></div>
<!-- Form -->
<form action="{{ route('post.sale') }}" method="post" id="saleForm">
    <input type="hidden" name="sale_type" value="new">
    <!-- Invoice Details -->
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
                        style="background-color: #D8FDCD;" name="invoice_no" id="invoice_no" value="{{ $invoice_no }}"
                        readonly>
                </div>

                <!-- Date -->
                <div class="form-group col-6 col-md-3">
                    <label for="datepicker" class="small font-weight-bold">Invoice Date <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm datepicker" name="date" id="datepicker"
                        placeholder="DD-MM-YYYY" value="{{ date('d-m-Y') }}" readonly>
                </div>
                <!-- Date -->
                <div class="form-group col-6 col-md-3">
                    <label for="datepicker" class="small font-weight-bold">Due Date <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm datepicker" name="due_date" id="endDate"
                        placeholder="DD-MM-YYYY" value="{{ date('d-m-Y') }}" readonly>
                </div>

                <!-- Status -->
                <div class="form-group col-6 col-md-3">
                    <label for="status" class="small font-weight-bold">Status <span
                            class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control form-control-sm select2">
                        <option disabled>Select</option>
                        <option value="2">Pending</option>
                        <option value="1" selected>Billed</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Customer Balance (Hidden on small screens) -->
                <div class="form-group col-md-4 d-none d-md-block">
                    <label for="customerBalance" class="small font-weight-bold">Godowns <span
                            class="text-danger">*</span></label>
                    <select class="form-control form-control-sm select2" id="godown_id" name="godown_id">
                        {{-- <option value="">-select-</option> --}}
                        @foreach ($godowns as $godown)
                            <option value="{{ $godown->id }}">{{ $godown->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer Search (Full width on mobile) -->
                <div class="form-group col-12 col-md-4">
                    <label for="searchCustomer" class="small font-weight-bold">Customer <span
                            class="text-danger">*</span></label>
                    <div class="input-group input-group-sm searchUsers">
                        <input type="text" class="form-control form-control-sm" placeholder="Search Customer"
                            name="customerName" id="searchCustomer" value="{{ @$walkinCustomer->name }}">
                        <input type="hidden" id="customer_id" name="customer_id" value="{{ @$walkinCustomer->id }}">
                        <input type="hidden" id="area_id" name="area_id" value="{{ @$walkinCustomer->area_id }}">
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
                        <span class="customerBalance badge badge-info"></span>
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

    <!-- Product Search -->
    <div class="card-body p-2">
        <div class="form-group mb-2">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                </div>
                <input type="text" class="form-control" name="barcode" id="searchItem"
                    placeholder="Item name / Barcode / Itemcode">
            </div>
        </div>

        <!-- Items Table - Scrollable on mobile -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-2">
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
                    <!-- Dynamic rows will be appended here -->
                </tbody>
            </table>
        </div>

        <!-- Summary Section - Stacked on mobile -->
        <div class="row">
            <!-- Left Column - Inputs -->
            <div class="col-md-6 order-2 order-md-1">
                <div class="row">
                    <div class="form-group col-6 col-md-6">
                        <label for="otherCharges" class="small font-weight-bold">Other Charges</label>
                        <input type="number" id="otherCharges" class="form-control form-control-sm text-right"
                            placeholder="Additional charges" value="0">
                    </div>

                    <div class="form-group col-6 col-md-6">
                        <label for="globalDiscount" class="small font-weight-bold">Discount</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="globalDiscount" class="form-control form-control-sm text-right"
                                name="discount" placeholder="Discount" value="0">
                            <div class="input-group-append">
                                <select id="discountType" name="discount_type" class="form-control form-control-sm">
                                    <option value="percentage">%</option>
                                    <option value="fixed">Fixed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="row">
                    <div class="form-group paymentTypeDiv col-12">
                        <label for="payment_type" class="small font-weight-bold">Payment Type <span
                                class="text-danger">*</span></label>
                        <select name="payment_type" id="payment_type" class="form-control form-control-sm select2">
                            <option disabled>Select</option>
                            <option value="cash" selected>Cash</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                </div> --}}

                <div class="form-group">
                    <label for="note" class="small font-weight-bold">Notes</label>
                    <textarea id="note" class="form-control form-control-sm" rows="2" name="description"
                        placeholder="Optional notes"></textarea>
                </div>
                <div id="invoicePaymentHistory" style="display: none;">
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
                                    <tr>
                                        <td colspan="3" class="text-center">No payments recorded</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Add Payment Form (simplified) -->
                    {{-- <div class="add-payment-form mt-3">
                        <h6 class="font-weight-bold border-bottom pb-2">Add New Payment</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Amount (PKR)</label>
                                    <input type="number" class="form-control form-control-sm" id="newPaymentAmount"
                                        step="0.01" min="0">
                                </div>
                            </div>

                        </div>
                    </div> --}}
                </div>

            </div>

            <!-- Right Column - Summary -->
            <div class="col-md-6 order-1 order-md-2 mb-3 mb-md-0">
                <div class="card">
                    <div class="card-body p-2">
                        <table class="table table-sm summary-table mb-0">
                            <tr>
                                <th class="small text-left">Subtotal:</th>
                                <td class="text-right"><span id="subtotal">0.00</span><input type="hidden"
                                        id="subtotalVal" name="sub_total" value="0"></td>
                            </tr>
                            <tr>
                                <th class="small text-left">Other Charges:</th>
                                <td class="text-right"><span id="otherChargesTotal">0.00</span><input type="hidden"
                                        id="otherChargesTotalVal" name="other_charges" value="0"></td>
                            </tr>
                            <tr>
                                <th class="small text-left">Discount:</th>
                                <td class="text-right"><span id="discountTotal">0.00</span><input type="hidden"
                                        id="discountVal" name="discount_amount" value="0"></td>
                            </tr>
                            <tr class="font-weight-bold">
                                <th class="small text-left">Total Payable:</th>
                                <td class="text-right"><span id="grandTotal">0.00</span><input type="hidden"
                                        id="grandTotalVal" name="grand_total" value="0"></td>
                            </tr>


                        </table>
                        <!-- Payment Section -->
                        <div class="p-3 border-top">
                            <div class="form-group mb-2">
                                <label for="amountPaid" class="small font-weight-bold">Amount Paid </label>
                                <input type="number" class="form-control form-control-lg text-right shadow-sm"
                                    id="amountPaid" name="paid_amount" placeholder="0.00" value="0"
                                    min="0" step="0.01" autocomplete="off">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small font-weight-medium">Balance Due:</span>
                                <span class="font-weight-bold text-danger" id="balanceAmount">0.00</span>
                                <input type="hidden" id="balanceAmountValue" name="balance_amount" value="0">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small font-weight-medium">Change:</span>
                                <span class="font-weight-bold text-success" id="changeAmount">0.00</span>
                                <input type="hidden" id="changeAmountValue" name="change_amount" value="0">
                            </div>

                            <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold"
                                id="saveSaleBtn">
                                <i class="fas fa-save mr-2"></i> SAVE SALE
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>
