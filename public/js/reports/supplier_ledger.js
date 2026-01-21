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
    const baseTitle = '| Supplier Ledger Report';
    $('#supplier_id').on('change', function () {
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

        if (!startDate || !endDate) {
            showWarningToast('Please select both start and end dates.');
            return;
        }

        totalAmount = 0;            // Reset the total amount
        table.clear().draw();       // Clear the table
        recordCounter = 1;
        $('#totalAmount').text('0.00'); // Reset the total amount display

        fetchPayments(url, startDate, endDate, supplierId); // Fetch data
    });

    function fetchPayments(url, startDate, endDate, supplierId) {
        $('#loader').show();

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                supplier_id: supplierId,
            },
            success: function (response) {
                if (response.supplierReport && response.supplierReport.transactions.length > 0) {
                    appendResults(response);
                    // updateTotalAmount(response.supplierReport);
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
        var report = response.supplierReport;
        // console.log(response)
        table.row.add([
            '',
            '',
            '',
            '',
            '',
            '<b>Filter Dates</b>',
            `<div style="text-align: center;">${response.startDate} - ${response.endDate}</div>`        // Total Amount
        ]).draw(false);

        const supplierName = report.supplier ? report.supplier.name : 'N/A';
        // Format opening balance with DB/CR notation
        const openingBalanceDisplay =
            report.openingBalance === 0
                ? `<span>${parseFloat(report.openingBalance).toFixed(2)}</span>`
                : report.openingBalance < 0
                    ? `<span style="color:red;">${Math.abs(report.openingBalance).toFixed(2)} DB</span>`
                    : `<span style="color:green;">${parseFloat(report.openingBalance).toFixed(2)} CR</span>`;

        // Add the opening balance row first
        table.row.add([
            '',
            '',                           // Date is empty (or you can show a label)
            '',                           // Invoice/Purchase number empty
            '<b>Opening Balance</b>',            // Description
            `<div style="text-align: center; color: red;">-</div>`,                          // Debit
            `<div style="text-align: center; color: red;">-</div>`,                          // Credit
            `<div style="text-align: right;"><b>${response.currency}  ${openingBalanceDisplay}</b></div>`  // Opening balance
        ]).draw(false);
        report.transactions.forEach(function (record) {
            const description = record.description ? record.description : 'N/A';
            const debit = record.debit !== '-' ? parseFloat(record.debit).toFixed(2) : '-';
            const credit = record.credit !== '-' ? parseFloat(record.credit).toFixed(2) : '-';

            // Add row dynamically to DataTable
            table.row.add([
                recordCounter++,
                record.date,
                `<a href="javascript:void(0);">${record.purchase_no}</a>`,
                description,
                `<div style="text-align: center; color: red;">${debit}</div>`,  // Debit centered
                `<div style="text-align: center; color: green;">${credit}</div>`,  // Credit centered
                `<div style="text-align: right;">${record.balance}</div>`
            ]).draw(false);
        });

        // Format closing balance with DB/CR notation using totalBalance


        const closingBalanceDisplay = report.totalBalance == 0
            ? `${parseFloat(report.totalBalance).toFixed(2)}`
            : report.totalBalance < 0
                ? `${Math.abs(report.totalBalance).toFixed(2)} DB`
                : `${parseFloat(report.totalBalance).toFixed(2)} CR`;

        // Add the closing balance row at the end
        table.row.add([
            '',
            '',                          // Date (empty)
            '',                          // Invoice/Purchase number (empty)
            '<b>Closing Balance</b>',           // Description
            `<div style="text-align: center; color: red;">-</div>`,                          // Debit
            `<div style="text-align: center; color: red;">-</div>`,
            `<div style="text-align: right;"><b>${response.currency}  ${closingBalanceDisplay}</b></div>`        // Closing balance
        ]).draw(false);
    }

    function updateTotalAmount(report) {
        totalAmount = report.totalBalance;
        $('#totalAmount').text(totalAmount.toFixed(2));
    }

});
