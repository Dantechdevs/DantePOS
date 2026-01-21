$(function () {
    // Initialize Select2 and Datepicker
    $('.select2').select2();
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
    });

    // Handle Report Type Change
    $('#sale_type').on('change', function () {
        const saleType = $(this).val();
        resetValidation(); // Reset validation on change

        $('#customerField, #areaField').hide(); // Hide all dynamic fields initially

        if (saleType === 'customerWise') {
            $('#customerField').show();
            $('.dateRange').show();
        } else if (saleType === 'areaWise') {
            $('#areaField').show();
            $('.dateRange').hide();
        }
    });

    // Handle Search Button Click
    // $('#searchButton').on('click', function () {
    //     if (!validateForm()) {
    //         return; // Stop if form validation fails
    //     }

    //     const formData = $('#creditDebitForm').serialize(); // Serialize form data

    //     $.ajax({
    //         url: '/report-credit-debit/search', // Your backend endpoint
    //         type: 'GET',
    //         data: formData,
    //         beforeSend: function () {
    //             $('#resultsContainer').hide(); // Hide the results container
    //             $('#loader').fadeIn(); // Show the loader
    //         },
    //         success: function (response) {
    //             console.log(response);
    //             return false;
    //             $('#resultsContainer').show(); // Show results container
    //             $('#reportBody').html(response.html); // Inject the response HTML into the table
    //             $('#loader').fadeOut(); // Hide the loader
    //         },
    //         error: function () {
    //             $('#loader').fadeOut(); // Hide the loader
    //             alert('An error occurred while fetching the report.'); // Handle errors
    //         },
    //     });
    // });

    $('#searchButton').on('click', function () {
        const formData = $('#creditDebitForm').serialize();

        $.ajax({
            url: '/report-credit-debit/search',
            type: 'GET',
            data: formData,
            beforeSend: function () {
                $('#resultsContainer').hide();
                $('#loader').fadeIn();
            },
            success: function (response) {
                $('#loader').fadeOut();
                $('#resultsContainer').show();

                if ($.fn.DataTable.isDataTable('#resultsTable')) {
                    $('#resultsTable').DataTable().destroy(); // Destroy existing DataTable instance
                }

                // Clear Table Content
                $('#resultsTable thead').empty();
                $('#resultsTable tbody').empty();

                // Build Headers
                const headers = response.headers;
                let headerHtml = '<tr>';
                headers.forEach(header => {
                    headerHtml += `<th>${header}</th>`;
                });
                headerHtml += '</tr>';
                $('#resultsTable thead').html(headerHtml);

                // Build Rows
                let rowsHtml = '';
                if (response.customer_type === 'all') {
                    response.reportData.forEach(report => {
                        // Add Summary Row (Not part of DataTable)
                        rowsHtml += `
                            <tr class="bg-light font-weight-bold">
                                <td colspan="${headers.length}" class="text-center">Customer: ${report.customer.name} (${report.customer.mobile})</td>
                            </tr>
                            <tr>
                                <td colspan="${headers.length - 3}" class="text-right font-weight-bold">Opening Balance:</td>
                                <td colspan="3" class="text-right font-weight-bold">
                                    ${Math.abs(report.openingBalance).toFixed(2)} ${report.openingBalance < 0 ? 'DB' : 'CR'}
                                </td>
                            </tr>`;

                        // Add Transactions (Part of DataTable)
                        report.transactions.forEach((transaction, index) => {
                            rowsHtml += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${transaction.invoice_no}</td>
                                    <td>${transaction.date}</td>
                                    <td>${transaction.description}</td>
                                    <td>${transaction.debit || '-'}</td>
                                    <td>${transaction.credit || '-'}</td>
                                    <td>${transaction.balance}</td>
                                </tr>`;
                        });

                        // Add Total Balance Row (Not part of DataTable)
                        rowsHtml += `
                            <tr>
                                <td colspan="${headers.length - 3}" class="text-right font-weight-bold">Total Balance:</td>
                                <td colspan="3" class="text-right font-weight-bold">
                                    ${Math.abs(report.totalBalance).toFixed(2)} ${report.totalBalance < 0 ? 'DB' : 'CR'}
                                </td>
                            </tr>`;
                    });
                } else if (response.customer_type === 'single') {
                    // Add Summary Row (Not part of DataTable)
                    rowsHtml += `
                        <tr class="bg-light font-weight-bold">
                            <td colspan="${headers.length}" class="text-center">Customer: ${response.customer.name} (${response.customer.mobile})</td>
                        </tr>
                        <tr>
                            <td colspan="${headers.length - 3}" class="text-right font-weight-bold">Opening Balance:</td>
                            <td colspan="3" class="text-right font-weight-bold">
                                ${Math.abs(response.openingBalance).toFixed(2)} ${response.openingBalance < 0 ? 'DB' : 'CR'}
                            </td>
                        </tr>`;

                    // Add Transactions (Part of DataTable)
                    response.transactions.forEach((transaction, index) => {
                        rowsHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${transaction.invoice_no}</td>
                                <td>${transaction.date}</td>
                                <td>${transaction.description}</td>
                                <td>${transaction.debit || '-'}</td>
                                <td>${transaction.credit || '-'}</td>
                                <td>${transaction.balance}</td>
                            </tr>`;
                    });

                    // Add Total Balance Row (Not part of DataTable)
                    rowsHtml += `
                        <tr>
                            <td colspan="${headers.length - 3}" class="text-right font-weight-bold">Total Balance:</td>
                            <td colspan="3" class="text-right font-weight-bold">
                                ${Math.abs(response.totalBalance).toFixed(2)} ${response.totalBalance < 0 ? 'DB' : 'CR'}
                            </td>
                        </tr>`;
                }
                console.log(rowsHtml)

                $('#resultsTable tbody').html(rowsHtml);

                // Reinitialize DataTable Only on Transaction Rows
                // $('#resultsTable').DataTable({
                //     dom: 'Bfrtip',
                //     buttons: [
                //         { extend: 'copyHtml5', className: 'btn btn-primary btn-sm', text: 'Copy' },
                //         { extend: 'csvHtml5', className: 'btn btn-success btn-sm', text: 'CSV' },
                //         { extend: 'excelHtml5', className: 'btn btn-info btn-sm', text: 'Excel' },
                //         { extend: 'pdfHtml5', className: 'btn btn-danger btn-sm', text: 'PDF' },
                //         { extend: 'print', className: 'btn btn-warning btn-sm', text: 'Print' },
                //     ],
                //     paging: false,
                //     searching: true,
                //     ordering: true,
                // });
            },
            error: function (xhr) {
                $('#loader').fadeOut();
                console.error('AJAX Error:', xhr.responseText);
                alert('An error occurred while fetching the report.');
            },
        });
    });



    // Initialize DataTable
    // function initializeDataTable() {
    //     $('#resultsTable').DataTable({
    //         dom: 'Bfrtip', // Include buttons in the DOM
    //         buttons: [
    //             {
    //                 extend: 'copyHtml5',
    //                 className: 'btn btn-primary btn-sm',
    //                 text: '<i class="fas fa-copy"></i> Copy',
    //             },
    //             {
    //                 extend: 'csvHtml5',
    //                 className: 'btn btn-success btn-sm',
    //                 text: '<i class="fas fa-file-csv"></i> CSV',
    //             },
    //             {
    //                 extend: 'excelHtml5',
    //                 className: 'btn btn-info btn-sm',
    //                 text: '<i class="fas fa-file-excel"></i> Excel',
    //             },
    //             {
    //                 extend: 'pdfHtml5',
    //                 className: 'btn btn-danger btn-sm',
    //                 text: '<i class="fas fa-file-pdf"></i> PDF',
    //             },
    //             {
    //                 extend: 'print',
    //                 className: 'btn btn-warning btn-sm',
    //                 text: '<i class="fas fa-print"></i> Print',
    //             },
    //         ],
    //         paging: false,
    //         searching: true,
    //         ordering: true,
    //     });
    // }





    // Function to Validate Form Fields
    function validateForm() {
        let isValid = true;
        const saleType = $('#sale_type').val();

        if (!saleType) {
            notifyError('Please select a Report Type');
            isValid = false;
        }

        if (saleType === 'customerWise') {
            isValid &= validateField('#customer_id', 'Customer is required');
            isValid &= validateField('#startDate', 'Start Date is required');
            isValid &= validateField('#endDate', 'End Date is required');
        } else if (saleType === 'areaWise') {
            isValid &= validateField('#area_id', 'Area is required');
        }

        return isValid;
    }

    // Function to Validate a Specific Field
    function validateField(selector, message) {
        const value = $(selector).val();
        if (!value) {
            notifyError(message);
            return false;
        }
        return true;
    }

    // Function to Display Error Notifications
    function notifyError(message) {
        $.notify(message, { globalPosition: 'top right', className: 'error' });
    }

    // Reset Validation States
    function resetValidation() {
        $('.error').remove(); // Clear previous errors (if any)
    }

    /**************** Download PDF  ****************/
    $(document).on("submit", "#creditDebitForm", function (e) {
        e.preventDefault();

        // Get the submit button and add a spinner
        const submitButton = $(this).find("button[type='submit']");
        const spinnerText = " Generating...";
        const originalText = addSpinner(submitButton, spinnerText);

        // Serialize form data
        const formData = serializeFormToObject(this);
        const url = $("#creditDebitForm").attr("action");

        // Send AJAX request with blob response type
        ajaxRequest(
            url,
            "POST",
            formData,
            function (response, status, xhr) {
                // Extract values from response headers
                const file_name = xhr.getResponseHeader("file_name");
                const caseId = $("#caseId").val(); // Assuming caseId is stored in an input field

                // Create a Blob for the PDF
                const blob = new Blob([response], { type: "application/pdf" });
                const link = document.createElement("a");

                // Update the file name with dynamic values
                const fileName = `${file_name}.pdf`;
                link.href = window.URL.createObjectURL(blob);
                link.download = fileName;

                // Trigger the download
                link.click();

                // Cleanup
                window.URL.revokeObjectURL(link.href);
                showSuccessToast("PDF downloaded successfully!");
            },
            function (error) {
                if (error.responseJSON && error.responseJSON.error) {
                    const errors = error.responseJSON.error.split("<br>");
                    errors.forEach((err) => showErrorToast(err.trim()));
                } else if (error.status === 422) {
                    const validationErrors = error.responseJSON.errors;
                    $.each(validationErrors, (field, messages) => {
                        messages.forEach((message) => showWarningToast(message));
                    });
                } else {
                    showErrorToast("Failed to download the PDF. Please try again.");
                }
            },
            {
                xhrFields: {
                    responseType: "blob", // Expecting binary data
                },
            }
        ).always(() => {
            removeSpinner(submitButton, originalText); // Remove spinner in all cases
        });
    });


});
