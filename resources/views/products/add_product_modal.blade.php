<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%) !important;
    }

    .modal-xl {
        max-width: 1200px;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, .05);
    }

    .unit-row {
        border-left: 3px solid #6a11cb;
        transition: all 0.3s ease;
    }

    .unit-row:hover {
        box-shadow: 0 0.15rem 1rem rgba(0, 0, 0, 0.05);
    }

    .font-weight-600 {
        font-weight: 600;
    }

    .font-weight-700 {
        font-weight: 700;
    }

    .required-field::after {
        content: "*";
        color: #e74c3c;
        margin-left: 4px;
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px);
    }

    .form-control,
    .select2-selection {
        border-radius: 0.375rem;
        transition: all 0.3s;
    }

    .form-control:focus,
    .select2-container--focus .select2-selection {
        border-color: #6a11cb;
        box-shadow: 0 0 0 0.2rem rgba(106, 17, 203, 0.15);
    }

    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
    }

    .text-primary {
        color: #6a11cb !important;
    }

    .border-left-primary {
        border-left-color: #6a11cb !important;
    }

    /* Custom Switch Styling */
    .custom-switch-lg .custom-control-label::before {
        width: 3rem;
        height: 1.5rem;
        border-radius: 1rem;
        left: -3.5rem;
    }

    .custom-switch-lg .custom-control-label::after {
        width: calc(1.5rem - 4px);
        height: calc(1.5rem - 4px);
        border-radius: 50%;
        left: calc(-3.5rem + 2px);
    }

    .custom-switch-lg .custom-control-input:checked~.custom-control-label::after {
        transform: translateX(1.5rem);
    }

    .custom-control-label {
        cursor: pointer;
        font-size: 1rem;
        padding-left: 0.5rem;
    }

    .custom-switch-lg {
        padding-left: 4rem;
        min-height: 2rem;
    }
