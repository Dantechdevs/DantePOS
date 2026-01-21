class OfflineManager {
    constructor() {
        this.db = null;
        this.init();
        this.checkOnlineStatus();

        window.addEventListener('online', () => this.syncData());
        window.addEventListener('offline', () => this.showOfflineWarning());
    }

    async init() {
        this.db = await this.openDB();
        await this.cacheEssentialData();
    }

    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('POSDatabase', 4);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = event => {
                const db = event.target.result;

                // Create offline sales store
                if (!db.objectStoreNames.contains('offline_sales')) {
                    const store = db.createObjectStore('offline_sales', { keyPath: 'local_id' });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                }

                // Create products cache store
                if (!db.objectStoreNames.contains('products')) {
                    const store = db.createObjectStore('products', { keyPath: 'id' });
                }

                // Create customers cache store
                if (!db.objectStoreNames.contains('customers')) {
                    const store = db.createObjectStore('customers', { keyPath: 'id' });
                }
            };
        });
    }

    async cacheEssentialData() {
        try {
            // Cache products with enhanced unit information
            const productsResponse = await fetch('/products/essential');
            const products = await productsResponse.json();

            const tx = this.db.transaction('products', 'readwrite');
            const store = tx.objectStore('products');

            for (const product of products) {
                // Parse the unit_info JSON if it's stored as a string
                const unitInfo = typeof product.unit_info === 'string'
                    ? JSON.parse(product.unit_info)
                    : product.unit_info;

                // Ensure unit_info is always an array
                const availableUnits = Array.isArray(unitInfo) ? unitInfo : [];

                // Find default unit
                let defaultUnit = availableUnits.find(unit => unit.is_default);
                if (!defaultUnit && availableUnits.length > 0) {
                    defaultUnit = availableUnits[0];
                }

                // Enhance product data for offline use
                const enhancedProduct = {
                    id: product.id,
                    name: product.name,
                    product_code: product.product_code,
                    stock: product.stock,
                    cost: product.cost,
                    price: product.price,
                    unit_info: product.unit_info,
                    available_units: availableUnits,
                    default_unit_id: defaultUnit ? defaultUnit.unit_id : null,
                    default_unit: defaultUnit ? defaultUnit.unit : '',
                    default_selling_price: defaultUnit ? defaultUnit.selling_price : 0
                };

                await store.put(enhancedProduct);
            }

            // Cache customers
            const customersResponse = await fetch('/customers/essential');
            const customers = await customersResponse.json();

            const tx2 = this.db.transaction('customers', 'readwrite');
            const store2 = tx2.objectStore('customers');

            for (const customer of customers) {
                await store2.put(customer);
            }

            console.log('Essential data cached for offline use');
        } catch (error) {
            console.error('Failed to cache essential data:', error);
        }
    }

    async saveSale(validatedData) {
        if (!navigator.onLine) {
            console.log('Saving transaction offline');
            // Add local ID and timestamp for offline transactions
            validatedData.local_id = 'OFF-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
            validatedData.timestamp = new Date().toISOString();
            validatedData.synced = false;

            const tx = this.db.transaction('offline_sales', 'readwrite');
            const store = tx.objectStore('offline_sales');
            await store.add(validatedData);

            // Register for background sync
            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                try {
                    const registration = await navigator.serviceWorker.ready;
                    await registration.sync.register('sync-orders');
                } catch (error) {
                    console.log('Background sync not supported:', error);
                }
            }

            return { success: true, local_id: validatedData.local_id, offline: true };
        } else {
            console.log("online transaction ....")
            // Online - send directly to server
            try {
                const response = await fetch('/post-sale', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(validatedData)
                });

                return await response.json();
            } catch (error) {
                // If online request fails, save offline
                return await this.saveSale(validatedData);
            }
        }
    }

    async syncData() {
        try {
            const tx = this.db.transaction('offline_sales', 'readonly');
            const store = tx.objectStore('offline_sales');
            const transactions = await store.getAll();

            console.log('Found offline transactions:', transactions);

            if (transactions.length === 0) {
                console.log('No offline transactions to sync');
                return;
            }

            console.log(`Syncing ${transactions.length} offline transactions`);

            let successCount = 0;
            let errorCount = 0;

            for (const transaction of transactions) {
                try {
                    // Use the API endpoint instead of web route
                    const response = await fetch('/sales/offline-sync', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(transaction)
                    });

                    if (response.ok) {
                        const deleteTx = this.db.transaction('offline_sales', 'readwrite');
                        const deleteStore = deleteTx.objectStore('offline_sales');
                        await deleteStore.delete(transaction.local_id);
                        successCount++;
                    } else {
                        console.error('Failed to sync transaction:', transaction.local_id);
                        errorCount++;
                    }
                } catch (error) {
                    console.error('Failed to sync transaction:', error);
                    errorCount++;
                }
            }

            this.hideOfflineWarning();

            if (successCount > 0) {
                this.showSyncSuccess(`Successfully synced ${successCount} offline transactions`);
            }

            if (errorCount > 0) {
                this.showSyncError(`Failed to sync ${errorCount} transactions`);
            }

        } catch (error) {
            console.error('Failed to sync data:', error);
            this.showSyncError('Failed to sync offline data');
        }
    }

    checkOnlineStatus() {
        if (!navigator.onLine) {
            this.showOfflineWarning();
        } else {
            this.hideOfflineWarning();
        }
    }

    showOfflineWarning() {
        // Create or show offline warning
        let warning = document.getElementById('offline-warning');
        if (!warning) {
            warning = document.createElement('div');
            warning.id = 'offline-warning';
            warning.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #f39c12;
                color: white;
                text-align: center;
                padding: 10px;
                z-index: 10000;
                font-weight: bold;
            `;
            warning.textContent = 'You are currently offline. Transactions will be saved locally and synced when connection is restored.';
            document.body.appendChild(warning);
        } else {
            warning.style.display = 'block';
        }

        // Update status indicator
        this.updateOnlineStatus(false);
    }

    hideOfflineWarning() {
        const warning = document.getElementById('offline-warning');
        if (warning) {
            warning.style.display = 'none';
        }

        // Update status indicator
        this.updateOnlineStatus(true);
    }

    updateOnlineStatus(isOnline) {
        let statusIndicator = document.getElementById('online-status');
        if (!statusIndicator) {
            const userInfoContainer = document.querySelector('.user-info-container');
            if (userInfoContainer) {
                statusIndicator = document.createElement('div');
                statusIndicator.id = 'online-status';
                statusIndicator.style.cssText = `
                    margin-right: 15px;
                    display: flex;
                    align-items: center;
                    font-size: 0.8rem;
                `;

                const statusDot = document.createElement('span');
                statusDot.style.cssText = `
                    display: inline-block;
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    margin-right: 5px;
                `;

                const statusText = document.createElement('span');

                statusIndicator.appendChild(statusDot);
                statusIndicator.appendChild(statusText);
                userInfoContainer.insertBefore(statusIndicator, userInfoContainer.firstChild);
            }
        }

        if (statusIndicator) {
            const dot = statusIndicator.querySelector('span:first-child');
            const text = statusIndicator.querySelector('span:last-child');

            if (dot) {
                dot.style.backgroundColor = isOnline ? '#28a745' : '#dc3545';
            }

            if (text) {
                text.textContent = isOnline ? 'Online' : 'Offline';
            }
        }
    }

    showSyncSuccess(message) {
        // Show sync success message
        const success = document.createElement('div');
        success.id = 'sync-success';
        success.style.cssText = `
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            background: #28a745;
            color: white;
            text-align: center;
            padding: 10px 20px;
            z-index: 10000;
            font-weight: bold;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        success.textContent = message;
        document.body.appendChild(success);

        // Remove after 5 seconds
        setTimeout(() => {
            if (document.getElementById('sync-success')) {
                document.getElementById('sync-success').remove();
            }
        }, 5000);
    }

    showSyncError(message) {
        // Show sync error message
        const error = document.createElement('div');
        error.id = 'sync-error';
        error.style.cssText = `
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            background: #dc3545;
            color: white;
            text-align: center;
            padding: 10px 20px;
            z-index: 10000;
            font-weight: bold;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        error.textContent = message;
        document.body.appendChild(error);

        // Remove after 5 seconds
        setTimeout(() => {
            if (document.getElementById('sync-error')) {
                document.getElementById('sync-error').remove();
            }
        }, 5000);
    }

    async getCachedProducts() {
        try {
            const tx = this.db.transaction('products', 'readonly');
            const store = tx.objectStore('products');
            return await store.getAll();
        } catch (error) {
            console.error('Failed to get cached products:', error);
            return [];
        }
    }

    async getCachedCustomers() {
        try {
            const tx = this.db.transaction('customers', 'readonly');
            const store = tx.objectStore('customers');
            return await store.getAll();
        } catch (error) {
            console.error('Failed to get cached customers:', error);
            return [];
        }
    }

    async getProductWithUnits(productId) {
        try {
            const tx = this.db.transaction('products', 'readonly');
            const store = tx.objectStore('products');
            return await store.get(parseInt(productId));
        } catch (error) {
            console.error('Failed to get product:', error);
            return null;
        }
    }

    convertUnitQuantity(quantity, fromConversion, toConversion) {
        if (!fromConversion || !toConversion || fromConversion === 0 || toConversion === 0) return quantity;
        return (quantity * fromConversion) / toConversion;
    }

    async searchProductsOffline(searchTerm) {
        const cachedProducts = await this.getCachedProducts();
        const filteredProducts = cachedProducts.filter(product =>
            product.name.toLowerCase().includes(searchTerm) ||
            (product.product_code && product.product_code.toLowerCase().includes(searchTerm))
        );

        // Enhance with unit information for display
        return filteredProducts.map(product => ({
            id: product.id,
            name: product.name,
            product_code: product.product_code,
            stock: product.stock,
            available_units: product.available_units || [],
            default_unit_id: product.default_unit_id,
            default_unit: product.default_unit,
            default_selling_price: product.default_selling_price
        }));
    }

    async getOfflineSalesCount() {
        try {
            const tx = this.db.transaction('offline_sales', 'readonly');
            const store = tx.objectStore('offline_sales');
            return await store.count();
        } catch (error) {
            console.error('Failed to count offline sales:', error);
            return 0;
        }
    }
}

// Initialize offline manager
const offlineManager = new OfflineManager();

// Override form submission for offline capability
document.addEventListener('DOMContentLoaded', function () {
    // Add manual sync button
    if (!document.getElementById('manual-sync-btn')) {
        const syncButton = document.createElement('button');
        syncButton.id = 'manual-sync-btn';
        syncButton.textContent = 'Sync Offline Data';
        syncButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;

        syncButton.addEventListener('click', () => {
            offlineManager.syncData();
        });

        document.body.appendChild(syncButton);

        // Show button if there are offline transactions
        setInterval(async () => {
            try {
                const count = await offlineManager.getOfflineSalesCount();
                const syncButton = document.getElementById('manual-sync-btn');
                if (syncButton) {
                    syncButton.style.display = count > 0 ? 'block' : 'none';
                    syncButton.textContent = `Sync Offline Data (${count})`;
                }
            } catch (error) {
                console.error('Failed to check offline transactions:', error);
            }
        }, 10000);
    }

    // Modify sale form submission
    // const saleForm = document.getElementById('sale-form');
    // if (saleForm) {
    //     const originalSubmit = saleForm.onsubmit;

    //     saleForm.onsubmit = async function (e) {
    //         e.preventDefault();

    //         // Check if offline and show confirmation
    //         if (!navigator.onLine) {
    //             const proceed = confirm('You are currently offline. This sale will be saved locally and synced when you are back online. Do you want to continue?');
    //             if (!proceed) return false;
    //         }

    //         // Collect form data
    //         const formData = new FormData(saleForm);
    //         const productIds = Array.from(document.querySelectorAll('[name="product_id[]"]')).map(input => input.value);
    //         const quantities = Array.from(document.querySelectorAll('[name="quantity[]"]')).map(input => parseFloat(input.value) || 0);
    //         const units = Array.from(document.querySelectorAll('[name="unit[]"]')).map(input => input.value);
    //         const sellingPrices = Array.from(document.querySelectorAll('[name="selling_price[]"]')).map(input => parseFloat(input.value) || 0);

    //         const validatedData = {
    //             customer_id: formData.get('customer_id'),
    //             godown_id: formData.get('godown_id'),
    //             product_id: productIds,
    //             quantity: quantities,
    //             unit: units,
    //             selling_price: sellingPrices,
    //             cost: Array.from(document.querySelectorAll('[name="cost[]"]')).map(input => parseFloat(input.value) || 0),
    //             calculatedCost: Array.from(document.querySelectorAll('[name="calculatedCost[]"]')).map(input => parseFloat(input.value) || 0),
    //             subtotal: parseFloat(formData.get('subtotal')) || 0,
    //             discount: parseFloat(formData.get('discount')) || 0,
    //             discount_type: formData.get('discount_type'),
    //             other_charges: parseFloat(formData.get('other_charges')) || 0,
    //             grand_total: parseFloat(formData.get('grand_total')) || 0,
    //             paid_amount: parseFloat(formData.get('paid_amount')) || 0,
    //             change_amount: parseFloat(formData.get('change_amount')) || 0,
    //             payment_type: formData.get('payment_type'),
    //             status: parseInt(formData.get('status')) || 0,
    //             description: formData.get('description'),
    //             date: formData.get('date')
    //         };

    //         // Save transaction (online or offline)
    //         const result = await offlineManager.saveSale(validatedData);

    //         if (result.success) {
    //             if (result.offline) {
    //                 alert('Transaction saved locally. It will be synced when you are back online.\nLocal Invoice Number: ' + result.local_id);

    //                 // Reset form
    //                 saleForm.reset();
    //                 // Clear items table if needed
    //                 const itemsTable = document.querySelector('.items-table tbody');
    //                 if (itemsTable) itemsTable.innerHTML = '';

    //                 // Reset totals
    //                 const subtotalInput = document.querySelector('[name="subtotal"]');
    //                 const grandTotalInput = document.querySelector('[name="grand_total"]');
    //                 const paidAmountInput = document.querySelector('[name="paid_amount"]');
    //                 const changeAmountInput = document.querySelector('[name="change_amount"]');

    //                 if (subtotalInput) subtotalInput.value = '0.00';
    //                 if (grandTotalInput) grandTotalInput.value = '0.00';
    //                 if (paidAmountInput) paidAmountInput.value = '0.00';
    //                 if (changeAmountInput) changeAmountInput.value = '0.00';
    //             } else {
    //                 alert('Transaction saved successfully!');
    //                 if (result.invoice_url) {
    //                     window.location.href = result.invoice_url;
    //                 }
    //             }
    //         } else {
    //             alert('Error saving transaction: ' + (result.message || 'Unknown error'));
    //         }

    //         return false;
    //     };
    // }

    // Add offline product search functionality
    const productSearch = document.querySelector('input[name="product_search"]');
    if (productSearch) {
        productSearch.addEventListener('input', async function (e) {
            const searchTerm = e.target.value.toLowerCase();
            if (searchTerm.length < 2) {
                // Clear results if search term is too short
                const resultsContainer = document.getElementById('search-results');
                if (resultsContainer) resultsContainer.remove();
                return;
            }

            if (!navigator.onLine) {
                // Search in cached products
                const filteredProducts = await offlineManager.searchProductsOffline(searchTerm);

                // Display results
                displayOfflineSearchResults(filteredProducts, productSearch);
            }
        });
    }

    // Close search results when clicking outside
    document.addEventListener('click', function (e) {
        const resultsContainer = document.getElementById('search-results');
        if (resultsContainer && !resultsContainer.contains(e.target) && e.target !== productSearch) {
            resultsContainer.remove();
        }
    });
});

// Function to display offline search results
function displayOfflineSearchResults(products, searchInput) {
    // Remove existing results
    const existingResults = document.getElementById('search-results');
    if (existingResults) existingResults.remove();

    if (products.length === 0) return;

    const resultsContainer = document.createElement('div');
    resultsContainer.id = 'search-results';
    resultsContainer.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid #ccc;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        width: ${searchInput.offsetWidth}px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;

    products.forEach(product => {
        const item = document.createElement('div');
        item.style.padding = '8px';
        item.style.borderBottom = '1px solid #eee';
        item.style.cursor = 'pointer';
        item.style.display = 'flex';
        item.style.justifyContent = 'space-between';
        item.style.alignItems = 'center';

        item.innerHTML = `
            <div>
                <strong>${product.name}</strong>
                ${product.product_code ? `(${product.product_code})` : ''}<br>
                <small>Stock: ${product.stock} | Default: ${product.default_selling_price}/${product.default_unit}</small>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm">Add</button>
            </div>
        `;

        item.addEventListener('click', () => {
            addProductToSale(product);
            resultsContainer.remove();
        });

        resultsContainer.appendChild(item);
    });

    // Position below search input
    const searchRect = searchInput.getBoundingClientRect();
    resultsContainer.style.top = (searchRect.bottom + window.scrollY) + 'px';
    resultsContainer.style.left = (searchRect.left + window.scrollX) + 'px';

    document.body.appendChild(resultsContainer);
}

// Function to handle adding product to sale with unit selection
function addProductToSale(product) {
    const saleForm = document.getElementById('sale-form');
    if (!saleForm) return;

    // Create product row in sale table
    const tbody = saleForm.querySelector('.items-table tbody');
    if (!tbody) return;

    const newRow = document.createElement('tr');
    newRow.className = 'product-row';

    // If multiple units available, create a select dropdown
    let unitOptions = '';
    if (product.available_units && product.available_units.length > 0) {
        product.available_units.forEach(unit => {
            const selected = unit.unit_id == product.default_unit_id ? 'selected' : '';
            unitOptions += `<option value="${unit.unit_id}" data-conversion="${unit.conversion}" data-price="${unit.selling_price}" ${selected}>${unit.unit}</option>`;
        });
    }

    const sellingPrice = product.default_selling_price || 0;
    const quantity = 1;
    const total = sellingPrice * quantity;

    newRow.innerHTML = `
        <td>${product.name}</td>
        <td>${product.stock}</td>
        <td><input type="number" name="quantity[]" value="${quantity}" min="0.01" step="0.01" class="form-control form-control-sm"></td>
        <td>
            ${product.available_units.length > 1 ?
            `<select name="unit[]" class="form-control form-control-sm unit-select">${unitOptions}</select>` :
            `<input type="hidden" name="unit[]" value="${product.default_unit_id}">${product.default_unit}`
        }
        </td>
        <td><input type="number" name="selling_price[]" value="${sellingPrice}" class="form-control form-control-sm price-input"></td>
        <td class="total-amount">${total.toFixed(2)}</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item">×</button></td>
        <input type="hidden" name="product_id[]" value="${product.id}">
        <input type="hidden" name="productName[]" value="${product.name}">
        <input type="hidden" name="cost[]" value="${product.cost || 0}">
        <input type="hidden" name="calculatedCost[]" value="${product.cost || 0}">
    `;

    tbody.appendChild(newRow);

    // Add event listeners for dynamic calculations
    const quantityInput = newRow.querySelector('input[name="quantity[]"]');
    const priceInput = newRow.querySelector('input[name="selling_price[]"]');
    const unitSelect = newRow.querySelector('select[name="unit[]"]');

    const updateTotal = () => {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        newRow.querySelector('.total-amount').textContent = (quantity * price).toFixed(2);
        updateGrandTotal();
    };

    quantityInput.addEventListener('input', updateTotal);
    priceInput.addEventListener('input', updateTotal);

    if (unitSelect) {
        unitSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const newPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            priceInput.value = newPrice;
            updateTotal();
        });
    }

    newRow.querySelector('.remove-item').addEventListener('click', function () {
        newRow.remove();
        updateGrandTotal();
    });

    updateGrandTotal();
}

// Function to update grand total (you'll need to implement this based on your existing code)
function updateGrandTotal() {
    // This should be implemented based on your existing sale calculation logic
    let subtotal = 0;
    document.querySelectorAll('.total-amount').forEach(element => {
        subtotal += parseFloat(element.textContent) || 0;
    });

    const discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;
    const otherCharges = parseFloat(document.querySelector('[name="other_charges"]').value) || 0;

    let grandTotal = subtotal - discount + otherCharges;
    if (grandTotal < 0) grandTotal = 0;

    const subtotalInput = document.querySelector('[name="subtotal"]');
    const grandTotalInput = document.querySelector('[name="grand_total"]');

    if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
    if (grandTotalInput) grandTotalInput.value = grandTotal.toFixed(2);

    // Update paid amount and change if needed
    const paidAmountInput = document.querySelector('[name="paid_amount"]');
    if (paidAmountInput && paidAmountInput.value) {
        const paidAmount = parseFloat(paidAmountInput.value) || 0;
        const changeAmountInput = document.querySelector('[name="change_amount"]');
        if (changeAmountInput) {
            const change = paidAmount - grandTotal;
            changeAmountInput.value = change > 0 ? change.toFixed(2) : '0.00';
        }
    }
}

// Initialize online status
setTimeout(() => {
    offlineManager.updateOnlineStatus(navigator.onLine);
}, 1000);
