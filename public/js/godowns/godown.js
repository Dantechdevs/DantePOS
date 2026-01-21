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

    const productsUrl = $('#godownsTable').data('url');

    initializeDataTable({
        tableSelector: '#godownsTable',
        ajaxUrl: productsUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'Godown' },
            { data: 'status', name: 'status', title: 'Status' },
            { data: 'createdBy', name: 'createdBy', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });

    // Show add modal
    $(document).on('click', '.add-product', function() {
        $('#godownForm')[0].reset();
        $('#godown_id').val('');
        var url = $(this).attr('data-url');
        $('#godownForm').attr('action', url);
        $('#saveGodownBtn').html('<i class="fas fa-sync-alt"></i> Save');
        $('#godownModalLabel').text('Add New Godown');
        $('#godownModal').modal('show');
    });

    submitForm({
        formSelector: "#godownForm",
        reloadTableSelector: null,
        modalSelector: null,
        successToastMessage: "Godown added successfully.",
        extraFieldUpdates: function (response) {
        },
        onSuccessCallback: function (response) {
            if (response.success) {
                $('#godownModal').modal('hide');

                setTimeout(function () {
                    $('#godownsTable').DataTable().ajax.reload();
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
                showErrorToast("Failed to add Product. Please try again.");
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

    // Form submission
    $('#godownFormxxx').on('submit',function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action') || $('.add-product').data('url');
        const method = $('#godown_id').val() ? 'PUT' : 'POST';
        const button = $('#saveGodownBtn');

        // Client-side validation
        if (!form[0].checkValidity()) {
            form.addClass('was-validated');
            return false;
        }

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: url,
            type: method,
            data: form.serialize(),
            success: function(response) {
                $('#godownModal').modal('hide');
                showToast(response.success ? 'success' : 'error', response.message);
                $('#godownsTable').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                if (errors && errors.name) {
                    $('#godown_name').addClass('is-invalid');
                    $('#godown_name_error').text(errors.name[0]).show();
                }
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Godown');
            }
        });
    });

    // Reset validation on modal hide
    $('#godownModal').on('hidden.bs.modal', function() {
        $('#godownForm').removeClass('was-validated');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
    });

    // Edit godown
    $(document).on('click', '.edit-godown', function() {
    const id = $(this).data('id');
    const url = $(this).data('url');

    // Show loading state
    const editBtn = $(this);
    editBtn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

    $.get(url, function(response) {
        // Populate form fields
        $('#godown_id').val(response.data.id);
        $('#godown_name').val(response.data.name);
        $('#status').val(response.data.status).trigger('change');

        // Update UI for edit mode
        $('#godownModalLabel').text('Edit Godown');
        $('#saveGodownBtn').html('<i class="fas fa-sync-alt"></i> Update');
        $('#godownForm').attr('action', response.updateUrl);

        // Show modal
        $('#godownModal').modal('show');

        // Reset button state
        editBtn.html('<i class="fas fa-pen"></i> ').prop('disabled', false);
    }).fail(function(xhr) {
        showErrorToast('Failed to load godown data: ' + (xhr.responseJSON?.message || ''));
        editBtn.html('<i class="fas fa-pen"></i> ').prop('disabled', false);
    });
});

    /***********************Delete Data *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'godownsTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
