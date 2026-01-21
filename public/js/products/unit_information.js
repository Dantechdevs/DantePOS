// unit_information.js - Product Unit Management
$(function () {
    // Configuration
    const config = {
        currency: $('#currency-symbol').data('currency') || '$',
        units: JSON.parse(document.getElementById('unit-data').dataset.units),
        formId: '#productForm',
        containerId: '#unit-container',
        addButtonId: '#add-unit',
        generateCodeId: '#generateCode',
        baseUnitNameId: '#base-unit-name'
    };

    // State management
    const state = {
        unitIndex: 1,
        unitRelationships: {},
        manuallyChangedPrices: new Set()
    };

    // DOM Elements
    const dom = {
        form: $(config.formId),
        container: $(config.containerId),
        addBtn: $(config.addButtonId),
        generateBtn: $(config.generateCodeId),
        baseUnitName: $(config.baseUnitNameId)
    };

    // Initialize the module
    function init() {
        setupEventListeners();
        initSelect2();
        updateBaseUnitName();
        setupUnitRelationships();
    }

    // Event Listeners
    function setupEventListeners() {
        dom.addBtn.on('click', addUnitRow);
        dom.container.on('click', '.remove-unit', removeUnitRow);
        dom.container.on('change', '.default-unit-radio', handleDefaultUnitChange);
        dom.generateBtn.on('click', generateProductCode);
        dom.form.on('submit', handleFormSubmit);

        // Price calculation triggers
        dom.container.on('input', '.purchase-price, .selling-price, .conversion-value', function () {
            setupUnitRelationships();
            calculateDerivedPrices();
        });

        // Additional listener for unit changes
        dom.container.on('change', '.unit-select, .conversion-value', function () {
            setupUnitRelationships();
            updateBaseUnitName();
            calculateDerivedPrices();
        });

        // Modal reset handler
        $('#addProductModal').on('hidden.bs.modal', resetModal);
    }

    // Unit Row Management
    function addUnitRow() {
        const newRow = createUnitRowHtml(state.unitIndex);
        dom.container.append(newRow);

        initSelect2();
        updateRemoveButtons();
        trackManualChanges();

        state.unitIndex++;
    }

    function createUnitRowHtml(index) {
        return `
        <div class="unit-row card mb-3 border-left-primary">
            <div class="card-body py-3">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-600 required-field">Unit</label>
                        <select name="unit_id[]" class="form-control select2 unit-select" required>
                            <option value="" selected disabled>Select Unit</option>
                            ${generateUnitOptions()}
                        </select>
                        <div class="invalid-feedback">Please select a unit.</div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-600 required-field">Conversion</label>
                        <div class="input-group">
                            <input type="number" name="conversion[]"
                                   class="form-control conversion-value"
                                   step="0.01" min="0.01"
                                   placeholder="1.00" required>
                            <div class="input-group-append">
                                <span class="input-group-text bg-light">units</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">How many base units equals this unit</small>
                        <div class="invalid-feedback">Please enter conversion value.</div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-600 required-field">Cost Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">${config.currency}</span>
                            </div>
                            <input type="number" name="purchase_price[]"
                                   class="form-control purchase-price"
                                   step="0.01" min="0"
                                   placeholder="0.00" required>
                        </div>
                        <div class="invalid-feedback">Please enter cost price.</div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-600 required-field">Selling Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">${config.currency}</span>
                            </div>
                            <input type="number" name="selling_price[]"
                                   class="form-control selling-price"
                                   step="0.01" min="0"
                                   placeholder="0.00" required>
                        </div>
                        <div class="invalid-feedback">Please enter selling price.</div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-600 required-field">Wholesale Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">${config.currency}</span>
                            </div>
                            <input type="number" name="wholesale_price[]"
                                   class="form-control wholesale-price"
                                   step="0.01" min="0"
                                   placeholder="0.00" required>
                        </div>
                        <div class="invalid-feedback">Please enter wholesale price.</div>
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-center">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="default-unit-${index}"
                                   name="default_unit" value="${index}"
                                   class="custom-control-input default-unit-radio">
                            <label class="custom-control-label font-weight-600"
                                   for="default-unit-${index}">Default</label>
                        </div>
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-center justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-unit">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }

    function removeUnitRow() {
        if ($('.unit-row').length > 1) {
            $(this).closest('.unit-row').remove();
            updateRemoveButtons();

            if ($('.default-unit-radio:checked').length === 0) {
                $('.default-unit-radio').first().prop('checked', true);
                updateBaseUnitName();
            }

            setupUnitRelationships();
            calculateDerivedPrices();
        }
    }

    function updateRemoveButtons() {
        $('.remove-unit').prop('disabled', $('.unit-row').length === 1);
    }

    // Unit Relationships
    function setupUnitRelationships() {
        state.unitRelationships = {};

        $('.unit-row').each(function () {
            const unitId = $(this).find('.unit-select').val();
            if (!unitId) return;

            const conversion = parseFloat($(this).find('.conversion-value').val()) || 1;
            const unitName = $(this).find('.unit-select option:selected').text();

            state.unitRelationships[unitId] = {
                row: $(this),
                conversion: conversion,
                name: unitName,
                isDefault: $(this).find('.default-unit-radio').is(':checked')
            };
        });

        // Build hierarchical relationships
        Object.keys(state.unitRelationships).forEach(unitId => {
            const unit = state.unitRelationships[unitId];
            Object.keys(state.unitRelationships).forEach(otherUnitId => {
                if (unitId === otherUnitId) return;
                const otherUnit = state.unitRelationships[otherUnitId];

                if (otherUnit.conversion > unit.conversion &&
                    otherUnit.conversion % unit.conversion === 0) {
                    otherUnit.parentId = unitId;
                }
            });
        });
    }

    // Price Calculations
    function calculateDerivedPrices() {
        const defaultUnit = getDefaultUnit();
        if (!defaultUnit) return;

        const smallestUnit = getSmallestUnit();

        $('.unit-row').each(function () {
            const currentUnitId = $(this).find('.unit-select').val();
            if (currentUnitId === defaultUnit.id) return;

            const currentRow = $(this);
            const currentConversion = parseFloat(currentRow.find('.conversion-value').val()) || 1;

            // Calculate based on the default unit but display in smallest unit
            updatePriceField(currentRow, '.purchase-price', defaultUnit.purchasePrice, currentConversion, defaultUnit.conversion);
            updatePriceField(currentRow, '.selling-price', defaultUnit.sellingPrice, currentConversion, defaultUnit.conversion);
        });

        // Update stock display to use smallest unit
        updateBaseUnitName();
    }

    function getDefaultUnit() {
        const defaultRow = $('.default-unit-radio:checked').closest('.unit-row');
        if (!defaultRow.length) return null;

        const unitId = defaultRow.find('.unit-select').val();
        return {
            id: unitId,
            conversion: parseFloat(defaultRow.find('.conversion-value').val()) || 1,
            purchasePrice: parseFloat(defaultRow.find('.purchase-price').val()) || 0,
            sellingPrice: parseFloat(defaultRow.find('.selling-price').val()) || 0
        };
    }

    function updatePriceField(row, fieldSelector, basePrice, currentConversion, baseConversion) {
        const input = row.find(fieldSelector);
        if (state.manuallyChangedPrices.has(input[0]) && input.val() !== '') return;

        if (basePrice > 0) {
            const calculatedPrice = (basePrice * (currentConversion / baseConversion)).toFixed(2);
            input.val(calculatedPrice);
        }
    }

    // Helper Functions
    function generateUnitOptions() {
        return config.units.map(unit =>
            `<option value="${unit.id}">${unit.name}</option>`
        ).join('');
    }

    function updateBaseUnitName() {
        const smallestUnit = getSmallestUnit();
        dom.baseUnitName.text(smallestUnit ? smallestUnit.name : 'units');
        $('#base-unit-name-alert').text(smallestUnit ? smallestUnit.name : 'units');
    }

    // Find the smallest unit in the current setup
    function getSmallestUnit() {
        let smallestUnit = null;
        let smallestConversion = Infinity;

        $('.unit-row').each(function () {
            const conversion = parseFloat($(this).find('.conversion-value').val()) || 1;
            const unitName = $(this).find('.unit-select option:selected').text();

            if (conversion < smallestConversion) {
                smallestConversion = conversion;
                smallestUnit = {
                    name: unitName,
                    conversion: conversion
                };
            }
        });

        return smallestUnit;
    }

    function generateProductCode() {
        // const randomCode = 'PRD-' + Math.floor(1000 + Math.random() * 9000);
        const randomCode = Math.floor(1000 + Math.random() * 9000);
        $('#product_code').val(randomCode);
    }

    function trackManualChanges() {
        $('.purchase-price, .selling-price').off('input').on('input', function () {
            state.manuallyChangedPrices.add(this);
        });
    }

    function initSelect2() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: "-select-",
            allowClear: false
        }).on('change', function () {
            if ($(this).closest('.unit-row').find('.default-unit-radio').is(':checked')) {
                updateBaseUnitName();
            }
            setupUnitRelationships();
            calculateDerivedPrices();
        });
    }

    function handleDefaultUnitChange() {
        updateBaseUnitName();
        setupUnitRelationships();
        calculateDerivedPrices();
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        dom.form.addClass('was-validated');

        if (dom.form[0].checkValidity()) {
            // Submit form via AJAX or standard submission
            // dom.form[0].submit();
        }
    }

    function resetModal() {
        $('#addProductModalLabel').html('<i class="fas fa-cube mr-2"></i>Add New Product');
        dom.form.attr('method', 'POST');
        $('input[name="_method"]').remove();
        dom.form[0].reset();

        // Reset unit rows
        $('.unit-row').not(':first').remove();
        $('.unit-row:first').find('.remove-unit').prop('disabled', true);
        $('.unit-row:first').find('.default-unit-radio').prop('checked', true).val('0');

        // Reset state
        state.unitIndex = 1;
        state.manuallyChangedPrices.clear();

        updateBaseUnitName();
    }

    // Edit Product Handler
    $(document).on('click', '.editProduct', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.get(btn.data('url'), function (response) {
            if (response.success) {
                setupEditModal(response.product, response.unitInfo, btn.data('update-url'));
                $('#addProductModal').modal('show');
            } else {
                toastr.error('Failed to load product data');
            }
        }).fail(() => {
            toastr.error('Failed to load product data');
        }).always(() => {
            btn.prop('disabled', false).html('<i class="fas fa-pen"></i>');
        });
    });

    function setupEditModal(product, unitInfo, updateUrl) {
        console.log("Update URL:", updateUrl); // Debugging

        // Update modal title
        $('#addProductModalLabel').html('<i class="fas fa-edit mr-2"></i>Edit Product');

        // Clear any existing method override
        $('input[name="_method"]').remove();

        // Update form action and method
        const form = $('#productForm');
        form.attr('action', updateUrl);
        form.append('<input type="hidden" name="_method" value="PUT">');

        // Debugging - log the current form action
        console.log("Current form action:", form.attr('action'));

        // Set basic product info
        $('#name').val(product.name);
        $('#name_ur').val(product.name_ur || '');

        // Set scheme product checkbox
        if (product.is_scheme_product) {
            $('#is_scheme_product').prop('checked', true);
        } else {
            $('#is_scheme_product').prop('checked', false);
        }

        $('#product_code').val(product.product_code);
        $('#quantity').val(product.quantity);
        $('#stock_alert').val(product.stock_alert);
        $('#datepicker').val(product.expiry_date ? moment(product.expiry_date).format('DD-MM-YYYY') : '');

        // Set supplier if exists
        // console.log(product.supplier_id)
        // if (product.supplier_id) {
        //     $('#supplier_id').val(product.supplier_id).trigger('change');
        // }

        if (product.supplier_id) {
            // Handle supplier_id whether it's single value, multiple, or empty
            const supplierIds = product.supplier_id
                ? product.supplier_id.toString()
                    .split(',')
                    .map(id => id.trim())
                    .filter(id => id !== '')
                : [];

            // Set the values (val() handles empty arrays appropriately)
            $('#supplier_id').val(supplierIds.length > 0 ? supplierIds : null).trigger('change');
        }


        // Clear existing unit rows except first
        $('.unit-row').not(':first').remove();
        $('.unit-row:first').find('.remove-unit').prop('disabled', true);

        // Find default unit index
        const defaultUnitIndex = unitInfo.findIndex(unit => unit.is_default);

        // Populate first unit row
        const firstRow = $('.unit-row:first');
        populateUnitRow(firstRow, unitInfo[0], 0, 0 === defaultUnitIndex);

        // Add additional unit rows
        for (let i = 1; i < unitInfo.length; i++) {
            addUnitRow();
            const newRow = $('.unit-row').last();
            populateUnitRow(newRow, unitInfo[i], i, i === defaultUnitIndex);
        }

        updateBaseUnitName();
        setupUnitRelationships();
        calculateDerivedPrices();
    }

    function populateUnitRow(row, unitData, index, isDefault) {
        row.find('.unit-select').val(unitData.unit_id).trigger('change');
        row.find('.conversion-value').val(unitData.conversion);
        row.find('.purchase-price').val(unitData.purchase_price);
        row.find('.selling-price').val(unitData.selling_price);
        row.find('.wholesale-price').val(unitData.wholesale_price);
        row.find('.default-unit-radio').prop('checked', isDefault).val(index.toString());

        if (isDefault) {
            state.manuallyChangedPrices.add(row.find('.purchase-price')[0]);
            state.manuallyChangedPrices.add(row.find('.selling-price')[0]);
        }
    }

    // Initialize the module
    init();
});
