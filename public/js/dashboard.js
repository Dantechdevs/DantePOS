// dashboard.js

// Initialize chart variable and current period
let salesPurchaseChart = null;
let currentPeriod = 'month'; // Default period

document.addEventListener('DOMContentLoaded', function() {
    // Load initial dashboard data
    loadDashboardData();

    // Setup search functionality for employee table
    setupEmployeeSearch();

    // Setup period selector buttons
    setupPeriodButtons();
});

/**
 * Setup employee search functionality
 */
function setupEmployeeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#employeeTableBody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

/**
 * Setup period selector buttons with click handlers
 */
function setupPeriodButtons() {
    const periodButtons = document.querySelectorAll('[data-period]');
    if (periodButtons.length > 0) {
        periodButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('[data-period]').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                // Update current period and load chart data
                currentPeriod = this.dataset.period;
                loadChartData(currentPeriod);
            });
        });
    }
}

/**
 * Load all dashboard data
 */
function loadDashboardData() {
    showLoadingIndicator();

    fetch('/dashboard/data')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            renderFinancialCards(data.financialData);
            renderSummaryCards(data.summaryData);
            renderEmployeeTable(data.employees);
            renderSalesPurchaseChart(data.chartData);
            hideLoadingIndicator();
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            showErrorAlert('Failed to load dashboard data');
            hideLoadingIndicator();
        });
}

/**
 * Load chart data based on selected period
 * @param {string} period - 'month', 'week', or 'year'
 */
