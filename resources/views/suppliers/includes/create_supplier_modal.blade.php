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
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addSupplierModalLabel">
                    <i class="fas fa-user-plus"></i> Add New Supplier
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSupplierForm" action="">
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="name" class="font-weight-bold">Supplier Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name"
                                placeholder="Enter supplier name" value="{{ @$supplier->name }}" autocomplete="name">
                        </div>
                        <!-- Email -->
                        <div class="form-group col-md-6">
                            <label for="email" class="font-weight-bold">Email</label>
                            <input type="text" class="form-control form-control-sm" id="email" name="email"
                                placeholder="Enter Email" value="{{ @$supplier->email }}" autocomplete="email">
                        </div>
                    </div>
                    <div class="row">


                        <!-- Phone -->
                        <div class="form-group col-md-6">
                            <label for="mobile" class="font-weight-bold">Phone <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="mobile" name="mobile"
                                placeholder="Enter phone number" value="{{ @$supplier->mobile }}" autocomplete="tel">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="national_id" class="font-weight-bold">National ID</label>
                            <input type="text" class="form-control form-control-sm" id="national_id"
                                name="national_id" placeholder="Enter supplier National ID"
                                value="{{ @$supplier->national_id }}" autocomplete="off">
                        </div>
                    </div>

                    <!-- Days Selection -->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="days" class="font-weight-bold">Available Days <span
                                    class="text-danger">*</span></label>
                            <select class="form-control form-control-sm select2" multiple="true" id="days"
                                name="available_days[]">
                                @php
                                    $days = [
                                        'monday',
                                        'tuesday',
                                        'wednesday',
                                        'thursday',
                                        'friday',
                                        'saturday',
                                        'sunday',
                                    ];
                                @endphp
                                <option value="monday"
                                    {{ in_array('monday', (array) @$available_days) ? 'selected' : '' }}>Monday
                                </option>
                                <option value="tuesday"
                                    {{ in_array('tuesday', (array) @$available_days) ? 'selected' : '' }}>
                                    Tuesday</option>
                                <option value="wednesday"
                                    {{ in_array('wednesday', (array) @$available_days) ? 'selected' : '' }}>
                                    Wednesday</option>
                                <option value="thursday"
                                    {{ in_array('thursday', (array) @$available_days) ? 'selected' : '' }}>
                                    Thursday</option>
                                <option value="friday"
                                    {{ in_array('friday', (array) @$available_days) ? 'selected' : '' }}>Friday
                                </option>
                                <option value="saturday"
                                    {{ in_array('saturday', (array) @$available_days) ? 'selected' : '' }}>
                                    Saturday</option>
                                <option value="sunday"
                                    {{ in_array('sunday', (array) @$available_days) ? 'selected' : '' }}>
                                    Sunday</option>
                            </select>
                            <small class="form-text text-muted">Select one or more days when the supplier is
                                available</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="opening_balance" class="font-weight-bold">Opening Balance</label>
                            <input type="text" class="form-control form-control-sm" id="opening_balance"
                                name="opening_balance" placeholder="Enter supplier National ID"
                                value="{{ @$supplier->opening_balance ?? 0 }}" autocomplete="off">
                        </div>
                    </div>


                    <!-- Additional address -->
                    <div class="row">

                        <div class="form-group col-md-12">
                            <label for="address" class="font-weight-bold">Address</label>
                            <textarea class="form-control form-control-sm" id="address" name="address" rows="3"
                                placeholder="Add any additional address (optional)" autocomplete="address-line1">{!! @$supplier->address !!}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" id="saveSupplier">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