</style>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-primary text-white py-3">
                <h5 class="modal-title font-weight-700" id="addProductModalLabel">
                    <i class="fas fa-cube mr-2"></i>Add New Product
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="productForm" action="{{ route('store.product') }}" novalidate>
                <!-- Modal Body -->
                <div class="modal-body p-5">
                    <!-- Basic Information Section -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h6 class="m-0 font-weight-700 text-primary">
                                <i class="fas fa-info-circle mr-2"></i>Basic Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Product Supplier -->
                                <div class="form-group col-md-4">
                                    <label for="supplier_id" class="font-weight-600 required-field">
                                        <i class="fas fa-layer-group mr-1"></i>Product Supplier
                                    </label>
                                    <select class="form-control select2" name="supplier_id" id="supplier_id" multiple
                                        required>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Please select a supplier.</div>
                                </div>

                                <!-- Product Name -->
                                <div class="form-group col-md-4">
                                    <label for="name" class="font-weight-600 required-field">
                                        <i class="fas fa-tag mr-1"></i>Product Name
                                    </label>
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="e.g. Premium Organic Coffee" required>
                                    <div class="invalid-feedback" id="name_error">Please provide a product name.</div>
                                </div>

                                <!-- Product Description -->
                                <div class="form-group col-md-4">
                                    <label for="description" class="font-weight-600">
                                        <i class="fas fa-align-left mr-1"></i>Product Description
                                    </label>
                                    <textarea class="form-control" name="description" id="description" rows="2"
                                        placeholder="Optional: Add details about the product"></textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Is Scheme Product Checkbox -->
                                <div class="form-group col-md-12">
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="checkbox" class="custom-control-input" name="is_scheme_product"
                                            id="is_scheme_product">
                                        <label class="custom-control-label font-weight-600 text-primary"
                                            for="is_scheme_product">
                                            <i class="fas fa-gift mr-2"></i>Is Scheme Product?
                                        </label>
                                        <small class="form-text text-muted d-block mt-1">
                                            Enable this if this product is part of a special scheme or promotional offer
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Unit Section -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h6 class="m-0 font-weight-700 text-primary">
                                <i class="fas fa-balance-scale mr-2"></i>Pricing & Units
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="multi-unit-section">
                                <!-- Unit Container -->
                                <div id="unit-container">
                                    <div class="unit-row card mb-3 border-left-primary">
                                        <div class="card-body py-3">
                                            <div class="row">
                                                <!-- Unit Selection -->
                                                <div class="col-md-2 mb-3">
                                                    <label class="font-weight-600 required-field">Unit</label>
                                                    <select name="unit_id[]" class="form-control select2 unit-select" required>
                                                        <option value="" selected disabled>Select Unit</option>
                                                        @foreach ($units as $unit)
                                                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">Please select a unit.</div>
                                                </div>

                                                <!-- Conversion Value -->
                                                <div class="col-md-2 mb-3">
                                                    <label class="font-weight-600 required-field">Conversion</label>
                                                    <div class="input-group">
                                                        <input type="number" name="conversion[]"
                                                            class="form-control conversion-value" step="0.01" min="0.01" placeholder="1.00" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text bg-light">units</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">How many base units equals this unit</small>
                                                    <div class="invalid-feedback">Please enter conversion value.</div>
                                                </div>

                                                <!-- Purchase Price -->
                                                <div class="col-md-2 mb-3">
                                                    <label class="font-weight-600 required-field">Cost Price</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-light">{{ $currency }}</span>
                                                        </div>
                                                        <input type="number" name="purchase_price[]"
                                                            class="form-control purchase-price" step="0.01" min="0" placeholder="0.00" required>
                                                    </div>
                                                    <div class="invalid-feedback">Please enter cost price.</div>
                                                </div>

                                                <!-- Selling Price -->
                                                <div class="col-md-2 mb-3">
                                                    <label class="font-weight-600 required-field">Selling Price</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-light">{{ $currency }}</span>
                                                        </div>
                                                        <input type="number" name="selling_price[]"
                                                            class="form-control selling-price" step="0.01" min="0" placeholder="0.00" required>
                                                    </div>
                                                    <div class="invalid-feedback">Please enter selling price.</div>
                                                </div>

                                                <!-- Wholesale Price -->
                                                <div class="col-md-2 mb-3">
                                                    <label class="font-weight-600 required-field">WholeSale Price</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-light">{{ $currency }}</span>
                                                        </div>
                                                        <input type="number" name="wholesale_price[]"
                                                            class="form-control wholesale-price" step="0.01" min="0" placeholder="0.00" required>
                                                    </div>
                                                    <div class="invalid-feedback">Please enter wholesale price.</div>
                                                </div>

                                                <!-- Default Unit Selection -->
                                                <div class="col-md-1 mb-3 d-flex align-items-center">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="default-unit-0" name="default_unit" value="0" class="custom-control-input default-unit-radio" checked>
                                                        <label class="custom-control-label font-weight-600" for="default-unit-0">Default</label>
                                                    </div>
                                                </div>

                                                <!-- Remove Button -->
                                                <div class="col-md-1 mb-3 d-flex align-items-center justify-content-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-unit" disabled>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add Unit Button -->
                                <div class="text-center mt-2">
                                    <button type="button" class="btn btn-primary btn-sm" id="add-unit">
                                        <i class="fas fa-plus-circle mr-2"></i> Add Another Unit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Information Section -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light py-3">
                            <h6 class="m-0 font-weight-700 text-primary">
                                <i class="fas fa-boxes mr-2"></i>Inventory Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Product Code -->
                                <div class="col-md-6 mb-4">
                                    <label for="product_code" class="font-weight-600 required-field">
                                        <i class="fas fa-barcode mr-1"></i>Product Code
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="product_code" id="product_code" placeholder="PRD-001" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="generateCode">
                                                <i class="fas fa-bolt mr-1"></i> Generate
                                            </button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="product_code_error">Please provide a product code.</div>
                                </div>

                                <!-- Quantity -->
                                <div class="col-md-6 mb-4">
                                    <label for="quantity" class="font-weight-600">
                                        <i class="fas fa-cubes mr-1"></i>Stock
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="quantity" id="quantity" placeholder="0" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light" id="base-unit-name">units</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Stock will be recorded in base units</small>
                                    <div class="invalid-feedback" id="quantity_error"></div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Stock Alert Threshold -->
                                <div class="col-md-6 mb-4">
                                    <label for="stock_alert" class="font-weight-600">
                                        <i class="fas fa-bell mr-1"></i>Low Stock Alert
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="stock_alert" id="stock_alert" placeholder="e.g. 10" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light" id="base-unit-name-alert">units</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Get notified when stock is low</small>
                                    <div class="invalid-feedback" id="stock_alert_error"></div>
                                </div>

                                <!-- Expiry Date -->
                                <div class="col-md-6 mb-4">
                                    <label for="expiry_date" class="font-weight-600">
                                        <i class="fas fa-calendar-times mr-1"></i>Expiry Date
                                    </label>
                                    <div class="input-group">
                                        <input type="text" value="{{ date('d-m-Y', strtotime('+30 days')) }}" class="form-control datepicker" name="expiry_date" id="datepicker">
                                    </div>
                                    <small class="form-text text-muted">Leave empty if not applicable</small>
                                    <div class="invalid-feedback" id="expiry_date_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveProduct">
                        <i class="fas fa-save mr-2"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
