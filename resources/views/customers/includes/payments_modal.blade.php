<!-- Button to trigger modal -->

<style>
    .select2-container .select2-selection--single {
        height: 30px !important;
        /* Match the input field size */
        font-size: 14px;
        /* Ensure the font size matches other inputs */
        line-height: 30px !important;
        /* Align the text vertically */
        padding: 2px 8px;
        /* Add some padding */
        border-radius: 6px;
        /* Match the rounded corners */
        border: 1px solid #ced4da;
        /* Default border */
        background-color: #fff;
        /* Background color */
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        /* Align text with the input */
        font-size: 14px;
        /* Font size consistency */
        color: #212529;
        /* Text color */
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 30px !important;
        /* Match the select height */
        width: 30px;
        /* Maintain consistent size */
    }

    .select2-container--default .select2-selection--single {
        border-color: #ced4da !important;
        /* Default border for Select2 */
    }

    .select2-container--default .select2-selection--single:focus {
        border-color: #007bff !important;
        /* Blue border on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        /* Add focus effect */
    }

    .ui-autocomplete {
        z-index: 1051 !important;
        /* Ensure it appears above modals */
        background: #ffffff;
        border: 1px solid #dcdcdc;
        border-radius: 4px;
        max-height: 300px;
        /* Limit dropdown height */
        overflow-y: auto;
        /* Enable scrolling if too many items */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 0;
    }

    .ui-menu-item {
        padding: 10px 15px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .ui-menu-item:hover {
        background-color: #007bff;
        /* Highlight background color */
        color: #fff;
        /* Highlight text color */
    }

    .autocomplete-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .autocomplete-item strong {
        font-weight: bold;
        color: #333;
    }

    .autocomplete-details {
        font-size: 12px;
        color: #666;
    }
</style>
<!-- Modal -->
<div class="modal fade" id="customerPaymentModal" tabindex="-1" role="dialog" aria-labelledby="customerPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="customerPaymentModalLabel"> <i class="fa fa-wallet"></i> Customer
                    Payments</h5>
                <button type="button" id="addCustomer" data-url="{{ route('load.customer.form') }}"
                    data-saveCustomerUrl="{{ route('create.customer') }}" class="close text-white" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="customerPaymentForm" action="">
                <div class="modal-body">
                    <div class="row">
                        <!-- Voucher Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_no">Voucher#</label>
                                <input type="text" id="invoice_no"
                                    value="{{ @$customerPayments->invoice_no ?? @$voucher_no }}" name="invoice_no"
                                    class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="datepicker">Date</label>

                                <input type="text" id="datepicker" name="date"
                                    class="form-control datepicker form-control-sm" placeholder="DD-MM-YYYY"
                                    value="{{ @$customerPayments ? date('d-m-Y', strtotime($customerPayments->date)) : date('d-m-Y') }}"
                                    readonly>

                            </div>
                        </div>

                        <!-- Customers -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="searchCustomer">Customers</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Search Customer"
                                    name="customerName" id="searchCustomer"
                                    value="{{ @$customerPayments->customers->name ?? '' }}">
                                <input type="hidden" id="customer_id" name="customer_id"
                                    value="{{ @$customerPayments->customer_id ?? '' }}">
                                <span style="font-size: 12px;"><b>Balance:</b><span class="customerBalance"
                                        style="color: red;">{{ @$balance }}</span></span>
                            </div>

                        </div>

                        <!-- Balance Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="balanceType">Balance Type</label>
                                <select id="balanceType" name="type" class="form-control select2">
                                    <option selected disabled>-Select-</option>
                                    @foreach (['credit' => 'Credit', 'debit' => 'Debit'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('type', $customerPayments->type ?? '') === $value ? 'selected' : '' }}>
                                            {{ strtoupper($label) }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="text" id="amount" name="amount" class="form-control form-control-sm"
                                    placeholder="Enter Amount" value="{{@$customerPayments->amount ?? ''}}">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-control form-control-sm"
                                    placeholder="Enter Description...">{!!@$customerPayments->description!!}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i>
                        Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
