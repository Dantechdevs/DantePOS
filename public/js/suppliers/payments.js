import {
    initializeSupplierAutocomplete,
    initializeDataTable,
    handleDeleteAction
} from '../common/utilities.js';
import { dynamicFormData, submitForm } from '../utilities/utilities.js';

function initializeFunctions() {
    initializeSupplierAutocomplete('#supplierName'); // Replace with your customer search input selector
}

$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /************************* Load Data **************************/
    const customersPaymentsListUrl = $('#supplierPaymentsTable').data('url');

    initializeDataTable({
        tableSelector: '#supplierPaymentsTable',
        ajaxUrl: customersPaymentsListUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'purchase_no', name: 'purchase_no', title: 'Voucher#' },
            { data: 'date', name: 'date', title: 'Date' },
            { data: 'supplier', name: 'supplier.name', title: 'Supplier', className: 'text-left' },
            { data: 'amount', name: 'amount', title: 'Amount', className: 'text-right' },
            { data: 'createdBy', name: 'users.name', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });
    /************************* Add Form **********************/
    $(document).on('click', '#addSupplierPayment', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveUrl = $(this).attr('data-saveSupplierPaymentUrl');
        const title = "Add Supplier Payment";
        const container = "#supplierPaymentModalContainer";
        const modalId = "#supplierPaymentModal";

        // dynamicFormData(url, title, saveUrl, container, modalId)
        dynamicFormData(url, title, saveUrl, container, modalId).then(() => {
            initializeFunctions(); // Re-initialize functions
        });
    });

    /*********************** Edit Form ***********************/

    $(document).on('click', '.editSupplierPayment', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = $(this).attr('data-saveSupplierPaymentUrl');
        var title = "Update Supplier Payment";
        const container = "#supplierPaymentModalContainer";
        const modalId = "#supplierPaymentModal";
        // dynamicFormData(url, title, saveUrl, container, modalId)
        dynamicFormData(url, title, saveUrl, container, modalId).then(() => {
            initializeFunctions(); // Re-initialize functions
        });
    })

    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#supplierPaymentForm",
        reloadTableSelector: "#supplierPaymentsTable",
        modalSelector: "#supplierPaymentModal",
        successToastMessage: "Supplier payment added successfully.",
        extraFieldUpdates: function (response) {
            // console.log("print response data")
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
        var title = "Supplier Payment Information";
        const container = "#supplierPaymentModalContainer";
        const modalId = "#viewSupplierPaymentModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /***********************Delete Data **********************/

    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'supplierPaymentsTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
