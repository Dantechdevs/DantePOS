// area_wise.js
$(function () {

    // Store original title
    const originalTitle = document.title;

    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true,
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
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        columns: [
            { width: "2%" },
            { width: "20%" },
            { width: "20%" },
            { width: "5%" },
            { width: "25%" }
        ]
    });

    // Handle Search Button Click
    $('#searchButton').on('click', function () {
        const url = $(this).data('url');
        const areaId = $('#area_id').val();

        const areaName = $('#area_id').find('option:selected').text();
console.log(areaName);
        if (!areaId) {
            alert('Please select an area.');
            return;
        }

        // Update page title with selected area
        updatePageTitle(areaId, areaName);

        fetchAreaWiseSales(url, areaId);
    });

    function updatePageTitle(areaId, areaName) {
        if (!areaId) {
            // Reset to original title if no area selected
            document.title = originalTitle;
        } else {
            // Update title with selected area name
            document.title = `${areaName} - Area Report`;
        }
    }

    function fetchAreaWiseSales(url, areaId) {
        $('#loader').show();
        table.clear().draw();

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                area_id: areaId
            },
            success: function (response) {
                if (response.areaCustomers && response.areaCustomers.length > 0) {
                    populateResults(response.areaCustomers);
                } else {
                    alert('No records found for the selected area.');
                }
                $('#loader').hide();
            },
            error: function () {
                alert('Error fetching data.');
                $('#loader').hide();
            }
        });
    }

    function populateResults(customers) {
        table.clear();

        customers.forEach((customer, index) => {
            const receivingCell = `
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" class="form-control receiving-amount"
                        data-customer-id="${customer.id}"
                        data-balance="${customer.balance}"
                        min="0" max="${customer.balance}"
                        step="0.01"
                        placeholder="0.00">
                    <button class="btn btn-sm btn-success btn-save-receiving" data-customer-id="${customer.id}">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            `;

            table.row.add([
                index + 1,
                customer.customerName,
                customer.customerMobile,
                formatBalanceDisplay(customer.balance),
                // receivingCell
                ''
            ]).draw(false);
        });

        // Add event listeners for receiving amount inputs
        $('.receiving-amount').on('input', function () {
            const balance = $(this).data('balance');
            const receivingAmount = parseFloat($(this).val()) || 0;

            if (receivingAmount > balance) {
                $(this).val(balance.toFixed(2));
                alert('Receiving amount cannot exceed balance.');
            }
        });

        // Add event listeners for save buttons
        $('.btn-save-receiving').click(function () {
            saveReceivingAmount($(this));
        });
    }

    function formatBalanceDisplay(balance) {
        if (balance === 0) {
            return `<span>${parseFloat(balance).toFixed(2)}</span>`;
        } else if (balance > 0) {
            return `<span style="color:red;">${Math.abs(balance).toFixed(2)} DB</span>`;
        } else {
            return `<span style="color:green;">${parseFloat(balance).toFixed(2)} CR</span>`;
        }
    }

    function saveReceivingAmount(button) {
        const customerId = button.data('customer-id');
        const receivingInput = $(`input[data-customer-id="${customerId}"]`);
        const receivingAmount = parseFloat(receivingInput.val()) || 0;
        const balance = parseFloat(receivingInput.data('balance')) || 0;

        if (receivingAmount <= 0) {
            alert('Please enter a valid receiving amount');
            return;
        }

        if (receivingAmount > balance) {
            alert('Receiving amount cannot exceed balance');
            return;
        }

        // Disable input and button while saving
        receivingInput.prop('disabled', true);
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i>');

        // Send AJAX request to save receiving amount
        $.ajax({
            url: '/receivings/save',
            type: 'POST',
            data: {
                customer_id: customerId,
                amount: receivingAmount,
                date: new Date().toISOString().split('T')[0],
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    button.html('<i class="fas fa-check"></i>');
                    button.removeClass('btn-success').addClass('btn-secondary');

                    // Update the balance display
                    const newBalance = balance - receivingAmount;
                    const balanceCell = $(`input[data-customer-id="${customerId}"]`)
                        .closest('tr')
                        .find('td:eq(3)');

                    balanceCell.html(formatBalanceDisplay(newBalance));

                    alert('Receiving amount saved successfully');
                } else {
                    alert('Error saving receiving amount: ' + response.message);
                    receivingInput.prop('disabled', false);
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-check"></i>');
                }
            },
            error: function () {
                alert('Error saving receiving amount');
                receivingInput.prop('disabled', false);
                button.prop('disabled', false);
                button.html('<i class="fas fa-check"></i>');
            }
        });
    }
});
