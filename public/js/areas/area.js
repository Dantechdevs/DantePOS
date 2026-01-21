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

    const areaUrl = $('#areasTable').data('url');

    initializeDataTable({
        tableSelector: '#areasTable',
        ajaxUrl: areaUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'Area' },
            { data: 'createdBy', name: 'createdBy', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });

    // Show add modal
    $(document).on('click', '.add-area', function() {
        $('#areaForm')[0].reset();
        $('#area_id').val('');
        var url = $(this).attr('data-url');
        $('#areaForm').attr('action', url);
        $('#saveAreaBtn').html('<i class="fas fa-sync-alt"></i> Save');
        $('#areaModalLabel').text('Add New Area');
        $('#areaModal').modal('show');
    });

    submitForm({
        formSelector: "#areaForm",
        reloadTableSelector: null,
        modalSelector: null,
        successToastMessage: "Area added successfully.",
        extraFieldUpdates: function (response) {
        },
        onSuccessCallback: function (response) {
            if (response.success) {
                $('#areaModal').modal('hide');

                setTimeout(function () {
                    $('#areasTable').DataTable().ajax.reload();
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
    $('#areaModal').on('hidden.bs.modal', function() {
        $('#areaForm').removeClass('was-validated');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
    });

    // Edit area
    $(document).on('click', '.edit-area', function() {
    const id = $(this).data('id');
    const url = $(this).data('url');

    // Show loading state
    const editBtn = $(this);
    editBtn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

    $.get(url, function(response) {
        // Populate form fields
        $('#area_id').val(response.data.id);
        $('#area_name').val(response.data.name);
        $('#status').val(response.data.status).trigger('change');

        // Update UI for edit mode
        $('#areaModalLabel').text('Edit Area');
        $('#saveAreaBtn').html('<i class="fas fa-sync-alt"></i> Update');
        $('#areaForm').attr('action', response.updateUrl);

        // Show modal
        $('#areaModal').modal('show');

        // Reset button state
        editBtn.html('<i class="fas fa-pen"></i> ').prop('disabled', false);
    }).fail(function(xhr) {
        showErrorToast('Failed to load area data: ' + (xhr.responseJSON?.message || ''));
        editBtn.html('<i class="fas fa-pen"></i> ').prop('disabled', false);
    });
});

    /***********************Delete Data *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'areasTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
