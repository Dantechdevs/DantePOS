import {
    initializeDataTable,
    handleDeleteAction
} from '../common/utilities.js';

import { dynamicFormData, submitForm } from '../utilities/utilities.js';

$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const suppliersListUrl = $('#suppliersTable').data('url');

    initializeDataTable({
        tableSelector: '#suppliersTable',
        ajaxUrl: suppliersListUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'Name' },
            { data: 'mobile', name: 'mobile', title: 'Mobile', className: 'text-center' },
            { data: 'balance', name: 'balance', title: 'Balance', className: 'text-right' },
            { data: 'createdBy', name: 'user.name', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });
    /************************* Add Form **********************/
    $(document).on('click', '.addSupplier', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveUrl = $(this).attr('data-saveSupplierUrl');
        const title = "New Supplier";
        const container = "#supplierModalContainer";
        const modalId = "#addSupplierModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /*********************** Edit Form ***********************/

    $(document).on('click', '.editSupplier', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = $(this).attr('data-saveSupplierUrl');
        var title = "Update Supplier";
        const container = "#supplierModalContainer";
        const modalId = "#addSupplierModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    })

    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#addSupplierForm",
        reloadTableSelector: "#suppliersTable",
        modalSelector: "#addSupplierModal",
        successToastMessage: "Supplier added successfully.",
        extraFieldUpdates: function (response) {
            $("#supplierName").val(response.supplierName);
            $("#supplier_id").val(response.supplierID);
            $("#supplierBalance").val(response.supplierBalance);
        },
        onSuccessCallback: function (response) {
            // console.log("Custom success logic executed.", response);
        },
        onErrorCallback: function (error) {
            // console.error("Custom error logic executed.", error);
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

    /************************ View Data *********************/
    $(document).on('click', '.view', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = null;
        var title = "Supplier Information";
        const container = "#supplierModalContainer";
        const modalId = "#viewSupplierPaymentModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /***********************Delete Data **********************/

    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'suppliersTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
