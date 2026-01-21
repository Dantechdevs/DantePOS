

$(function () {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Set default dates (last 30 days to today)
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);

    $('#startDate').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#endDate').val(today.toISOString().split('T')[0]);

    // DataTable initialization
    const dataTable = $('#purchaseItemReportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Purchase Items Report',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Purchase Items Report',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                title: 'Purchase Items Report',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
        paging: true,
        pageLength: 25,
        searching: true,
        ordering: true,
        autoWidth: false,
        responsive: true,
        language: {
            emptyTable: "No purchase data available. Generate a report to see data.",
            info: "Showing _START_ to _END_ of _TOTAL_ items",
            infoEmpty: "Showing 0 to 0 of 0 items",
            infoFiltered: "(filtered from _MAX_ total items)",
            lengthMenu: "Show _MENU_ items per page",
            search: "Search items:",
            zeroRecords: "No matching items found"
        },
        columns: [
            {
                data: null,
                className: 'text-center',
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: 'purchase_date',
                render: function(data) {
                    return formatDate(data);
                }
            },
            {
                data: 'purchase_no',
                className: 'font-weight-bold'
            },
            {
                data: 'supplier_name'
            },
            {
                data: 'product_name',
                render: function(data, type, row) {
                    return `<div>
                        <div class="font-weight-bold">${data}</div>
                        <small class="text-muted">Unit: ${row.unit}</small>
                    </div>`;
                }
            },
            {
                data: 'quantity',
                className: 'text-right',
                render: function(data) {
                    return parseFloat(data).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'unit_cost',
                className: 'text-right',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'total_cost',
                className: 'text-right font-weight-bold',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'discount_amount',
                className: 'text-right text-danger',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'net_amount',
                className: 'text-right font-weight-bold text-success',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'status',
                className: 'text-center',
                render: function(data) {
                    let badgeClass = 'badge-secondary';
                    let statusText = 'Unknown';

                    switch(data) {
                        case 'received':
                            badgeClass = 'badge-success';
                            statusText = 'Received';
                            break;
                        case 'pending':
                            badgeClass = 'badge-warning';
                            statusText = 'Pending';
                            break;
                        case 'cancelled':
                            badgeClass = 'badge-danger';
                            statusText = 'Cancelled';
                            break;
                        default:
                            badgeClass = 'badge-info';
                            statusText = data;
                    }

                    return `<span class="badge ${badgeClass} badge-lg">${statusText}</span>`;
                }
            }
        ],
        order: [[1, 'desc']], // Sort by purchase date descending by default
        drawCallback: function(settings) {
            // Add any additional styling after table draw
            $('.badge-lg').css({
                'font-size': '0.85em',
                'padding': '0.4em 0.6em'
            });
        }
    });

    // Show Report Button Click Handler
    $('#show_report').on('click', function() {
        generateReport();
    });

    // Reset Filters Button Click Handler
    $('#reset_filters').on('click', function() {
        resetFilters();
    });

    // Export to Excel Button Click Handler
    $('#export_excel').on('click', function() {
        if (dataTable.rows().count() === 0) {
            showWarningToast('No data to export. Please generate a report first.');
            return;
        }
        $('.buttons-excel').trigger('click');
    });

    // Enter key support for form inputs
    $('#startDate, #endDate, #supplier_id, #product_id, #status').on('keypress', function(e) {
        if (e.which === 13) {
            generateReport();
        }
    });

    // Date validation
    $('#startDate, #endDate').on('change', function() {
        validateDates();
    });

    function validateDates() {
        const startDate = new Date($('#startDate').val());
        const endDate = new Date($('#endDate').val());

        if (startDate && endDate && startDate > endDate) {
            showErrorToast('Start date cannot be greater than end date.');
            $('#startDate').focus();
            return false;
        }
        return true;
    }

    // Generate Report Function
    function generateReport() {
        if (!validateDates()) {
            return;
        }

        const formData = {
            startDate: $('#startDate').val(),
            endDate: $('#endDate').val(),
            supplier_id: $('#supplier_id').val(),
            product_id: $('#product_id').val(),
            status: $('#status').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Validation
        if (!formData.startDate || !formData.endDate) {
            showErrorToast('Please select both start and end dates.');
            return;
        }

        // Show loading spinner
        $('#loadingSpinner').show();
        $('#report_summary').hide();
        $('#show_report').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');

        // AJAX request to fetch report data
        $.ajax({
            url: '/reports/purchase-items/data',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#loadingSpinner').hide();
                $('#show_report').prop('disabled', false).html('<i class="fas fa-search"></i> Generate Report');

                if (response.success) {
                    displayReportData(response.data);
                    updateSummary(response.summary, formData);
                    $('#report_summary').show();
                    showSuccessToast(`Report generated successfully! Found ${response.data.length} items.`);
                } else {
                    showErrorToast(response.message || 'Failed to generate report.');
                    dataTable.clear().draw();
                    $('#report_summary').hide();
                }
            },
            error: function(xhr, status, error) {
                $('#loadingSpinner').hide();
                $('#show_report').prop('disabled', false).html('<i class="fas fa-search"></i> Generate Report');
                showErrorToast('An error occurred while generating the report. Please try again.');
                console.error('Error:', error);
                dataTable.clear().draw();
                $('#report_summary').hide();
            }
        });
    }

    // Display Report Data in DataTable
    function displayReportData(data) {
        dataTable.clear();

        if (data && data.length > 0) {
            dataTable.rows.add(data).draw();

            // Calculate and update footer totals
            const totals = calculateTotals(data);
            updateFooterTotals(totals);
        } else {
            dataTable.draw();
            updateFooterTotals({
                totalQuantity: 0,
                totalCost: 0,
                totalDiscount: 0,
                netAmount: 0
            });
        }
    }

    // Calculate Totals from Data
    function calculateTotals(data) {
        let totalQuantity = 0;
        let totalCost = 0;
        let totalDiscount = 0;
        let netAmount = 0;

        data.forEach(item => {
            totalQuantity += parseFloat(item.quantity) || 0;
            totalCost += parseFloat(item.total_cost) || 0;
            totalDiscount += parseFloat(item.discount_amount) || 0;
            netAmount += parseFloat(item.net_amount) || 0;
        });

        return {
            totalQuantity,
            totalCost,
            totalDiscount,
            netAmount
        };
    }

    // Update Footer Totals
    function updateFooterTotals(totals) {
        $('#footer_total_quantity').text(totals.totalQuantity.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#footer_total_cost').text(formatCurrency(totals.totalCost));
        $('#footer_total_discount').text(formatCurrency(totals.totalDiscount));
        $('#footer_net_amount').text(formatCurrency(totals.netAmount));
    }

    // Update Report Summary
    function updateSummary(summary, filters) {
        $('#total_purchases').text(summary.totalPurchases.toLocaleString());
        $('#total_quantity').text(summary.totalQuantity.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#total_amount').text(formatCurrency(summary.totalAmount));
        $('#average_cost').text(formatCurrency(summary.averageCost));
        $('#total_discount').text(formatCurrency(summary.totalDiscount));
        $('#net_amount').text(formatCurrency(summary.netAmount));

        // Format dates for display
        const startDate = new Date(filters.startDate).toLocaleDateString();
        const endDate = new Date(filters.endDate).toLocaleDateString();
        $('#date_range').text(`${startDate} to ${endDate}`);
        $('#generated_on').text(new Date().toLocaleString());
    }

    // Reset Filters
    function resetFilters() {
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);

        $('#startDate').val(thirtyDaysAgo.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);
        $('#supplier_id').val('all').trigger('change');
        $('#product_id').val('all').trigger('change');
        $('#status').val('all');

        dataTable.clear().draw();
        $('#report_summary').hide();
        updateFooterTotals({
            totalQuantity: 0,
            totalCost: 0,
            totalDiscount: 0,
            netAmount: 0
        });

        showSuccessToast('Filters have been reset.');
    }

    // Initialize datepicker if needed
    $('input[type="date"]').on('focus', function() {
        this.showPicker();
    });
});
