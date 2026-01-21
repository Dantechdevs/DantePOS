$(function() {
    let searchTimeout;
    const searchInput = $('#productSearch');
    const searchResults = $('#searchResults');
    const selectedProductsBody = $('#selectedProductsBody');
    let products = [];

    // Product Search with Debounce
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if(query.length < 2) {
            searchResults.hide();
            return;
        }

        searchTimeout = setTimeout(() => {
            performProductSearch(query);
        }, 300);
    });

    function performProductSearch(query) {
        const searchUrl = searchInput.data('search-url');

        $.ajax({
            url: searchUrl,
            method: 'GET',
            data: { term: query },
            dataType: 'json',
            success: function(response) {
                if(response.results.length > 0) {
                    products = response.results;
                    renderSearchResults(response.results);
                } else {
                    showNoResults();
                }
            },
            error: function(xhr) {
                console.error('Search error:', xhr.responseText);
                showSearchError();
            }
        });
    }

    function renderSearchResults(results) {
        let html = '';

        results.forEach(product => {
            html += `
                <div class="search-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${product.productName}</strong> (${product.code})<br>
                            <small>Stock: ${product.productQty} ${product.productUnit}</small>
                        </div>
                        <div>
                            Cost: $${product.purchasePrice}
                            <button class="btn btn-sm btn-primary ml-2 add-product"
                                    data-id="${product.productID}">Add</button>
                        </div>
                    </div>
                </div>
            `;
        });

        searchResults.html(html).show();
    }

    function showNoResults() {
        searchResults.html('<div class="search-item">No products found</div>').show();
    }

    function showSearchError() {
        searchResults.html('<div class="search-item text-danger">Error loading results</div>').show();
    }

    // Add Product to Table
    searchResults.on('click', '.add-product', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const product = products.find(p => p.productID == productId);
        if(product) {
            addProductToTable(product);
            searchInput.val('');
            searchResults.hide();
            calculateTotals();
        }
    });

    function addProductToTable(product) {
        const existingRow = selectedProductsBody.find(`tr[data-id="${product.productID}"]`);

        if(existingRow.length > 0) {
            const qtyInput = existingRow.find('.quantity');
            qtyInput.val(parseInt(qtyInput.val()) + 1);
        } else {
            const rowHtml = `
                <tr data-id="${product.productID}">
                    <td>
                        <input type="hidden" name="products[${product.productID}][id]" value="${product.productID}">
                        <strong>${product.productName}</strong><br>
                        <small>${product.code} (${product.productUnit})</small>
                    </td>
                    <td>
                        <input type="number" name="products[${product.productID}][quantity]"
                               class="form-control quantity" value="1" min="1" max="${product.productQty}">
                    </td>
                    <td>
                        <input type="number" name="products[${product.productID}][price]"
                               class="form-control price" value="${product.purchasePrice}" step="0.01">
                    </td>
                    <td class="product-total">$${product.purchasePrice}</td>
                    <td>
                        <button class="btn btn-danger btn-sm remove-product">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            selectedProductsBody.append(rowHtml);
        }

        attachRowEventListeners();
        calculateTotals();
    }

    // Table Interactions
    function attachRowEventListeners() {
        selectedProductsBody.off('change', '.quantity, .price')
            .on('change', '.quantity, .price', calculateTotals)
            .off('click', '.remove-product')
            .on('click', '.remove-product', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });
    }

    // Calculate Totals
    function calculateTotals() {
        let subtotal = 0;

        selectedProductsBody.find('tr').each(function() {
            const $row = $(this);
            const qty = parseFloat($row.find('.quantity').val()) || 0;
            const price = parseFloat($row.find('.price').val()) || 0;
            const total = qty * price;

            subtotal += total;
            $row.find('.product-total').text(`$${total.toFixed(2)}`);
        });

        const tax = subtotal * 0.16;
        const grandTotal = subtotal + tax;

        $('#subtotal').text(`$${subtotal.toFixed(2)}`);
        $('#tax').text(`$${tax.toFixed(2)}`);
        $('#grandTotal').text(`$${grandTotal.toFixed(2)}`);
    }

    // Initial calculation
    calculateTotals();
});
