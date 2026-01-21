

import {
    initializeCustomerAutocomplete,
    initializeDataTable,
    handleDeleteAction
} from '../common/utilities.js';

import { dynamicFormData, submitForm } from '../utilities/utilities.js';

initializeCustomerAutocomplete('#searchCustomer'); // Replace with your customer search input selector

$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.addCustomer', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveUrl = $(this).attr('data-saveCustomerUrl');
        const title = "New Customer";
        const container = "#customerModalContainer";
        const modalId = "#addCustomerModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });



    const customersListUrl = $('#customersTable').data('url');

    initializeDataTable({
        tableSelector: '#customersTable',
        ajaxUrl: customersListUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'Name' },
            { data: 'mobile', name: 'mobile', title: 'Mobile', className: 'text-center' },
            { data: 'balance', name: 'balance', title: 'Balance', className: 'text-right' },
            { data: 'createdBy', name: 'user.name', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });


    $(document).on('click', '.editCustomer', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = $(this).attr('data-saveCustomerUrl');
        var title = "Update Customer";
        const container = "#customerModalContainer";
        const modalId = "#addCustomerModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    })

    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#addCustomerForm",
        reloadTableSelector: "#customersTable",
        modalSelector: "#addCustomerModal",
        successToastMessage: "Customer added successfully.",
        extraFieldUpdates: function (response) {
            $("#searchCustomer").val(response.customerName);
            $("#customer_id").val(response.customerID);
            $("#area_id").val(response.areaID);
            $("#customerBalance").val(response.customerBalance);
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



    /***********************View Customer  *******************/
    $(document).on('click', '.view', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = null;
        var title = "Customer Information";
        const container = "#customerModalContainer";
        const modalId = "#viewCustomerModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /***********************Delete Data **********************/

    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'customersTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });
});
