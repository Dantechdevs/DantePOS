import {
    initializeDataTable
} from '../common/utilities.js';
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const productsUrl = $('#employeesMonthlySalaryTable').data('url');

    initializeDataTable({
        tableSelector: '#employeesMonthlySalaryTable',
        ajaxUrl: productsUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'employee_name', name: 'employee_name', title: 'Employee Name' },
            { data: 'date', name: 'date', title: 'Date' },
            { data: 'amount', name: 'amount', title: 'Salary' },
            { data: 'status', name: 'status', title: 'Status', className: 'text-center' },
        ],
    });

});
