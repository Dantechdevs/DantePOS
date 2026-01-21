import { validateDates } from '../utilities/utilities.js';
$(function () {
    let totalAmount = 0;
    let recordCounter = 1; // Initialize the counter

    // Initialize Select2 and DatePicker
    $('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true,
    });

    $('#startDate, #endDate').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
    });
    // Store original title format
    const baseTitle = '| Purchase Report';

    $('#supplier_id').on('change', function() {
        const selectedSupplier = $(this).find('option:selected');

        if ($(this).val() === 'all') {
            // Reset to default title
            document.title = baseTitle;
        } else {
            // Update title with supplier name
            document.title = `${selectedSupplier.text()} ${baseTitle}`;
        }
    });
    validateDates("#startDate", "#endDate");

    // Initialize DataTable with buttons
    const table = $('#resultsTable').DataTable({
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'copyHtml5',
                className: 'btn btn-outline-primary btn-sm',
                text: '<i class="fas fa-copy"></i> Copy'
            },
            {
                extend: 'csvHtml5',
                className: 'btn btn-outline-success btn-sm',
                text: '<i class="fas fa-file-csv"></i> CSV'
            },
            {
                extend: 'excelHtml5',
                className: 'btn btn-outline-info btn-sm',
                text: '<i class="fas fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdfHtml5',
                className: 'btn btn-outline-danger btn-sm',
                text: '<i class="fas fa-file-pdf"></i> PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-outline-secondary btn-sm',
                text: '<i class="fas fa-print"></i> Print'
            }
        ],
        paging: false,       // Enable paging
        searching: false,   // Disable built-in search
        ordering: false,    // Disable ordering
        info: false,         // Show "Showing X of Y" text
        autoWidth: false,   // Disable auto width for better responsiveness
    });

    // Handle Search Button Click
    $('#searchButton').on('click', function () {
        const url = $(this).data('url');
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const supplierId = $('#supplier_id').val();
        const userId = $('#user_id').val();
        const status = $('#status').val();

        if (!startDate || !endDate) {
            showWarningToast('Please select both start and end dates.');
            return;
        }

        totalAmount = 0;            // Reset the total amount
        table.clear().draw();       // Clear the table
        recordCounter = 1;
        $('#totalAmount').text('0.00'); // Reset the total amount display

        fetchPayments(url, startDate, endDate, supplierId, userId, status);            // Fetch initial data
    });

    function fetchPayments(url, startDate, endDate, supplierId, userId, status) {




        $('#loader').show();

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                user_id: userId,
                supplier_id: supplierId,
                status: status, // Include the status filter
            },
            success: function (response) {
                if (response.data && response.data.length > 0) {
                    appendResults(response);
                    // updateTotalAmount(response.data);
                } else {
                    showWarningToast('No records found for the selected filters.');
                }

                $('#loader').hide();
            },
            error: function () {
                showWarningToast('Error fetching data.');
                $('#loader').hide();
            },
        });
    }

    function appendResults(response) {
        var data = response.data;
        table.row.add([
            '',
            '',
            '',
            '',
            '<b>Filter Dates</b>',
            `<div style="text-align: center;">${response.startDate} - ${response.endDate}</div>`        // Total Amount
        ]).draw(false);
        data.forEach(function (record) {
            const supplierName = record.supplier ? record.supplier.name : 'N/A';
            const userName = record.users ? record.users.name : 'N/A';

            // Add row dynamically to DataTable
            table.row.add([
                recordCounter++,
                moment(record.date).format('DD/MM/YY | hh:mm A'),
                `<a href="/purchase-invoice/${record.id}" target="_blank">${record.purchase_no}</a>`,
                supplierName,
                userName,
                `<div style="text-align: right;">${parseFloat(record.grand_total).toFixed(2)}</div>`,
            ]).draw(false);
        });
        var totalAmount = data.reduce((sum, record) => sum + parseFloat(record.grand_total), 0);
        // console.log("total amount..: " + totalAmount)
        // Add the Total Amount row at the end
        table.row.add([
           '',
            '',
            '',
            '',
            '<b>Total Amount</b>',
            `<div style="text-align: right;"><b>${response.currency}  ${totalAmount.toFixed(2)}</b></div>`        // Total Amount
        ]).draw(false);
    }

    function updateTotalAmount(data) {
        totalAmount = data.reduce((sum, record) => sum + parseFloat(record.grand_total), 0);
        $('#totalAmount').text(totalAmount.toFixed(2));
    }
});