function loadChartData(period) {
    showChartLoading();

    fetch(`/dashboard/chart-data?period=${period}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch chart data');
            }
            return response.json();
        })
        .then(data => {
            // Format week labels if in weekly view
            if (period === 'week') {
                data.labels = formatWeekLabels(data.labels);
            }
            updateChart(data);
            hideChartLoading();
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            showErrorAlert('Failed to load chart data');
            hideChartLoading();
        });
}

/**
 * Format week labels consistently
 * @param {Array} labels - Array of week labels
 * @returns {Array} Formatted week labels
 */
function formatWeekLabels(labels) {
    return labels.map((label, index) => {
        // Handle both "Week X" and raw numbers
        const weekNum = typeof label === 'string'
            ? label.replace(/[^\d]/g, '') // Extract numbers only
            : index + 1;
        return `Week ${String(weekNum).padStart(2, '0')}`;
    });
}

/**
 * Render financial cards with data
 * @param {Object} data - Financial data
 */
function renderFinancialCards(data) {
    const container = document.getElementById('financialCardsRow');
    if (!container) return;

    container.innerHTML = `
        <!-- Customers Cash -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary mb-1">Customers Cash</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${formatCurrency(data.customersCash)}</div>
                            <small class="text-muted">${data.currency} </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payables -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-hand-holding-usd fa-2x text-warning"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning mb-1">Payables</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${formatCurrency(data.suppliersCash)}</div>
                            <small class="text-muted">${data.currency} </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-shopping-cart fa-2x text-success"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-success mb-1">Total Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${formatCurrency(data.sale_total)}</div>
                            <small class="text-muted">${data.currency}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Cash -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-info mb-1">Total Cash</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${formatCurrency(data.totalCash)}</div>
                            <small class="text-muted">${data.currency}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-coins fa-2x text-danger"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-danger mb-1">Total Expenses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${formatCurrency(data.total_expenses)}</div>
                            <small class="text-muted">${data.currency}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card border-left-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-warehouse fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-secondary mb-1">Stock Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${formatCurrency(data.totalAmount)}</div>
                            <small class="text-muted">${data.currency}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Render summary cards with data
 * @param {Object} data - Summary data
 */
function renderSummaryCards(data) {
    const container = document.getElementById('summaryCardsRow');
    if (!container) return;

    container.innerHTML = `
        <!-- Sales -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-shopping-cart fa-2x text-success"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-success mb-1">Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${data.countSales}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-shopping-bag fa-2x text-primary"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary mb-1">Purchase</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${data.countPurchase}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-info mb-1">Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${data.countCustomers}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-boxes fa-2x text-warning"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning mb-1">Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${data.countProducts || '0'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-user-tie fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-secondary mb-1">Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${data.countEmployees || '0'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suppliers -->
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-truck fa-2x text-danger"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-danger mb-1">Suppliers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${data.countSupplier}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Render employee table with data
 * @param {Array} employees - Employee data
 */
function renderEmployeeTable(employees) {
    const container = document.getElementById('employeeTableBody');
    if (!container) return;

    container.innerHTML = employees.map(employee => `
        <tr>
            <td>${employee.name}</td>
            <td class="text-end">${formatCurrency(employee.advance)}</td>
            <td class="text-end">${formatCurrency(employee.returned)}</td>
            <td class="text-end ${employee.advance - employee.returned > 0 ? 'text-danger' : 'text-success'}">
                ${formatCurrency(Math.abs(employee.advance - employee.returned))}
                ${employee.advance - employee.returned > 0 ? 'DB' : 'CR'}
            </td>
        </tr>
    `).join('');
}

/**
 * Render the sales/purchase chart
 * @param {Object} chartData - Chart data with labels, salesData, and purchaseData
 */
function renderSalesPurchaseChart(chartData) {
    const ctx = document.getElementById('salesPurchaseChart');
    if (!ctx) return;

    // Format week labels if in weekly view
    if (currentPeriod === 'week') {
        chartData.labels = formatWeekLabels(chartData.labels);
    }

    // Destroy previous chart if exists
    if (salesPurchaseChart) {
        salesPurchaseChart.destroy();
    }

    salesPurchaseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Sales',
                    data: chartData.salesData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Purchases',
                    data: chartData.purchaseData,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${formatCurrency(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false, // Show all labels
                        maxRotation: 45, // Rotate labels if needed
                        minRotation: 45,
                        callback: function(value, index) {
                            // For weekly view, ensure consistent formatting
                            if (currentPeriod === 'week') {
                                return `Week ${String(index + 1).padStart(2, '0')}`;
                            }
                            return this.getLabelForValue(value);
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Update chart with new data
 * @param {Object} data - New chart data
 */
/**
 * Update chart with new data
 * @param {Object} data - Chart data with labels, salesData, and purchaseData
 */
function updateChart(data) {
    // Validate incoming data structure
    if (!data || !data.labels || !data.salesData || !data.purchaseData) {
        console.error('Invalid chart data structure:', data);
        showErrorAlert('Invalid chart data received');
        return;
    }

    // Always format week labels consistently
    if (currentPeriod === 'week') {
        data.labels = formatWeekLabels(data.labels);
    }

    // Destroy existing chart if:
    // 1. No chart exists yet
    // 2. Switching between different period types
    // 3. Data structure changed significantly
    const shouldRecreateChart = !salesPurchaseChart ||
        isDifferentPeriodType(data) ||
        isDataStructureChanged(data);

    if (shouldRecreateChart) {
        if (salesPurchaseChart) {
            salesPurchaseChart.destroy();
        }
        renderSalesPurchaseChart(data);
    } else {
        // Smooth update for same period type
        try {
            salesPurchaseChart.data.labels = data.labels;
            salesPurchaseChart.data.datasets[0].data = data.salesData;
            salesPurchaseChart.data.datasets[1].data = data.purchaseData;
            salesPurchaseChart.update();
        } catch (error) {
            console.error('Chart update failed, recreating:', error);
            salesPurchaseChart.destroy();
            renderSalesPurchaseChart(data);
        }
    }
}

/**
 * Check if period type has changed significantly
 */
function isDifferentPeriodType(data) {
    if (!salesPurchaseChart) return false;

    const currentLabels = salesPurchaseChart.data.labels;
    const newLabels = data.labels;

    // Switching between week and non-week views
    const wasWeekly = currentLabels.some(l => typeof l === 'string' && l.startsWith('Week'));
    const isWeekly = currentPeriod === 'week';

    if (wasWeekly !== isWeekly) return true;

    // Switching between month and year views with different label formats
    if (!isWeekly && currentLabels.length !== newLabels.length) {
        return true;
    }

    return false;
}

/**
 * Check if data structure changed significantly
 */
function isDataStructureChanged(data) {
    if (!salesPurchaseChart) return false;

    // Major change in data points count
    if (salesPurchaseChart.data.labels.length !== data.labels.length) {
        return true;
    }

    // Dataset structure changed
    if (salesPurchaseChart.data.datasets.length !== 2) {
        return true;
    }

    return false;
}

/**
 * Format currency value
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

/**
 * Show loading indicator for the whole dashboard
 */
function showLoadingIndicator() {
    // Implement your loading indicator logic here
    console.log('Loading dashboard data...');
}

/**
 * Hide loading indicator for the whole dashboard
 */
function hideLoadingIndicator() {
    // Implement your loading indicator hide logic here
    console.log('Dashboard data loaded');
}

/**
 * Show loading indicator for chart
 */
function showChartLoading() {
    const canvas = document.getElementById('salesPurchaseChart');
    if (canvas) {
        canvas.style.display = 'none';
        let loadingDiv = canvas.nextElementSibling;
        if (!loadingDiv || !loadingDiv.classList.contains('chart-loading')) {
            loadingDiv = document.createElement('div');
            loadingDiv.className = 'chart-loading text-center py-5';
            loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i>';
            canvas.parentNode.insertBefore(loadingDiv, canvas.nextSibling);
        }
        loadingDiv.style.display = 'block';
    }
}

/**
 * Hide loading indicator for chart
 */
function hideChartLoading() {
    const canvas = document.getElementById('salesPurchaseChart');
    if (canvas) {
        canvas.style.display = 'block';
        const loadingDiv = canvas.nextElementSibling;
        if (loadingDiv && loadingDiv.classList.contains('chart-loading')) {
            loadingDiv.style.display = 'none';
        }
    }
}

/**
 * Show error alert
 * @param {string} message - Error message to display
 */
function showErrorAlert(message) {
    // Implement your error alert logic here
    console.error(message);
    alert(message);
}
