import {
    initializeCustomerAutocomplete,
    initializeDataTable,
    handleDeleteAction
} from '../common/utilities.js';
import { dynamicFormData, submitForm } from '../utilities/utilities.js';

function initializeFunctions() {
    initializeCustomerAutocomplete('#searchCustomer'); // Replace with your customer search input selector
}



$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /************************* Load Data **************************/
    const customersPaymentsListUrl = $('#customerPaymentsTable').data('url');

    initializeDataTable({
        tableSelector: '#customerPaymentsTable',
        ajaxUrl: customersPaymentsListUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'invoice_no', name: 'invoice_no', title: 'Voucher#' },
            { data: 'date', name: 'date', title: 'Date' },
            { data: 'customer', name: 'customers.name', title: 'Customer', className: 'text-left' },
            { data: 'payment_type', name: 'type', title: 'Payment Type', className: 'text-center', searchable: true },
            { data: 'amount', name: 'amount', title: 'Amount', className: 'text-right' },
            { data: 'createdBy', name: 'users.name', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });
    /************************* Add Form **********************/
    $(document).on('click', '#addCustomerPayment', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveUrl = $(this).attr('data-saveCustomerPaymentUrl');
        const title = "Add Customer Payment";
        const container = "#customerPaymentModalContainer";
        const modalId = "#customerPaymentModal";

        // dynamicFormData(url, title, saveUrl, container, modalId)
        dynamicFormData(url, title, saveUrl, container, modalId).then(() => {
            initializeFunctions(); // Re-initialize functions
        });
    });

    /*********************** Edit Form ***********************/

    $(document).on('click', '.editCustomerPayment', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = $(this).attr('data-saveCustomerPaymentUrl');
        var title = "Update Customer Payment";
        const container = "#customerPaymentModalContainer";
        const modalId = "#customerPaymentModal";
        // dynamicFormData(url, title, saveUrl, container, modalId)
        dynamicFormData(url, title, saveUrl, container, modalId).then(() => {
            initializeFunctions(); // Re-initialize functions
        });
    })

    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#customerPaymentForm",
        reloadTableSelector: "#customerPaymentsTable",
        modalSelector: "#customerPaymentModal",
        successToastMessage: "Customer payment added successfully.",
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
        var title = "Customer Payment Information";
        const container = "#customerPaymentModalContainer";
        const modalId = "#viewCustomerPaymentModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /***********************Delete Data **********************/

    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'customerPaymentsTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
