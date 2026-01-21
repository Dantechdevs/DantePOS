import {
    handleDeleteAction,
    initializeDataTable
} from '../common/utilities.js';
import { submitForm } from '../utilities/utilities.js';
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('change input blur', 'input[type="number"]', function () {
        const $input = $(this);
        const maxAmount = parseFloat($input.attr('max'));
        const enteredAmount = parseFloat($input.val()) || 0;

        // Validate only if max amount is specified
        if (!isNaN(maxAmount) && enteredAmount > maxAmount) {
            alert(`Payment amount cannot exceed due amount of PKR ${maxAmount.toFixed(2)}`);
            $input.val(maxAmount.toFixed(2));
            $input.trigger('change');
        }
    });

    const salesUrl = $('#AllSalesTable').data('url');

    initializeDataTable({
        tableSelector: '#AllSalesTable',
        ajaxUrl: salesUrl,
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    return '<input type="checkbox" class="sales-checkbox" value="' + row.id + '">';
                },
                orderable: false,
                searchable: false
            },
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'date', name: 'date', title: 'Date' },
            { data: 'status', name: 'status', title: 'status', className: 'text-center' },
            { data: 'invoice_no', name: 'invoice_no', title: 'Invoice#' },
            { data: 'customer', name: 'customer', title: 'Customer', className: 'text-left' },
            { data: 'grand_total', name: 'grand_total', title: 'Total', className: 'text-center' },
            { data: 'paid_amount', name: 'paid_amount', title: 'Paid', className: 'text-center' },
            { data: 'due', name: 'due', title: 'Due', className: 'text-center' },
            { data: 'payment_status', name: 'payment_status', title: 'Payment Status', className: 'text-center' },
            { data: 'createdBy', name: 'createdBy', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
            { data: 'sale_type', name: 'sale_type', visible: false, searchable: true } // Hidden but searchable
        ],
    });


    /***********************Delete Sale *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'AllSalesTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

    /*********************** Pay Due Payment **********************/
    $(document).on('click', '.paySaleDuePayment', function () {
    var url = $(this).data('url');

    $.get(url, function (data) {
        $('#invoiceModalContainer').html(data);
        $('#invoicePaymentModal').modal('show');

        // Initialize payment deletion functionality after modal is shown
        initPaymentDeletion();
    });
});

/*********************** Initialize Payment Deletion **********************/
function initPaymentDeletion() {
    // Remove any existing event handlers to prevent duplication
    $('#deleteConfirmationModal').off('show.bs.modal');
    $('#confirmDelete').off('click');
    $(document).off('click', '.delete-payment');

    // Set up click handlers for delete buttons
    $(document).on('click', '.delete-payment', function (e) {
        e.preventDefault();
        const paymentToDelete = $(this).data('payment-id');
        console.log("Delete button clicked for payment ID:", paymentToDelete);

        // Check if payment ID is null (initial amount)
        if (paymentToDelete === null || paymentToDelete === 'null' || paymentToDelete === '') {
            showPaymentNotification('Initial amount cannot be deleted', 'error');
            return;
        }

        // Store the payment ID in the confirmation modal's data
        $('#deleteConfirmationModal').data('payment-id', paymentToDelete);
        $('#deleteConfirmationModal').modal('show');
    });

    // Handle delete confirmation
    $('#confirmDelete').on('click', function () {
        // Get the payment ID from the modal's data
        const paymentToDelete = $('#deleteConfirmationModal').data('payment-id');

        if (paymentToDelete) {
            console.log("Deleting payment ID:", paymentToDelete);
            deletePaymentRecord(paymentToDelete);
        } else {
            console.error("No payment ID specified for deletion");
            showPaymentNotification('No payment selected for deletion', 'error');
        }
    });

    // Fix scrollability when confirmation modal closes
    $('#deleteConfirmationModal').on('hidden.bs.modal', function () {
        // Re-enable scrolling on the main payment modal
        $('body').addClass('modal-open');
        $('#invoicePaymentModal').css('overflow', 'auto');
    });

    console.log("Payment deletion initialized successfully");
}

