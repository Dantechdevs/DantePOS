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

    const productsUrl = $('#productsTable').data('url');

    initializeDataTable({
        tableSelector: '#productsTable',
        ajaxUrl: productsUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'product_code', name: 'product_code', title: 'Product Code' },
            { data: 'name', name: 'name', title: 'Name' },
            { data: 'stock', name: 'stock', title: 'Stock' },
            // { data: 'cost', name: 'cost', title: 'Cost' },
            // { data: 'selling_price', name: 'selling_price', title: 'Selling Price', className: 'text-center', render: data => `${parseFloat(data).toFixed(0)}` },
            { data: 'supplier_data', name: 'supplier_data', title: 'Supplier', className: 'text-left' },
            { data: 'createdBy', name: 'createdBy', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });

    // $(document).on('click', '.add-product', function (e) {
    //     e.preventDefault();
    //     var modal = $('#addProductModal');
    //     var form = $('#productForm');
    //     var url = $(this).data('url'); // Get URL from the data attribute
    //     console.log("Add Product URL:", url);
    //     $('#supplier_id').val('').trigger('change');
    //     form[0].reset();
    //     modal.find('.modal-title').text('Add Product');
    //     modal.attr('action', url).attr('method', 'POST');
    //     $(modal).modal('show');

    // })

    $(document).on('click', '.add-product', function (e) {
    e.preventDefault();
    var modal = $('#addProductModal');
    var form = $('#productForm');
    var url = $(this).data('url'); // Get URL from the data attribute
    // console.log("Add Product URL:", url);
// Update modal title and form attributes
    modal.find('.modal-title').text('Add Product');
    form.attr('action', url).attr('method', 'POST');
    // Reset form fields
    $('#supplier_id').val('').trigger('change');
    form[0].reset();



    // Remove any existing PUT method hidden input
    $('input[name="_method"]').remove();

    // Show the modal
    modal.modal('show');
});



    /***********************Delete Data *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'productsTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });


    initSelect2();
    generateProductCode();
    // Initialize Select2
    function initSelect2() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Select Unit",
            allowClear: true
        });
    }

    // Generate Product Code
    function generateProductCode() {
        $('#generateCode').on('click', function () {
            const randomCode = Math.floor(1000 + Math.random() * 9000);
            $('#product_code').val(randomCode).trigger('change');
        });
    }

    $(document).on('click', '.editProductxxx', function (e) {
        e.preventDefault();
        var modal = $('#addProductModal');
        modal.find('.modal-title').text('Edit Product');
        const url = $(this).data('url'); // Get URL from the data attribute
        const productId = $(this).data('id'); // Get product ID from the data attribute
        const updateUrl = $(this).data('update-url'); // Add this attribute to your edit button

        // Change the form action and method for update
        $('#productForm').attr('action', updateUrl).attr('method', 'POST');
        // Add hidden input for PUT method (if your backend expects PUT)
        // $('#productForm').append('<input type="hidden" name="_method" value="PUT">');

        $.ajax({
            url: url,
            type: 'GET',
            data: { id: productId },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    // Populate the form fields with the response data
                    $('#product_id').val(response.product.id);
                    $('#product_code').val(response.product.product_code);
                    $('#name').val(response.product.name);
                    $('#qtyPerUnit').val(response.product.qtyPerUnit);
                    $('#quantity').val(response.product.quantity);
                    $('#cost').val(response.product.cost);
                    $('#item_cost').val(response.product.item_cost);
                    $('#selling_price').val(response.product.selling_price);
                    $('#item_selling_price').val(response.product.item_selling_price);
                    $('#status').val(response.product.status).trigger('change');
                    $('#unit_id').val(response.product.unit_id).trigger('change');
                    $(modal).modal('show');
                } else {
                    showErrorToast("Failed to load product details.");
                }
            },
            error: function (error) {
                showErrorToast("Error fetching product details.");
            }
        });
    })






    submitForm({
        formSelector: "#productForm",
        reloadTableSelector: null,
        modalSelector: null,
        successToastMessage: "Product added successfully.",
        extraFieldUpdates: function (response) {
        },
        onSuccessCallback: function (response) {
            if (response.success) {
                $('#addProductModal').modal('hide');
                // const message = response.sale_type === 'new' ? 'Product added successfully!' : 'Product updated successfully!'
                // showSuccessToast(message);
                setTimeout(function () {
                    $('#productsTable').DataTable().ajax.reload();
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

});
