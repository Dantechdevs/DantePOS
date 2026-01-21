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
</style>
<style>
        .urdu-input {
            font-family: 'Noto Nastaliq Urdu', 'Noori Nastaliq', sans-serif;
            direction: rtl;
            font-size: 18px;
            text-align: right;
        }
    </style>
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="fas fa-user-plus"></i> Add New Customer
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCustomerForm" action="">
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="name" class="font-weight-bold">Customer Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                placeholder="Enter customer name" value="{{ @$customer->name }}" autocomplete="name">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="name_ur" class="font-weight-bold">Customer Name Urdu </label>
                            <input type="text" class="form-control form-control-sm text-right urdu-input" id="name_ur" name="name_ur"
                                placeholder="نام درج کریں" value="{{ @$customer->name_ur }}" autocomplete="name_ur">
                        </div>
                    </div>
                    <div class="row">
                        <!-- Email -->
                        <div class="form-group col-md-6">
                            <label for="email" class="font-weight-bold">Email</label>
                            <input type="text" class="form-control form-control-sm" id="email" name="email"
                                placeholder="Enter Email" value="{{ @$customer->email }}" autocomplete="email">
                        </div>

                        <!-- Phone -->
                        <div class="form-group col-md-6">
                            <label for="mobile" class="font-weight-bold">Phone
                                {{-- <span class="text-danger">*</span> --}}
                                </label>
                            <input type="text" class="form-control form-control-sm" id="mobile" name="mobile"
                                placeholder="Enter phone number" value="{{ @$customer->mobile }}" autocomplete="tel">
                        </div>
                    </div>

                    <div class="row">
                        <!-- National ID -->
                        <div class="form-group col-md-6">
                            <label for="national_id" class="font-weight-bold">National ID</label>
                            <input type="text" class="form-control form-control-sm" id="national_id"
                                name="national_id" placeholder="Enter customer National ID"
                                value="{{ @$customer->national_id }}" autocomplete="off">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="opening_balance" class="font-weight-bold">Opening Balance</label>
                            <input type="text" class="form-control form-control-sm" id="opening_balance"
                                name="opening_balance" placeholder="Enter customer National ID"
                                value="{{ @$customer->opening_balance  ?? 0 }}" autocomplete="off">
                        </div>


                    </div>

                    <!-- Additional address -->
                    <div class="row">
                        <!-- Area -->
                        <div class="form-group col-md-6">
                            <label for="form_area_id" class="font-weight-bold">Area
                                {{-- <span class="text-danger">*</span> --}}
                            </label>
                            <select class="form-control select2" id="form_area_id" name="area_id" style="width: 100%;"
                                autocomplete="address-level2">
                                <option disabled selected>Select Area</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}"
                                        {{ isset($customer->area) && $customer->area->name == $area->name ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="address" class="font-weight-bold">Address</label>
                            <textarea class="form-control form-control-sm" id="address" name="address" rows="3"
                                placeholder="Add any additional address (optional)" autocomplete="address-line1">{!! @$customer->address !!}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" id="saveCustomer">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
