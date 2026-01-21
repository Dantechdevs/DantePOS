$(function () {
    let currentPage = 1;
    let isLoading = false;
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
        paging: false,     // Disable paging for infinite scroll
        searching: false,  // Disable built-in search
        ordering: false,   // Disable ordering
        info: false,       // Hide "Showing X of Y" text
        autoWidth: false,  // Disable auto width for better responsiveness
    });

    // Handle Search Button Click
    $('#searchButton').on('click', function () {
        currentPage = 1;            // Reset the page
        totalAmount = 0;            // Reset the total amount
        table.clear().draw();       // Clear the table
        recordCounter = 1;
        $('#totalAmount').text('0.00'); // Reset the total amount display
        resetScrollListener();      // Reattach scroll listener
        fetchPayments();            // Fetch initial data
    });

    // Function to handle infinite scrolling
    function resetScrollListener() {
        $('#resultsContainer').off('scroll'); // Remove previous scroll listener

        $('#resultsContainer').on('scroll', function () {
            const container = $(this);
            if (
                !isLoading &&
                container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 10
            ) {
                currentPage++;
                fetchPayments();
            }
        });
    }

    // Initialize the scroll listener on page load
    resetScrollListener();

    function fetchPayments() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const userId = $('#user_id').val();

        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }

        isLoading = true;
        $('#loader').show();

        $.ajax({
            url: '/load-payments-by-staff',
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                user_id: userId,
                page: currentPage,
            },
            success: function (response) {
                if (response.data.length > 0) {
                    appendResults(response.data);
                    updateTotalAmount(response.data);

                    // If no more pages, stop scroll listener
                    if (currentPage >= response.pagination.last_page) {
                        $('#resultsContainer').off('scroll'); // Disable further scroll loading
                    }
                } else {
                    $('#resultsContainer').off('scroll'); // Disable further scroll
                }

                isLoading = false;
                $('#loader').hide();
            },
            error: function () {
                alert('Error fetching data.');
                isLoading = false;
                $('#loader').hide();
            },
        });
    }

    function appendResults(data) {
        data.forEach(function (record) {
            const customerName = record.customers ? record.customers.name : 'N/A';
            const userName = record.users ? record.users.name : 'N/A';
            const typeClass = record.type === 'debit' ? 'text-danger' : 'text-success';

            // Add row dynamically to DataTable
            table.row.add([
                recordCounter++,
                record.invoice_no,
                record.date,
                customerName,
                userName,
                `<span class="${typeClass}">${record.type.toUpperCase()}</span>`,
                record.description || '',
                parseFloat(record.amount).toFixed(2),
            ]).draw(false);
        });
    }

    function updateTotalAmount(data) {
        data.forEach(function (record) {
            const amount = parseFloat(record.amount);
            if (record.type === 'debit') {
                totalAmount -= amount; // Subtract debit amounts
            } else if (record.type === 'credit') {
                totalAmount += amount; // Add credit amounts
            }
        });
        $('#totalAmount').text(totalAmount.toFixed(2));
    }


    // function updateTotalAmount(data) {
    //     data.forEach(function (record) {
    //         totalAmount += parseFloat(record.amount);
    //     });
    //     $('#totalAmount').text(totalAmount.toFixed(2));
    // }
});
