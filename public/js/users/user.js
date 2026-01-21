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

    const salesUrl = $('#usersTable').data('url');

    initializeDataTable({
        tableSelector: '#usersTable',
        ajaxUrl: salesUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'name' },
            { data: 'email', name: 'email', title: 'Email' },
            { data: 'mobile', name: 'mobile', title: 'Mobile', className: 'text-center' },
            { data: 'role', name: 'role', title: 'Role', className: 'text-center' },
            { data: 'status', name: 'status', title: 'status', className: 'text-center' },
            { data: 'createdBy', name: 'createdBy', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });


    /***********************Delete Sale *********************/
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'usersTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

});
