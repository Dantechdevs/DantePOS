@extends('layouts.layout')
@section('title', '| Employees Monthly Salaries')
@section('content')

    <div class="content-wrapper" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
        <!-- Content Header -->
        <section class="content-header pt-4">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="header-card p-4 rounded-lg shadow-lg" style="background: rgba(255, 255, 255, 0.95);">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper mr-3 p-3 rounded-circle" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                                </div>
                                <div>
                                    <h1 class="m-0 text-dark font-weight-bold">Employee Salary Processing</h1>
                                    <p class="text-muted mb-0">Manage and process monthly salary payments efficiently</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content pb-4">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header py-3" style="background: linear-gradient(to right, #667eea, #764ba2);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title m-0 text-white">
                                        <i class="fas fa-search mr-2"></i>Select Month & View Employees
                                    </h3>
                                    <span class="badge badge-light">Version 2.0</span>
                                </div>
                            </div>

                            <form action="{{ url('/pay-employee-mothly-salary') }}" method="post" id="salaryForm">
                                @csrf
                                <input type="hidden" name="date" id="formDate" value="{{ date('Y-m') }}">

                                <div class="card-body">
                                    <!-- Search Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="font-weight-bold text-dark mb-2">
                                                    <i class="fas fa-calendar-check mr-2"></i>Select Month for Salary Processing
                                                </label>
                                                <div class="input-group input-group-lg shadow-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white border-right-0">
                                                            <i class="far fa-calendar-alt text-primary"></i>
                                                        </span>
                                                    </div>
                                                    <input type="month" name="search_date" id="date"
                                                           class="form-control border-left-0 py-3"
                                                           value="{{ date('Y-m') }}"
                                                           style="border-radius: 0 8px 8px 0;">
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="fas fa-info-circle mr-1"></i> Select the month for which you want to process salaries
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary btn-lg w-100 shadow-lg" id="search">
                                                <i class="fas fa-search mr-2"></i> Load Employees
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Stats Cards -->
                                    <div class="row mb-4" id="statsContainer" style="display: none;">
                                        <div class="col-md-3">
                                            <div class="stat-card bg-gradient-info text-white p-3 rounded-lg shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <p class="mb-1">Total Employees</p>
                                                        <h3 class="mb-0" id="totalEmployees">0</h3>
                                                    </div>
                                                    <div class="icon-wrapper">
                                                        <i class="fas fa-users fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-card bg-gradient-success text-white p-3 rounded-lg shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <p class="mb-1">Total Salary</p>
                                                        <h3 class="mb-0" id="totalSalaryAmount">Rs 0</h3>
                                                    </div>
                                                    <div class="icon-wrapper">
                                                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-card bg-gradient-warning text-white p-3 rounded-lg shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <p class="mb-1">Total Advance</p>
                                                        <h3 class="mb-0" id="totalAdvanceAmount">Rs 0</h3>
                                                    </div>
                                                    <div class="icon-wrapper">
                                                        <i class="fas fa-hand-holding-usd fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-card bg-gradient-danger text-white p-3 rounded-lg shadow-sm">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <p class="mb-1">Net Payable</p>
                                                        <h3 class="mb-0" id="netPayable">Rs 0</h3>
                                                    </div>
                                                    <div class="icon-wrapper">
                                                        <i class="fas fa-calculator fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Loading -->
                                    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
                                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <h4 class="mt-4 text-primary">Fetching Employee Data</h4>
                                        <p class="text-muted">Please wait while we load salary information</p>
                                    </div>

                                    <!-- Results Table with Hidden Inputs Container -->
                                    <div id="hiddenInputsContainer" style="display: none;"></div>

                                    <!-- Results Table -->
                                    <div id="DocumentResults" class="mt-4"></div>

                                    <!-- Empty State -->
                                    <div id="emptyState" class="text-center py-5" style="display: none;">
                                        <div class="empty-state-icon mb-4">
                                            <i class="fas fa-user-slash fa-4x text-muted mb-4"></i>
                                        </div>
                                        <h3 class="text-muted mb-3">No Employees Found</h3>
                                        <p class="text-muted mb-4">No salary records found for the selected month</p>
                                    </div>

                                    <!-- Handlebars Template -->
                                    <script id="document-template" type="text/x-handlebars-template">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        @{{{thsource}}}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @{{#each employees}}
                                                    <tr class="employee-row" data-employee-id="@{{employeeId}}" data-amount="@{{amount}}" data-index="@{{index}}">
                                                        <td>@{{sl}}</td>
                                                        <td class="font-weight-bold">@{{employeeName}}</td>
                                                        <td class="text-center advance-cell">@{{advance}}</td>
                                                        <td class="text-center salary-cell">@{{salary}}</td>
                                                        <td class="text-center">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input employee-checkbox"
                                                                       id="employee_@{{index}}" data-index="@{{index}}">
                                                                <label class="custom-control-label" for="employee_@{{index}}"></label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @{{/each}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </script>
                                </div>

                                <!-- Action Buttons -->
                                <div class="card-footer bg-white border-top py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ '/dashboard' }}" class="btn btn-outline-secondary btn-lg px-4">
                                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-info btn-lg px-4 mr-2" id="printBtn">
                                                <i class="fas fa-print mr-2"></i> Print Report
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg px-5" id="paySalaryBtn" style="display: none;">
                                                <i class="fas fa-paper-plane mr-2"></i> Process Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle mr-2"></i>Confirm Payment Processing
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-money-check-alt fa-3x text-success mb-3"></i>
                        <h4>Confirm Salary Payment?</h4>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        You are about to process salaries for <span id="selectedCount" class="font-weight-bold">0</span> employees.
                        Total amount: <span id="totalPayment" class="font-weight-bold">Rs 0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmPayment">Confirm & Process</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-attachment: fixed;
        }

        .card {
            border-radius: 16px;
            overflow: hidden;
            border: none;
        }

        .card-header {
            border-bottom: none;
        }

        .stat-card {
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }

        .employee-row td:nth-child(2) {
            font-weight: 600;
            color: #2d3748;
        }

        .salary-cell {
            color: #28a745;
            font-weight: 700;
            font-size: 1.1em;
        }

        .advance-cell {
            color: #fd7e14;
            font-weight: 700;
            font-size: 1.1em;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-lg {
            padding: 12px 24px;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        /* Custom scrollbar */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }

        /* Animation for new rows */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .employee-row {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-card {
                padding: 20px !important;
            }

            .btn-lg {
                padding: 10px 16px;
                font-size: 14px;
            }

            .table-responsive {
                border-radius: 8px;
                border: 1px solid #dee2e6;
            }
        }
    </style>
@endpush

@push('custom-script')
    <script type="text/javascript" src="{{ asset('js/handlebars-v4.0.12.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            console.log('Salary Processing System Loaded');
            let employeesData = {}; // Store employee data

            // Search button click handler
            $('#search').on('click', function() {
                const date = $("#date").val();
                console.log('Searching for date:', date);

                if (!date) {
                    showNotification('Please select a month first', 'warning');
                    $('#date').focus();
                    return false;
                }

                // Update form date
                $('#formDate').val(date);

                // Show loading
                $('#loadingIndicator').show();
                $('#DocumentResults').html('');
                $('#hiddenInputsContainer').html('').hide();
                $('#emptyState').hide();
                $('#statsContainer').hide();
                $('#paySalaryBtn').hide();

                // Make API call
                $.ajax({
                    url: '/employee-monthly-salary-datewise-get',
                    type: 'GET',
                    data: { date: date },
                    success: function(response) {
                        console.log('API Response:', response);
                        $('#loadingIndicator').hide();

                        if (response && Object.keys(response).length > 0 && response.thsource) {
                            processAndDisplayData(response);
                        } else {
                            showEmptyState();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('API Error:', error);
                        $('#loadingIndicator').hide();
                        showNotification('Error loading data. Please try again.', 'error');
                        showEmptyState();
                    }
                });
            });

            // Process and display the data
            function processAndDisplayData(data) {
                try {
                    console.log('Processing data...', data);

                    // Extract thsource and employee data
                    const thsource = data.thsource;
                    const employees = [];
                    employeesData = {}; // Reset stored data

                    // Calculate totals
                    let totalSalary = 0;
                    let totalAdvance = 0;
                    let employeeCount = 0;

                    // Process each employee
                    Object.keys(data).forEach(key => {
                        if (key !== 'thsource') {
                            const employee = data[key];
                            const tdsource = employee.tdsource;
                            console.log('Processing tdsource:', tdsource);

                            // Fix malformed HTML first
                            let fixedTdsource = tdsource
                                .replace(/<input type="hidden" name="amount\[\]" value="(\d+)"</g, '<input type="hidden" name="amount[]" value="$1">')
                                .replace(/<input type="hidden" name="employee_id\[\]" value="(\d+)"</g, '<input type="hidden" name="employee_id[]" value="$1">')
                                .replace(/<\/td><\/td>$/, '</td>');

                            console.log('Fixed tdsource:', fixedTdsource);

                            // Parse tdsource to extract values using regex
                            // Extract SL number
                            const slMatch = fixedTdsource.match(/<td>(\d+)<\/td>/);
                            let sl = slMatch ? slMatch[1] : (employeeCount + 1);

                            // Extract Employee Name (the text between the 2nd <td> tags)
                            const nameMatch = fixedTdsource.match(/<td>(\d+)<\/td><td>([^<]+)<\/td>/);
                            let employeeName = nameMatch ? nameMatch[2] : 'Unknown';

                            // Extract Advance amount
                            const advanceMatch = fixedTdsource.match(/<td>(\d+)<\/td><td>([^<]+)<\/td><td>(\d+)<\/td>/);
                            let advance = advanceMatch ? advanceMatch[3] : '0';

                            // Extract amount from hidden input
                            const amountMatch = fixedTdsource.match(/name="amount\[\]" value="(\d+)"/);
                            let amount = amountMatch ? amountMatch[1] : '0';

                            // Extract employee_id from hidden input
                            const employeeIdMatch = fixedTdsource.match(/name="employee_id\[\]" value="(\d+)"/);
                            let employeeId = employeeIdMatch ? employeeIdMatch[1] : (employeeCount + 1);

                            // Extract checkbox value and check if it's checked
                            const checkboxMatch = fixedTdsource.match(/name="checkmanage\[\]" value="(\d+)"( checked)?/);
                            let checkboxValue = checkboxMatch ? checkboxMatch[1] : employeeCount;
                            let isChecked = checkboxMatch && checkboxMatch[2] === ' checked';

                            console.log('Extracted data:', {
                                sl: sl,
                                employeeName: employeeName,
                                advance: advance,
                                amount: amount,
                                employeeId: employeeId,
                                checkboxValue: checkboxValue,
                                isChecked: isChecked
                            });

                            // Store employee data
                            employeesData[employeeCount] = {
                                employeeId: employeeId,
                                amount: amount,
                                checkmanageValue: checkboxValue,
                                salary: amount,
                                advance: advance,
                                isChecked: isChecked
                            };

                            // Add to display array
                            employees.push({
                                sl: sl,
                                employeeName: employeeName,
                                advance: advance,
                                salary: amount,
                                amount: amount,
                                employeeId: employeeId,
                                index: employeeCount,
                                isChecked: isChecked
                            });

                            // Calculate totals
                            totalSalary += parseInt(amount) || 0;
                            totalAdvance += parseInt(advance) || 0;
                            employeeCount++;
                        }
                    });

                    console.log('Processed employees:', employees);
                    console.log('Stored employees data:', employeesData);

                    // Prepare data for template
                    const templateData = {
                        thsource: thsource,
                        employees: employees
                    };

                    // Render template
                    const source = $("#document-template").html();
                    const template = Handlebars.compile(source);
                    const html = template(templateData);

                    $('#DocumentResults').html(html);
                    $('#paySalaryBtn').fadeIn(300);

                    // Update statistics
                    updateStatistics(employeeCount, totalSalary, totalAdvance);

                    // Initialize checkbox functionality
                    initializeCheckboxes();

                    // Show success message
                    showNotification(`Loaded ${employeeCount} employees successfully`, 'success');

                } catch (error) {
                    console.error('Error processing data:', error);
                    showNotification('Error displaying employee data', 'error');
                }
            }

            // Update statistics display
            function updateStatistics(count, salary, advance) {
                $('#totalEmployees').text(count);
                $('#totalSalaryAmount').text('Rs ' + salary.toLocaleString());
                $('#totalAdvanceAmount').text('Rs ' + advance.toLocaleString());
                $('#netPayable').text('Rs ' + salary.toLocaleString());
                $('#statsContainer').fadeIn(300);
            }

            // Show empty state
            function showEmptyState() {
                $('#emptyState').fadeIn(300);
                $('#statsContainer').hide();
                $('#paySalaryBtn').hide();
            }

            // Initialize checkbox functionality
            function initializeCheckboxes() {
                // Remove any existing select all checkbox
                $('#selectAllContainer').remove();

                // Add select all container
                $('#statsContainer').after(`
                    <div class="row mb-3" id="selectAllContainer">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body py-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="selectAll">
                                        <label class="custom-control-label font-weight-bold" for="selectAll">
                                            Select All Employees
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                // Check checkboxes based on API data
                $('.employee-checkbox').each(function() {
                    const index = $(this).data('index');
                    const employeeData = employeesData[index];
                    if (employeeData && employeeData.isChecked) {
                        $(this).prop('checked', true);
                    }
                });

                // Select all functionality
                $('#selectAll').on('change', function() {
                    const isChecked = $(this).prop('checked');
                    $('.employee-checkbox').prop('checked', isChecked).trigger('change');
                });

                // Individual checkbox change
                $('.employee-checkbox').off('change').on('change', function() {
                    updateSelectedCount();
                    updateHiddenInputs();
                });

                // Initial setup
                updateSelectedCount();
                updateHiddenInputs();
            }

            // Update hidden inputs based on selections
            function updateHiddenInputs() {
                // Clear existing hidden inputs
                $('#hiddenInputsContainer').html('');

                // Get all checked checkboxes
                $('.employee-checkbox:checked').each(function() {
                    const index = $(this).data('index');
                    const employeeData = employeesData[index];

                    if (employeeData) {
                        // Add hidden inputs for this employee
                        $('#hiddenInputsContainer').append(`
                            <input type="hidden" name="employee_id[]" value="${employeeData.employeeId}">
                            <input type="hidden" name="amount[]" value="${employeeData.amount}">
                            <input type="hidden" name="checkmanage[]" value="${employeeData.checkmanageValue}">
                        `);
                    }
                });

                // Show the container (it's hidden by CSS)
                $('#hiddenInputsContainer').show();

                console.log('Hidden inputs:', $('#hiddenInputsContainer').html());
            }

            // Update selected count
            function updateSelectedCount() {
                const selectedCount = $('.employee-checkbox:checked').length;
                const totalCount = $('.employee-checkbox').length;

                if (selectedCount > 0) {
                    // Calculate total amount for selected employees
                    let totalAmount = 0;
                    $('.employee-checkbox:checked').each(function() {
                        const index = $(this).data('index');
                        const employeeData = employeesData[index];
                        if (employeeData) {
                            totalAmount += parseInt(employeeData.amount) || 0;
                        }
                    });

                    $('#paySalaryBtn').html(`<i class="fas fa-paper-plane mr-2"></i> Pay ${selectedCount} Employees (Rs ${totalAmount.toLocaleString()})`);

                    // Update select all checkbox
                    if (selectedCount === totalCount) {
                        $('#selectAll').prop('checked', true);
                    } else if (selectedCount === 0) {
                        $('#selectAll').prop('checked', false);
                    } else {
                        $('#selectAll').prop('checked', false);
                    }
                } else {
                    $('#paySalaryBtn').html('<i class="fas fa-paper-plane mr-2"></i> Process Payment');
                }
            }

            // Print functionality
            $('#printBtn').on('click', function() {
                if ($('#DocumentResults').is(':empty')) {
                    showNotification('Please load employee data first', 'warning');
                    return;
                }

                const printContent = $('#DocumentResults').html();
                const date = $('#date').val();
                const printWindow = window.open('', '_blank');

                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Salary Report - ${date}</title>
                            <style>
                                body { font-family: Arial; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; }
                                .header h2 { color: #333; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th { background: #667eea; color: white; padding: 12px; }
                                td { padding: 10px; border: 1px solid #ddd; }
                                .total { font-weight: bold; background: #f8f9fa; }
                                @media print {
                                    .no-print { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h2>Employee Salary Report</h2>
                                <p>Month: ${date} | Generated: ${new Date().toLocaleDateString()}</p>
                            </div>
                            ${printContent}
                            <div class="no-print" style="margin-top: 30px; text-align: center;">
                                <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                    Print Report
                                </button>
                                <button onclick="window.close()" style="margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                    Close
                                </button>
                            </div>
                        </body>
                    </html>
                `);

                printWindow.document.close();
            });

            // Form submission with confirmation
            $('#salaryForm').on('submit', function(e) {
                e.preventDefault();

                const selectedCount = $('.employee-checkbox:checked').length;
                if (selectedCount === 0) {
                    showNotification('Please select at least one employee', 'warning');
                    return;
                }

                // Calculate total amount
                let totalAmount = 0;
                $('.employee-checkbox:checked').each(function() {
                    const index = $(this).data('index');
                    const employeeData = employeesData[index];
                    if (employeeData) {
                        totalAmount += parseInt(employeeData.amount) || 0;
                    }
                });

                $('#selectedCount').text(selectedCount);
                $('#totalPayment').text('Rs ' + totalAmount.toLocaleString());
                $('#confirmationModal').modal('show');
            });

            // Confirm payment
            $('#confirmPayment').on('click', function() {
                $('#confirmationModal').modal('hide');

                // Show processing
                $('#paySalaryBtn').html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...').prop('disabled', true);

                // Submit form after delay
                setTimeout(() => {
                    $('#salaryForm').off('submit').submit();
                }, 1000);
            });

            // Auto search on page load
            setTimeout(() => {
                $('#search').trigger('click');
            }, 500);

            // Notification function
            function showNotification(message, type = 'info') {
                // Remove existing notifications
                $('.alert-notification').remove();

                // Create notification
                const alertClass = type === 'error' ? 'danger' : type;
                const icon = type === 'success' ? 'check-circle' :
                           type === 'error' ? 'exclamation-circle' : 'info-circle';

                const notification = `
                    <div class="alert alert-${alertClass} alert-notification alert-dismissible fade show"
                         style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        <i class="fas fa-${icon} mr-2"></i> ${message}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                `;

                $('body').append(notification);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    $('.alert-notification').alert('close');
                }, 5000);
            }

            // Debug: Log form data before submit
            $('#salaryForm').on('submit', function(e) {
                console.log('Form Data:', $(this).serialize());
            });
        });
    </script>
@endpush
