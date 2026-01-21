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
    const baseTitle = '| Customer Ledger Report';
    $('#customer_id').on('change', function () {
        const selectedCustomer = $(this).find('option:selected');

        if ($(this).val() === 'all') {
            // Reset to default title
            document.title = baseTitle;
        } else {
            // Update title with customer name
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

        if (!startDate || !endDate) {
            showWarningToast('Please select both start and end dates.');
            return;
        }

        totalAmount = 0;            // Reset the total amount
        table.clear().draw();       // Clear the table
        recordCounter = 1;
        $('#totalAmount').text('0.00'); // Reset the total amount display

        fetchPayments(url, startDate, endDate, customerId); // Fetch data
    });

    function fetchPayments(url, startDate, endDate, customerId) {
        $('#loader').show();

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                customer_id: customerId,
            },
            success: function (response) {
                if (response.customerReport && response.customerReport.transactions.length > 0) {
                    appendResults(response);
                    // updateTotalAmount(response.customerReport);
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

    function formatBalanceDisplay(balance, currency) {
        if (balance === 0) {
            return `<span>${parseFloat(balance).toFixed(2)}</span>`;
        } else if (balance < 0) {
            return `<span style="color:red;">${Math.abs(balance).toFixed(2)} DB</span>`;
        } else {
            return `<span style="color:green;">${parseFloat(balance).toFixed(2)} CR</span>`;
        }
    }

    function appendResults(response) {
        var report = response.customerReport;

        // Add filter dates row
        table.row.add([
            '',
            '',
            '',
            '<b>Filter Dates</b>',
            '',
            '',
            `<div style="text-align: right;">${response.startDate} - ${response.endDate}</div>`
        ]).draw(false);

        // Add current balance row (if available in response)
        if (response.currentBalance !== undefined) {
            const currentBalanceDisplay = formatBalanceDisplay(response.currentBalance, response.currency);
            table.row.add([
                '',
                '',
                '',
                '<b>Current Balance</b>',
                '-',
                '-',
                `<div style="text-align: right;"><b>${response.currency} ${response.currentBalance > 0 ? response.currentBalance +' DB' : response.currentBalance +' CR'}</b></div>`
            ]).draw(false);
        }

        // Format opening balance with DB/CR notation
        const openingBalanceDisplay = formatBalanceDisplay(report.openingBalance, response.currency);

        // Add the opening balance row
        table.row.add([
            '',
            '',
            '',
            '<b>Opening Balance</b>',
            '-',
            '-',
            `<div style="text-align: right;"><b>${response.currency} ${openingBalanceDisplay}</b></div>`
        ]).draw(false);

        // Add transactions
        report.transactions.forEach(function (record) {
            const description = record.description ? record.description : 'N/A';
            const debit = record.debit !== '-' ? parseFloat(record.debit).toFixed(2) : '-';
            const credit = record.credit !== '-' ? parseFloat(record.credit).toFixed(2) : '-';

            // Parse the balance value from the record (handle both string and number formats)
            let balanceValue = 0;
            let balanceDisplay = '';

            if (typeof record.balance === 'string') {
                // Handle string format like "1591.65 DB" or "500.00 CR"
                const parts = record.balance.split(' ');
                balanceValue = parseFloat(parts[0]);
                balanceDisplay = formatBalanceDisplay(
                    parts[1] === 'DB' ? -balanceValue : balanceValue,
                    ''
                );
            } else {
                // Handle numeric format
                balanceValue = record.balance;
                balanceDisplay = formatBalanceDisplay(balanceValue, '');
            }

            // Add row dynamically to DataTable
            table.row.add([
                recordCounter++,
                record.date,
                record.invoice_no ? `<a href="javascript:void(0);">${record.invoice_no}</a>` : '-',
                description,
                debit !== '-' ? `<div style="text-align: center; color: red;">${debit}</div>` : '-',
                credit !== '-' ? `<div style="text-align: center; color: green;">${credit}</div>` : '-',
                `<div style="text-align: right;">${balanceDisplay}</div>`
            ]).draw(false);
        });

        // Format closing balance with DB/CR notation using totalBalance
        const closingBalanceDisplay = formatBalanceDisplay(report.totalBalance, response.currency);

        // Add the closing balance row at the end
        table.row.add([
            '',
            '',
            '',
            '<b>Closing Balance</b>',
            '-',
            '-',
            `<div style="text-align: right;"><b>${response.currency} ${closingBalanceDisplay}</b></div>`
        ]).draw(false);
    }

    function updateTotalAmount(report) {
        totalAmount = report.totalBalance;
        $('#totalAmount').text(totalAmount.toFixed(2));
    }
});