/*********************** Delete Payment Record **********************/
function deletePaymentRecord(paymentId) {
    $.ajax({
        url: '/invoice/payment-delete/' + paymentId,
        type: 'DELETE',

        beforeSend: function() {
            // Show loading state
            $('#confirmDelete').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                // Remove the deleted row from the table
                $(`#paymentHistory tr[data-payment-id="${paymentId}"]`).fadeOut(300, function() {
                    $(this).remove();
                    updatePaymentSummary(response.updatedData);
                    showPaymentNotification('Payment record deleted successfully', 'success');
                });
            } else {
                showPaymentNotification('Error deleting payment record: ' + response.message, 'error');
            }
            $('#deleteConfirmationModal').modal('hide');


        },
        error: function(xhr, status, error) {
            let errorMessage = 'An error occurred while deleting the payment record';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showPaymentNotification(errorMessage, 'error');
            $('#deleteConfirmationModal').modal('hide');

        },
        complete: function() {
            // Reset button state
            $('#confirmDelete').html('Delete').prop('disabled', false);
            $('#deleteConfirmationModal').data('payment-id', null);
        }
    });
}


/*********************** Update Payment Summary **********************/
function updatePaymentSummary(updatedData) {
    if (updatedData) {
        $('#amount-paid').text(updatedData.totalPaidFormatted);
        $('#balance-due').text(updatedData.dueAmountFormatted);
        $('#paymentAmount')
            .val(updatedData.dueAmount)
            .attr('max', updatedData.dueAmount);
    }
}

