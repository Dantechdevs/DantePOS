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

    const productsUrl = $('#unitsTable').data('url');

    initializeDataTable({
        tableSelector: '#unitsTable',
        ajaxUrl: productsUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'Unit' },
            { data: 'createdBy', name: 'createdBy', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });

    // Show add modal
    $(document).on('click', '.add-unit', function () {
        $('#unitForm')[0].reset();
        $('#unit_id').val('');
        var url = $(this).attr('data-url');
        $('#unitForm').attr('action', url);
        $('#saveUnitBtn').html('<i class="fas fa-sync-alt"></i> Save');
        $('#unitModalLabel').text('Add New Unit');
        $('#unitModal').modal('show');
    });

    submitForm({
        formSelector: "#unitForm",
        reloadTableSelector: null,
        modalSelector: null,
        successToastMessage: "unit added successfully.",
        extraFieldUpdates: function (response) {
        },
        onSuccessCallback: function (response) {
            if (response.success) {
                $('#unitModal').modal('hide');

                setTimeout(function () {
                    $('#unitsTable').DataTable().ajax.reload();
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



    // Reset validation on modal hide
    $('#unitModal').on('hidden.bs.modal', function () {
        $('#unitForm').removeClass('was-validated');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
    });

    // Edit unit
    $(document).on('click', '.edit-unit', function () {
        const id = $(this).data('id');
        const url = $(this).data('url');

        // Show loading state
        const editBtn = $(this);
        editBtn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

        $.get(url, function (response) {
            // Populate form fields
            $('#unit_id').val(response.data.id);
            $('#unit_name').val(response.data.name);

            // Update UI for edit mode
            $('#unitModalLabel').text('Edit unit');
            $('#saveunitBtn').html('<i class="fas fa-sync-alt"></i> Update');
            $('#unitForm').attr('action', response.updateUrl);

            // Show modal
            $('#unitModal').modal('show');

            // Reset button state
            editBtn.html('<i class="fas fa-pen"></i> ').prop('disabled', false);
        }).fail(function (xhr) {
            showErrorToast('Failed to load unit data: ' + (xhr.responseJSON?.message || ''));
            editBtn.html('<i class="fas fa-pen"></i> ').prop('disabled', false);
        });
    });

    /***********************Delete Data *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'unitsTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
