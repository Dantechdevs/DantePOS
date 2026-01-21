import {
    handleDeleteAction,
    initializeDataTable
 } from '../common/utilities.js';
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const salesUrl = $('#AllPurchaseTable').data('url');

    initializeDataTable({
            tableSelector: '#AllPurchaseTable',
            ajaxUrl: salesUrl,
            columns: [
                { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'date', name: 'date', title: 'Date' },
                { data: 'purchase_no', name: 'purchase_no', title: 'Invoice#' },
                { data: 'supplier', name: 'supplier.name', title: 'Customer', className: 'text-left' },
                { data: 'grand_total', name: 'grand_total', title: 'Amount', className: 'text-right' },
                { data: 'status', name: 'status', title: 'status', className: 'text-center' },
                { data: 'createdBy', name: 'users.name', title: 'Created By' },
                { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
            ],
        });


    /***********************Delete Sale *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'AllPurchaseTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
