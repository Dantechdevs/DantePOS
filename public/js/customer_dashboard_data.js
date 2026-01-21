// inactive_customers.js
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.getElementById('refreshInactiveCustomers');
    const inactiveCustomersTableBody = document.getElementById('inactiveCustomersTableBody');

    // Load inactive customers when page loads
    loadInactiveCustomers();

    // Refresh button click event
    refreshBtn.addEventListener('click', function() {
        loadInactiveCustomers();
    });

    function loadInactiveCustomers() {
        // Show loading state
        inactiveCustomersTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Loading inactive customers...</p>
                </td>
            </tr>
        `;

        // Disable refresh button during loading
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-1"></i> Loading...';

        // Make AJAX request
        fetch('/inactive-customers')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.customers.length > 0) {
                    renderInactiveCustomersTable(data.customers);
                } else {
                    showNoInactiveCustomersMessage();
                }
            })
            .catch(error => {
                console.error('Error fetching inactive customers:', error);
                showErrorMessage();
            })
            .finally(() => {
                // Re-enable refresh button
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Refresh';
            });
    }

    function renderInactiveCustomersTable(customers) {
        let html = '';
        customers.forEach((customer, index) => {
            const daysSince = customer.days_since_last_purchase;
            const badgeClass = daysSince >= 7 ? 'badge-danger' :
                              daysSince >= 5 ? 'badge-warning' : 'badge-info';

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${customer.name || 'N/A'}</td>
                    <td>${customer.mobile || 'N/A'}</td>
                    <td>${formatDate(customer.last_purchase_date) || 'Never'}</td>
                    <td>
                        <span class="badge ${badgeClass}">
                            ${daysSince} days
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary send-reminder"
                                data-customer-id="${customer.id}"
                                data-customer-name="${customer.name}">
                            <i class="fas fa-envelope mr-1"></i> Remind
                        </button>
                    </td>
                </tr>
            `;
        });
        inactiveCustomersTableBody.innerHTML = html;

        // Add event listeners to reminder buttons
        document.querySelectorAll('.send-reminder').forEach(button => {
            button.addEventListener('click', function() {
                const customerId = this.getAttribute('data-customer-id');
                const customerName = this.getAttribute('data-customer-name');
                sendReminder(customerId, customerName);
            });
        });
    }

    function showNoInactiveCustomersMessage() {
        inactiveCustomersTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                    <p class="mb-0">All customers have made purchases in the last 3 days!</p>
                    <small class="text-muted">Great job maintaining customer engagement!</small>
                </td>
            </tr>
        `;
    }

    function showErrorMessage() {
        inactiveCustomersTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="mb-0">Error loading inactive customers. Please try again.</p>
                </td>
            </tr>
        `;
    }

    function formatDate(dateString) {
        if (!dateString) return null;
        const date = new Date(dateString);
        return date.toLocaleDateString('en-PK') + ' ' + date.toLocaleTimeString('en-PK', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function sendReminder(customerId, customerName) {
        // Implement reminder functionality (email, SMS, etc.)
        alert(`Sending reminder to ${customerName} (ID: ${customerId})`);
        // You can implement actual reminder logic here
    }
});
