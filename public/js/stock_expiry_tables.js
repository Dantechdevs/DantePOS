import {
    initializeDataTable
} from '../js/common/utilities.js';
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const dueSalesUrl = $('#dueSalesTable').data('url');
    const stockAlertUrl = $('#lowStockTable').data('url');
    const expiredProductsTable = $('#expiredProductsTable').data('url');

    initializeDataTable({
        tableSelector: '#dueSalesTable',
        ajaxUrl: dueSalesUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'invoice', name: 'invoice', title: 'Invoice#' },
            { data: 'customer', name: 'customer', title: 'Customer' },
            { data: 'due_amount', name: 'due_amount', title: 'Due Amount', className: 'text-right' },
        ],
    });

    initializeDataTable({
        tableSelector: '#lowStockTable',
        ajaxUrl: stockAlertUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'product', name: 'product', title: 'Product' },
            { data: 'current_stock', name: 'current_stock', title: 'Current Stock' },
            { data: 'reorder_level', name: 'reorder_level', title: 'Reorder Level', className: 'text-left' },
            { data: 'status', name: 'status', title: 'status', className: 'text-center' },
        ],
    });

    initializeDataTable({
        tableSelector: '#expiredProductsTable',
        ajaxUrl: expiredProductsTable,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'product', name: 'product', title: 'Product' },
            { data: 'expiry_date', name: 'expiry_date', title: 'Expiry' },
            { data: 'quantity', name: 'quantity', title: 'Quantity', className: 'text-left' },
        ],
    });

});
