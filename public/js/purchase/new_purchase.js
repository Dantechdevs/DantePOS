// Import functions from sales_utilities.js
import {
    initializeAutocompleteForSearch, updateRowTotal,

} from '../purchase/purchase_utilities.js';

import {
    initializeSupplierAutocomplete,
} from '../common/utilities.js';
import { submitForm } from '../utilities/utilities.js';

initializeFunctions();
function initializeFunctions() {
    // Initialize the datepicker
    $('#datepicker').datepicker({
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy'
    });

    // Initialize Select2 elements
    $('.select2').select2({
        placeholder: 'Select an option', // Placeholder text
        allowClear: true, // Allow clearing the selection
    });

    $('.select2bs4').select2({
        theme: 'bootstrap4', // Use the Bootstrap 4 theme
        placeholder: 'Select an option', // Placeholder text
        allowClear: true, // Allow clearing the selection
    });

    // Call sales utilities functions
    // initializeQuantityListeners();
    initializeAutocompleteForSearch('#searchItem'); // Replace with your product search input selector
    initializeSupplierAutocomplete('#supplierName'); // Replace with your customer search input selector
    // Additional logic or utilities
    console.log('All functions initialized!');
}


$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Prevent form auto-submit when scanning a barcode
    $(document).on("keydown", "#purchaseForm input[name='barcode']", function (e) {
        if (e.key === "Enter") {
            // Prevent the default behavior of the Enter key
            e.preventDefault();
        }
    });
    // Add event handler for unit selection change
            $("#purchaseItems tr:last .unit-select").on('change',function() {
                const selectedOption = $(this).find('option:selected');
                const purchasePrice = selectedOption.data('purchase-price');
                const sellingPrice = selectedOption.data('selling-price');

                // Update the price input (using purchase price by default)
                $(this).closest('tr').find('.unit-price').val(purchasePrice);

                // Update the total
                updateRowTotal($(this).closest('tr'));
            });
    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#purchaseForm",
        reloadTableSelector: null,
        modalSelector: null,
        successToastMessage: "Purchase added successfully.",
        extraFieldUpdates: function (response) {
        },
        onSuccessCallback: function (response) {
            if (response.success) {
                // const message = response.sale_type === 'new' ? 'Purchase added successfully!' : 'Purchase updated successfully!'
                // showSuccessToast(message);
                setTimeout(function () {
                    window.location.href = response.url;
                }, 1000);
            }
            if (response.errors) {
                // Loop through validation errors and display them in a toaster
                $.each(response.errors, function (field, messages) {
                    messages.forEach(function (message) {
                        showWarningToast(message); // Display each error as a toast
                    });
                });
            }
            // console.log("Custom success logic executed.", response);
        },
        onErrorCallback: function (error) {
            if (error.responseJSON && error.responseJSON.error) {
                const errors = error.responseJSON.error.split('<br>');
                errors.forEach(function (err) {
                    showErrorToast(err.trim());
                });
            } else if (error.status === 422) {
                const validationErrors = error.responseJSON.errors;
                $.each(validationErrors, function (field, messages) {
                    messages.forEach(function (message) {
                        showWarningToast(message); // Display each error as a toast
                    });
                });
            } else {
                showErrorToast("Failed to add purchase. Please try again.");
            }
            // console.error("Custom error logic executed.", error);
        },
        beforeSendCallback: function () {
            // console.log("Custom: Show global loader...");
        },
        completeCallback: function () {
            // removeSpinner(submitButton, originalText);
            // console.log("Custom: Hide global loader...");
        },
        spinnerText: "Submitting...",
        requestOptions: {
            timeout: 150000, // Custom timeout
        },
    });

});
