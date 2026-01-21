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
    const baseTitle = '| Customer Payments Report';

    $('#customer_id').on('change', function () {
        const selectedCustomer = $(this).find('option:selected');

        if ($(this).val() === 'all') {
            // Reset to default title
            document.title = baseTitle;
        } else {
            // Update title with Customer name
            document.title = `${selectedCustomer.text()} ${baseTitle}`;
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
        const customerId = $('#customer_id').val();
        const userId = $('#user_id').val();
        const type = $('#type').val();

        if (!startDate || !endDate) {
            showWarningToast('Please select both start and end dates.');
            return;
        }

        totalAmount = 0;            // Reset the total amount
        table.clear().draw();       // Clear the table
        recordCounter = 1;
        $('#totalAmount').text('0.00'); // Reset the total amount display

        fetchPayments(url, startDate, endDate, customerId, userId, type);            // Fetch initial data
    });

    function fetchPayments(url, startDate, endDate, customerId, userId, type) {

        $('#loader').show();

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                user_id: userId,
                customer_id: customerId,
                type: type, // Include the type filter
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
            '',
            '',
            '<b>Filter Dates</b>',
            `<div style="text-align: center;">${response.startDate} - ${response.endDate}</div>`        // Total Amount
        ]).draw(false);
        data.forEach(function (record) {
            const customerName = record.customers ? record.customers.name : 'N/A';
            const userName = record.users ? record.users.name : 'N/A';
            const description = record.description ? record.description : 'N/A';
            // Determine the badge for the type
            let typeBadge = '';
            if (record.type) {
                console.log(record.type)
                if (record.type.toLowerCase() === 'credit') {
                    typeBadge = `<span class="badge badge-success">Credit</span>`;
                } else if (record.type.toLowerCase() === 'debit') {
                    typeBadge = `<span class="badge badge-danger">Debit</span>`;
                }
            }

            // Add row dynamically to DataTable
            table.row.add([
                recordCounter++,
                moment(record.date).format('DD/MM/YY | hh:mm A'),
                `<a href="javascript:void(0);">${record.invoice_no}</a>`,
                customerName,
                description,
                userName,
                typeBadge,
                `<div style="text-align: right;">${parseFloat(record.amount).toFixed(2)}</div>`,
            ]).draw(false);
        });
        // Calculate total credit and total debit
        const totalCredit = data.reduce((sum, record) => {
            return record.type && record.type.toLowerCase() === 'credit'
                ? sum + parseFloat(record.amount)
                : sum;
        }, 0);

        const totalDebit = data.reduce((sum, record) => {
            return record.type && record.type.toLowerCase() === 'debit'
                ? sum + parseFloat(record.amount)
                : sum;
        }, 0);

        // Calculate the net total amount (credit - debit)
        var totalAmount = totalCredit - totalDebit;

        const closingBalanceDisplay = totalAmount == 0
            ? `${parseFloat(totalAmount).toFixed(2)}`
            : totalAmount < 0
                ? `${Math.abs(totalAmount).toFixed(2)} DB`
                : `${parseFloat(totalAmount).toFixed(2)} CR`;
        // console.log("total amount..: " + totalAmount)
        // Add the Total Amount row at the end
        table.row.add([
            '',
            '',
            '',
            '',
            '',
            '',
            '<b>Total Amount</b>',
            `<div style="text-align: right;"><b>${response.currency}  ${closingBalanceDisplay}</b></div>`        // Total Amount
        ]).draw(false);
    }

    function updateTotalAmount(data) {
        totalAmount = data.reduce((sum, record) => sum + parseFloat(record.amount), 0);
        $('#totalAmount').text(totalAmount.toFixed(2));
    }
});
