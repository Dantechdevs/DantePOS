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
<div class="modal fade" id="supplierPaymentModal" tabindex="-1" role="dialog" aria-labelledby="supplierPaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="supplierPaymentModalLabel"> <i class="fa fa-wallet"></i> Supplier
                    Payments</h5>
                <button type="button" id="addSupplier" data-url="{{ route('load.supplier.form') }}"
                    data-saveSupplierUrl="{{ route('create.supplier') }}" class="close text-white" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="supplierPaymentForm" action="">
                <div class="modal-body">
                    <div class="row">
                        <!-- Voucher Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_no">Voucher#</label>
                                <input type="text" id="purchase_no"
                                    value="{{ @$supplierPayment->purchase_no ?? @$voucher_no }}" name="purchase_no"
                                    class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="datepicker">Date</label>

                                <input type="text" id="datepicker" name="date"
                                    class="form-control datepicker form-control-sm" placeholder="DD-MM-YYYY"
                                    value="{{ @$supplierPayment ? date('d-m-Y', strtotime($supplierPayment->date)) : date('d-m-Y') }}"
                                    readonly>

                            </div>
                        </div>

                        <!-- suppliers -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="supplierName">Supplier</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Search Supplier"
                                    name="supplierName" id="supplierName"
                                    value="{{ @$supplierPayment->supplier->name ?? '' }}">
                                <input type="hidden" id="supplier_id" name="supplier_id"
                                    value="{{ @$supplierPayment->supplier_id ?? '' }}">
                                <span style="font-size: 12px;"><b>Balance:</b><span class="supplierBalance"
                                        style="color: red;">{{ @$balance }}</span></span>
                            </div>

                        </div>


                        <!-- Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="text" id="amount" name="amount" class="form-control form-control-sm"
                                    placeholder="Enter Amount" value="{{@$supplierPayment->amount ?? ''}}">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-control form-control-sm"
                                    placeholder="Enter Description...">{!!@$supplierPayment->description!!}</textarea>
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
