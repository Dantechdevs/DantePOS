// supplier_day_filter.js
document.addEventListener('DOMContentLoaded', function() {
    const daySelect = document.getElementById('daySelect');
    const supplierTableBody = document.getElementById('supplierTableBody');

    // Load suppliers when page loads
    loadSuppliers(daySelect.value);

    // Load suppliers when day changes
    daySelect.addEventListener('change', function() {
        loadSuppliers(this.value);
    });

    function loadSuppliers(day) {
        // Show loading state
        supplierTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Loading suppliers for ${capitalizeFirstLetter(day)}...</p>
                </td>
            </tr>
        `;

        // Make AJAX request to get suppliers for the selected day
        fetch(`/suppliers-by-day/${day}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.suppliers.length > 0) {
                    renderSuppliersTable(data.suppliers);
                } else {
                    showNoSuppliersMessage(day);
                }
            })
            .catch(error => {
                console.error('Error fetching suppliers:', error);
                showErrorMessage();
            });
    }

    function renderSuppliersTable(suppliers) {
        let html = '';
        suppliers.forEach((supplier, index) => {
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${supplier.name || 'N/A'}</td>
                    <td>${supplier.mobile || 'N/A'}</td>
                    <td class="text-end">${formatBalance(supplier.balance)}</td>
                </tr>
            `;
        });
        supplierTableBody.innerHTML = html;
    }

    function showNoSuppliersMessage(day) {
        supplierTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                    <p class="mb-0">No suppliers available on ${capitalizeFirstLetter(day)}</p>
                </td>
            </tr>
        `;
    }

    function showErrorMessage() {
        supplierTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="mb-0">Error loading suppliers. Please try again.</p>
                </td>
            </tr>
        `;
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function formatBalance(balance) {
        if (balance === null || balance === undefined) return '0.00';
        return parseFloat(balance).toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
});
