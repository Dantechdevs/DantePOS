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
        totalAmount = 0;            // Reset the total amount
        table.clear().draw();       // Clear the table
        recordCounter = 1;
        $('#totalAmount').text('0.00'); // Reset the total amount display
        fetchPayments();            // Fetch initial data
    });

    function fetchPayments() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const userId = $('#user_id').val();

        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }

        $('#loader').show();

        $.ajax({
            url: '/report-get-staff-sales',
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                user_id: userId,
            },
            success: function (response) {
                if (response.data && response.data.length > 0) {
                    appendResults(response.data);
                    updateTotalAmount(response.data);
                } else {
                    alert('No records found for the selected filters.');
                }

                $('#loader').hide();
            },
            error: function () {
                alert('Error fetching data.');
                $('#loader').hide();
            },
        });
    }

    function appendResults(data) {
        data.forEach(function (record) {
            const customerName = record.customers ? record.customers.name : 'N/A';
            const userName = record.users ? record.users.name : 'N/A';

            // Add row dynamically to DataTable
            table.row.add([
                recordCounter++,
                moment(record.date).format('DD/MM/YY | hh:mm A'),
                `<a href="/sale-invoice/${record.id}" target="_blank">${record.invoice_no}</a>`,
                customerName,
                userName,
                parseFloat(record.grand_total).toFixed(2),
            ]).draw(false);
        });
    }

    function updateTotalAmount(data) {
        totalAmount = data.reduce((sum, record) => sum + parseFloat(record.grand_total), 0);
        $('#totalAmount').text(totalAmount.toFixed(2));
    }
});
