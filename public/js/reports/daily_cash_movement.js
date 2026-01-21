$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    // Initialize datepicker
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // Load initial data
    loadDailyCashReport($('#date').val());

    // Search button click event
    $('#searchButton').on('click', function() {
        const date = $('#date').val();
        loadDailyCashReport(date);
    });

    // Date change event
    $('#date').on('change', function() {
        const date = $(this).val();
        loadDailyCashReport(date);
    });
});

// Add this after the date change event
$('#downloadPdfButton').on('click', function() {
    const date = $('#date').val();
    const url = $(this).data('url');

    // Create a form to submit the date for PDF download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
    form.appendChild(csrfToken);

    // Add date parameter
    const dateInput = document.createElement('input');
    dateInput.type = 'hidden';
    dateInput.name = 'date';
    dateInput.value = date;
    form.appendChild(dateInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
});


function loadDailyCashReport(date) {
    const url = $('#searchButton').data('url');

    // Show loading state
    $('#resultsBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

    $.ajax({
        url: url,
        method: 'POST',
        data: { date: date },
        success: function(response) {
            if (response.success) {
                updateUI(response);
            } else {
                toastr.error('Failed to load report data');
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred while fetching data');
            console.error(xhr.responseText);
        }
    });
}

function updateUI(data) {
    // Update report date
    $('#reportDate').text(formatDate(data.date));

    // Update balances
    $('#openingBalance').text(formatCurrency(data.opening_balance));
    $('#closingBalance').text(formatCurrency(data.closing_balance));

    const netChange = data.closing_balance - data.opening_balance;
    $('#netChange').text(formatCurrency(netChange));
    $('#netChange').toggleClass('cash-in', netChange >= 0);
    $('#netChange').toggleClass('cash-out', netChange < 0);

    // Update transactions table
    let cashInTotal = 0;
    let cashOutTotal = 0;
    let tableHTML = '';

    if (data.transactions.length === 0) {
        tableHTML = '<tr><td colspan="5" class="text-center">No transactions found for this date</td></tr>';
    } else {
        data.transactions.forEach((transaction, index) => {
            const time = formatTime(transaction.created_at);
            const amount = parseFloat(transaction.amount);

            // Declare variables with let to avoid reference errors
            let amountClass;
            let typeBadge;

            if (transaction.type === 'in') {
                cashInTotal += amount;
                amountClass = 'cash-in';
                typeBadge = '<span class="badge badge-success">Cash In</span>';
            } else {
                cashOutTotal += amount;
                amountClass = 'cash-out';
                typeBadge = '<span class="badge badge-danger">Cash Out</span>';
            }

            tableHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${time}</td>
                    <td>${transaction.description}</td>
                    <td>${typeBadge}</td>
                    <td class="${amountClass}">${formatCurrency(amount)}</td>
                </tr>
            `;
        });
    }

    $('#resultsBody').html(tableHTML);
    $('#totalCashIn').text(formatCurrency(cashInTotal));
    $('#totalCashOut').text(formatCurrency(cashOutTotal));
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB'); // DD/MM/YYYY format
}

function formatTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function formatCurrency(amount) {
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