/*********************** Show Payment Notification **********************/
function showPaymentNotification(message, type = 'info') {
    // Remove any existing notifications
    $('.payment-notification').remove();

    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' : 'alert-info';

    const notification = $(
        `<div class="alert ${alertClass} alert-dismissible fade show payment-notification" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`
    );

    $('#invoicePaymentModal .modal-body').prepend(notification);

    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}

    /*********************** Submit Invoice Payment Form **********************/
    submitForm({
        formSelector: "#paymentForm",
        reloadTableSelector: "#AllSalesTable",
        modalSelector: "#invoicePaymentModal",
        successToastMessage: "Payment recorded successfully.",
        extraFieldUpdates: function (response) {
            // Add new payment to history table after successful submission
            if (response.payment) {
                addPaymentToHistory(response.payment);
            }

            // Update payment summary
            if (response.updatedData) {
                updatePaymentSummary(response.updatedData);
            }
        },
        onSuccessCallback: function (response) {
            // console.log("Custom success logic executed.", response);
        },
        onErrorCallback: function (error) {
            // console.error("Custom error logic executed.", error);
            // Show error notification
            let errorMessage = 'An error occurred while processing the payment';
            if (error.responseJSON && error.responseJSON.errors) {
                errorMessage = Object.values(error.responseJSON.errors).join('<br>');
            } else if (error.responseJSON && error.responseJSON.message) {
                errorMessage = error.responseJSON.message;
            }
            showPaymentNotification(errorMessage, 'error');
        },
        beforeSendCallback: function () {
            // console.log("Custom: Show global loader...");
        },
        completeCallback: function () {
            // console.log("Custom: Hide global loader...");
        },
        spinnerText: "Submitting...",
        requestOptions: {
            timeout: 150000, // Custom timeout
        },
    });

    /*********************** Add Payment to History Table **********************/
    function addPaymentToHistory(payment) {
        const formattedDate = new Date(payment.payment_date).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        }).replace(',', ' |');

        const newRow = `
        <tr data-payment-id="${payment.id}">
            <td>${formattedDate}</td>
            <td>${payment.notes || ''}</td>
            <td>$${parseFloat(payment.amount).toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger delete-payment"
                        data-payment-id="${payment.id}"
                        title="Delete Payment">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    `;

        $('#paymentHistory').prepend(newRow);
    }



    /************ Bulk Sale Import *************************/
    $(document).on('click', '#bulkSaleImort', function (e) {
        e.preventDefault();
        const modal = $("#importModal");
        modal.modal({
            backdrop: 'static',  // Prevent closing on outside click
            keyboard: false      // Prevent closing with ESC key
        });

        modal.modal('show'); // Show the modal
    })
    /*********************** Download Sample Bulk sample **********************/
    $(document).on("click", "#downloadSample", function (e) {
        e.preventDefault(); // Prevent default behavior

        const btn = $(this);
        const url = btn.attr('data-url');

        // Disable the button and show a loading indicator
        btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm"></span> Downloading...`);

        // AJAX request
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Create a temporary link element and trigger download
                    const link = document.createElement("a");
                    link.href = response.file_url; // File URL from response
                    link.setAttribute("download", response.file_name); // Suggested filename
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    showSuccessToast(response.message || "File not found!");
                }
            },
            error: function (xhr) {
                console.error("An unexpected error occurred.");
            },
            complete: function () {
                // Restore button state
                btn.prop("disabled", false).html("Download Sample");
            },
        });
    });


    /*********************** Submit Form **********************/
    $(document).on("submit", "#importForm", function (e) {
        e.preventDefault(); // Prevent the default form submission

        const form = $(this);
        const url = form.attr("action");
        const formData = new FormData(this);

        // Disable the button and show a spinner
        const submitButton = form.find("button[type='submit']");
        const originalButtonText = submitButton.html();
        submitButton.prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Uploading...');

        // AJAX request
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData

            success: function (response) {
                if (response.success) {
                    // Success logic
                    showSuccessToast(response.message || "File processed successfully!");
                } else {
                    console.log(response.errors)
                    // Display validation errors using showErrorToast
                    if (response.errors) {
                        response.errors.forEach(error => {
                            showErrorToast(error);
                        });
                    } else {
                        showErrorToast(response.message || "Some validation errors occurred!");
                    }
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    console.log('Validation Error:', xhr.responseJSON.errors);

                    // Ensure errors are properly extracted
                    const errors = xhr.responseJSON?.errors || {};
                    showValidationErrors(errors);
                    // Object.keys(errors).forEach(key => {
                    //     errors[key].forEach(errorMessage => {
                    //         // showErrorToast(errorMessage);
                    //         showValidationErrors(errorMessage);
                    //     });
                    // });
                } else {
                    console.log('Server Error:', xhr);
                    const error = xhr.responseJSON?.error || "An unexpected error occurred.";
                    showValidationErrors(error)
                    // showErrorToast(xhr.responseJSON?.error || "An unexpected error occurred.");
                }
            },
            complete: function () {
                $('#AllSalesTable').DataTable().ajax.reload();
                // Restore the button state
                submitButton.prop("disabled", false).html(originalButtonText);
                // Reset the form
                form[0].reset();

                // Clear the file input
                form.find("input[type='file']").val("");
                $("#importModal").modal('hide');
            },
        });
    });

    function showValidationErrors(errors) {
        let errorContainer = $("#alert-container"); // Change this to your alert container div
        errorContainer.html(''); // Clear previous alerts

        if (typeof errors === "string") {
            // Handle a single error message as a string
            appendError(errors);
        } else if (typeof errors === "object") {
            // Handle multiple errors stored in an object
            Object.keys(errors).forEach(key => {
                let errorMessages = Array.isArray(errors[key]) ? errors[key] : [errors[key]]; // Ensure array
                errorMessages.forEach(errorMessage => appendError(errorMessage));
            });
        }
    }

    function appendError(errorMessage) {
        let errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ${errorMessage}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        $("#alert-container").append(errorHtml);
    }

    // $(document).on("submit", "#importFormxxx", function (e) {
    //     e.preventDefault(); // Prevent the default form submission

    //     const form = $(this);
    //     const url = form.attr("action");
    //     const formData = new FormData(this);

    //     // Disable the button and show a spinner
    //     const submitButton = form.find("button[type='submit']");
    //     const originalButtonText = submitButton.html();
    //     submitButton.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm"></span> Uploading...`);

    //     // AJAX request
    //     $.ajax({
    //         url: url,
    //         type: "POST",
    //         data: formData,
    //         processData: false, // Required for FormData
    //         contentType: false, // Required for FormData

    //         success: function (response) {

    //             if (response.success) {
    //                 // Success logic
    //                 showSuccessToast(response.message || "File processed successfully!");

    //                 // Reload any necessary data tables or UI here
    //             } else {

    //                 // Automatically download the error file
    //                 const link = document.createElement("a");
    //                 link.href = response.error_file_url; // Use the URL from the response
    //                 link.download = response.fileName; // You can customize the file name
    //                 document.body.appendChild(link);
    //                 link.click();
    //                 document.body.removeChild(link);
    //                 showSuccessToast(response.message || "Some Validation Errors!");

    //             }
    //         },
    //         error: function (xhr) {
    //             if (xhr.status === 422 || xhr.status === 400) {
    //                 const blob = new Blob([xhr.responseText], { type: "application/json" });
    //                 const link = document.createElement("a");
    //                 link.href = window.URL.createObjectURL(blob);
    //                 link.download = "errors.json"; // Change file name/extension as needed
    //                 link.click();
    //                 console.log("There were validation errors. Downloading error details...");
    //             } else {
    //                 console.log("An unexpected error occurred.");
    //             }
    //         },
    //         complete: function () {
    //             $('#AllSalesTable').DataTable().ajax.reload();
    //             // Restore the button state
    //             submitButton.prop("disabled", false).html(originalButtonText);
    //             // Reset the form
    //             form[0].reset();

    //             // Clear the file input
    //             form.find("input[type='file']").val("");
    //             $("#importModal").modal('hide');
    //         },
    //     });
    // });

    /************************ Bulk Print *****************************/
    const table = $('#AllSalesTable').DataTable();

    // Select all checkboxes
    $('#select-all').on('click', function () {
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
        togglePrintBulkButton();
    });

    // Handle individual checkbox click
    $('#AllSalesTable tbody').on('change', 'input[type="checkbox"]', function () {
        if (!this.checked) {
            var el = $('#select-all').get(0);
            if (el && el.checked && ('indeterminate' in el)) {
                el.indeterminate = true;
            }
        }
        togglePrintBulkButton();
    });

    // Toggle Print Bulk button visibility
    function togglePrintBulkButton() {
        var anyChecked = $('.sales-checkbox:checked').length > 0;
        $('#print-bulk').toggle(anyChecked);
    }

    // Handle Print Bulk button click
    $('#print-bulk').on('click', function () {
        var selectedSales = [];
        const url = $(this).attr('data-url'); // Get URL from the data attribute

        $('.sales-checkbox:checked').each(function () {
            selectedSales.push($(this).val());
        });

        if (selectedSales.length > 0) {
            // Open the logo selection modal
            $('#logoSelectionModal').modal('show');

            // Handle logo selection
            $('.logo-option').off('click').on('click', function () {
                var selectedLogo = $(this).attr('data-logo'); // Get selected logo URL
                // console.log(selectedLogo);
                // return false;
                $('#logoSelectionModal').modal('hide'); // Close the modal

                // Proceed with printing after selecting the logo
                const finalUrl = `${url}?ids=${selectedSales.join(',')}&logo=${selectedLogo}`;
                window.open(finalUrl, '_blank'); // Open the invoice print URL in a new tab
            });
        }
    });


    // /************************ Bulk Print *****************************/
    // const table = $('#AllSalesTable').DataTable();
    // // Select all checkboxes
    // $('#select-all').on('click', function () {
    //     var rows = table.rows({ 'search': 'applied' }).nodes();
    //     $('input[type="checkbox"]', rows).prop('checked', this.checked);
    //     togglePrintBulkButton();
    // });

    // // Handle individual checkbox click
    // $('#AllSalesTable tbody').on('change', 'input[type="checkbox"]', function () {
    //     if (!this.checked) {
    //         var el = $('#select-all').get(0);
    //         if (el && el.checked && ('indeterminate' in el)) {
    //             el.indeterminate = true;
    //         }
    //     }
    //     togglePrintBulkButton();
    // });

    // // Toggle Print Bulk button visibility
    // function togglePrintBulkButton() {
    //     var anyChecked = $('.sales-checkbox:checked').length > 0;
    //     $('#print-bulk').toggle(anyChecked);
    // }

    // // Handle Print Bulk button click
    // $('#print-bulk').on('click', function () {
    //     var selectedSales = [];
    //     const url = $(this).attr('data-url'); // Get URL from the data attribute

    //     $('.sales-checkbox:checked').each(function () {
    //         selectedSales.push($(this).val());
    //     });

    //     if (selectedSales.length > 0) {
    //         const finalUrl = `${url}?ids=${selectedSales.join(',')}`;
    //         window.open(finalUrl, '_blank'); // Opens the URL in a new tab
    //     }
    // });

});
