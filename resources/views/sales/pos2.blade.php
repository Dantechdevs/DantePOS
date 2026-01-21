<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>POS Invoice | Al Noor Super Market</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ===== Color Scheme & Navbar ===== */
        body {
            background-color: #f5f7fa;
        }

        .navbar-custom {
            background: linear-gradient(90deg, #00c851 0%, #6f42c1 100%);
        }

        .navbar-brand,
        .navbar-custom .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            text-decoration: underline;
        }

        /* ===== Layout Containers ===== */
        .pos-container {
            padding: 1rem;
        }

        .left-panel {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 1rem;
        }

        .right-panel {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 1rem;
            max-height: 80vh;
            overflow-y: auto;
        }

        /* ===== Cart Table ===== */
        .cart-table thead {
            background-color: #6f42c1;
            /* Purple-ish from gradient */
            color: #fff;
        }

        /* Make table scrollable if many items */
        .cart-table-wrapper {
            max-height: 300px;
            /* Adjust as needed */
            overflow-y: auto;
            margin-bottom: 0.5rem;
        }

        .cart-table td button {
            border: none;
            background: none;
            color: #dc3545;
            cursor: pointer;
        }

        /* ===== Action Buttons ===== */
        .pos-actions .btn {
            min-width: 100px;
            margin: 0.25rem;
        }

        .btn-hold {
            background: #fd7e14;
            color: #fff;
        }

        .btn-multiple {
            background: #007bff;
            color: #fff;
        }

        .btn-cash {
            background: #00c851;
            color: #fff;
        }

        /* ===== Item List (Right Panel) ===== */
        .item-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .item-box:hover {
            background: #f1f1f1;
        }

        .item-box .price {
            font-weight: 600;
            color: #00c851;
            /* Green from gradient start */
        }

        .item-box .name {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* ===== Misc ===== */
        .discount-edit-icon {
            cursor: pointer;
            color: #007bff;
        }

        .discount-edit-icon:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- TOP NAV BAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Al Noor Super Market</a>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">Sales List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">New Invoice</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Items List</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item px-2">
                    <!-- Clicking this link opens a modal showing the hold list -->
                    <a class="nav-link" href="#" id="holdListLink">
                        <i class="fas fa-list"></i> Hold List <span class="badge bg-danger" id="holdCount">0</span>
                    </a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link" href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link" href="#"><i class="fas fa-user"></i> Admin</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="container-fluid pos-container">
        <div class="row">
            <!-- LEFT PANEL: CART & INVOICE -->
            <div class="col-lg-8 mb-3">
                <div class="left-panel">
                    <h5 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Sales Invoice</h5>

                    <!-- Customer & Search Row -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label for="customerSelect" class="form-label">Customer</label>
                            <div class="input-group">
                                <select class="form-select" id="customerSelect">
                                    <option>Walk-in Customer</option>
                                    <option>Customer A</option>
                                    <option>Customer B</option>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" title="Add new customer">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Search / Barcode</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control" id="itemSearchInput"
                                    placeholder="Type item name or scan barcode">
                            </div>
                        </div>
                    </div>

                    <!-- Cart Table (scrollable) -->
                    <div class="cart-table-wrapper">
                        <table class="table cart-table" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Stock</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th style="width:50px;">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamically added rows go here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- SMS Checkbox -->
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="sendSmsCheck">
                        <label class="form-check-label" for="sendSmsCheck">
                            Send SMS to Customer <i class="fas fa-info-circle text-info"
                                title="SMS with invoice details"></i>
                        </label>
                    </div>

                    <!-- Totals / Actions -->
                    <div class="row mt-3">
                        <div class="col-md-3 text-center">
                            <div>Quantity</div>
                            <div class="h5 mb-0" id="cartQty">0</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div>Total Amount</div>
                            <div class="h5 mb-0" id="cartTotal">PKR 0.00</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div>
                                Discount
                                <i class="fa-solid fa-pen discount-edit-icon" id="discountEditBtn"
                                    title="Click to apply discount"></i>
                            </div>
                            <div class="h5 mb-0" id="cartDiscount">PKR 0.00</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div>Grand Total</div>
                            <div class="h5 mb-0" id="cartGrandTotal">PKR 0.00</div>
                        </div>
                    </div>

                    <div class="pos-actions text-center mt-3">
                        <button class="btn btn-hold" id="btnHold">
                            <i class="fas fa-hand-paper"></i> Hold
                        </button>
                        <button class="btn btn-multiple" id="btnMultiple">
                            <i class="fas fa-th-large"></i> Multiple
                        </button>
                        <button class="btn btn-cash" id="btnCash">
                            <i class="fas fa-money-bill-wave"></i> Cash
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: ITEM LIST -->
            <div class="col-lg-4">
                <div class="right-panel">
                    <h6 class="mb-3">All Items</h6>

                    <!-- Filter row (category & item search) -->
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <select class="form-select" id="categorySelect">
                                <option value="">All Categories</option>
                                <option value="snacks">Snacks</option>
                                <option value="drinks">Drinks</option>
                                <option value="stationery">Stationery</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="itemFilterInput"
                                    placeholder="Filter Items">
                                <button class="btn btn-secondary" id="filterResetBtn">All</button>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div id="itemList">
                        <!-- Dynamically created item boxes -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HOLD LIST MODAL -->
    <div class="modal fade" id="holdListModal" tabindex="-1" aria-labelledby="holdListModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holdListModalLabel">Hold List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- This is populated by JS -->
                    <div id="holdListContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS: Bootstrap & jQuery (optional for Bootstrap 5) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== DUMMY DATA =====
        const allItems = [{
                id: 1,
                name: 'Lays Chilli & Lime',
                category: 'snacks',
                price: 50,
                stock: 100
            },
            {
                id: 2,
                name: 'Tape',
                category: 'stationery',
                price: 25,
                stock: 30
            },
            {
                id: 3,
                name: 'Pepsi Can',
                category: 'drinks',
                price: 60,
                stock: 80
            },
            {
                id: 4,
                name: 'Coca-Cola Bottle',
                category: 'drinks',
                price: 75,
                stock: 50
            },
            {
                id: 5,
                name: 'Blue Pen',
                category: 'stationery',
                price: 10,
                stock: 100
            },
            {
                id: 6,
                name: 'KitKat Chocolate',
                category: 'snacks',
                price: 120,
                stock: 25
            },
            {
                id: 7,
                name: 'Sprite Bottle',
                category: 'drinks',
                price: 70,
                stock: 40
            },
            {
                id: 8,
                name: 'Notebook A4',
                category: 'stationery',
                price: 90,
                stock: 15
            },
        ];

        // ===== STATE =====
        let cart = []; // each item: {id, name, price, stock, qty, subtotal}
        let holds = []; // array of *cart snapshots*
        let currentDiscount = 0;

        // ===== SELECT DOM ELEMENTS =====
        const itemListEl = document.getElementById('itemList');
        const cartTableBody = document.querySelector('#cartTable tbody');
        const cartQtyEl = document.getElementById('cartQty');
        const cartTotalEl = document.getElementById('cartTotal');
        const cartDiscountEl = document.getElementById('cartDiscount');
        const cartGrandTotalEl = document.getElementById('cartGrandTotal');
        const holdCountEl = document.getElementById('holdCount');
        const holdListContainer = document.getElementById('holdListContainer');

        // ===== INITIAL RENDER =====
        renderItemList(allItems);

        // ===== RENDER FUNCTIONS =====

        function renderItemList(items) {
            itemListEl.innerHTML = '';
            items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'item-box';
                div.innerHTML = `
      <div class="price">PKR ${item.price}</div>
      <div class="name">${item.name}</div>
      <small class="text-muted">${item.stock} in stock</small>
    `;
                // Add click event
                div.addEventListener('click', () => addItemToCart(item));
                itemListEl.appendChild(div);
            });
        }

        function renderCart() {
            cartTableBody.innerHTML = '';
            let totalQty = 0;
            let totalAmount = 0;

            cart.forEach((cItem, idx) => {
                totalQty += cItem.qty;
                totalAmount += cItem.subtotal;
                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td>${cItem.name}</td>
      <td>${cItem.stock}</td>
      <td>
        <input type="number" class="form-control form-control-sm cart-qty-input"
               value="${cItem.qty}" min="1" style="max-width:70px;">
      </td>
      <td>PKR ${cItem.price.toFixed(2)}</td>
      <td>PKR ${cItem.subtotal.toFixed(2)}</td>
      <td class="text-center">
        <button><i class="fas fa-times"></i></button>
      </td>
    `;
                // Remove item
                tr.querySelector('button').addEventListener('click', () => {
                    removeCartItem(idx);
                });
                // Update qty
                tr.querySelector('.cart-qty-input').addEventListener('change', (e) => {
                    const newQty = parseInt(e.target.value) || 1;
                    updateCartItemQty(idx, newQty);
                });
                cartTableBody.appendChild(tr);
            });

            // Display totals
            cartQtyEl.textContent = totalQty;
            cartTotalEl.textContent = 'PKR ' + totalAmount.toFixed(2);

            // Apply discount
            cartDiscountEl.textContent = 'PKR ' + currentDiscount.toFixed(2);
            let grand = totalAmount - currentDiscount;
            if (grand < 0) grand = 0;
            cartGrandTotalEl.textContent = 'PKR ' + grand.toFixed(2);
        }

        function renderHoldList() {
            holdListContainer.innerHTML = '';
            if (holds.length === 0) {
                holdListContainer.innerHTML = '<p class="text-muted">No holds yet.</p>';
                return;
            }
            // Build a nice list
            holds.forEach((hCart, i) => {
                const itemCount = hCart.reduce((sum, it) => sum + it.qty, 0);
                const totalVal = hCart.reduce((sum, it) => sum + it.subtotal, 0);
                const div = document.createElement('div');
                div.className = 'border rounded p-2 mb-2';
                div.innerHTML = `
      <strong>Hold #${i+1}</strong> | Items: ${itemCount}, Total: PKR ${totalVal}
      <br><button class="btn btn-sm btn-outline-primary mt-1">Restore</button>
      <button class="btn btn-sm btn-outline-danger mt-1 ms-2">Delete</button>
    `;
                // On "Restore"
                div.querySelector('.btn-outline-primary').addEventListener('click', () => {
                    // restore cart
                    cart = JSON.parse(JSON.stringify(hCart));
                    holds.splice(i, 1);
                    currentDiscount = 0; // reset discount for simplicity
                    renderCart();
                    renderHoldList();
                    updateHoldBadge();
                    // close modal
                    let holdModal = bootstrap.Modal.getInstance(document.getElementById('holdListModal'));
                    holdModal.hide();
                });
                // On "Delete"
                div.querySelector('.btn-outline-danger').addEventListener('click', () => {
                    holds.splice(i, 1);
                    renderHoldList();
                    updateHoldBadge();
                });

                holdListContainer.appendChild(div);
            });
        }

        // ===== CART LOGIC =====
        function addItemToCart(item) {
            const existingIndex = cart.findIndex(ci => ci.id === item.id);
            if (existingIndex >= 0) {
                cart[existingIndex].qty += 1;
                cart[existingIndex].subtotal = cart[existingIndex].price * cart[existingIndex].qty;
            } else {
                cart.push({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    stock: item.stock,
                    qty: 1,
                    subtotal: item.price
                });
            }
            renderCart();
        }

        function removeCartItem(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function updateCartItemQty(index, newQty) {
            cart[index].qty = newQty;
            cart[index].subtotal = cart[index].price * newQty;
            renderCart();
        }

        // ===== FILTER / SEARCH (Right Panel) =====
        const categorySelect = document.getElementById('categorySelect');
        const itemFilterInput = document.getElementById('itemFilterInput');
        const filterResetBtn = document.getElementById('filterResetBtn');

        categorySelect.addEventListener('change', doFilter);
        itemFilterInput.addEventListener('input', doFilter);
        filterResetBtn.addEventListener('click', () => {
            categorySelect.value = '';
            itemFilterInput.value = '';
            doFilter();
        });

        function doFilter() {
            const catVal = categorySelect.value.trim().toLowerCase();
            const txtVal = itemFilterInput.value.trim().toLowerCase();
            const filtered = allItems.filter(it => {
                const inCategory = catVal ? it.category === catVal : true;
                const inText = txtVal ? it.name.toLowerCase().includes(txtVal) : true;
                return inCategory && inText;
            });
            renderItemList(filtered);
        }

        // ===== BARCODE/SEARCH (Left Panel) =====
        const itemSearchInput = document.getElementById('itemSearchInput');
        itemSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchLeftPanel();
            }
        });

        function searchLeftPanel() {
            const text = itemSearchInput.value.trim().toLowerCase();
            if (!text) return;
            const found = allItems.find(it => it.name.toLowerCase().includes(text));
            if (found) addItemToCart(found);
            itemSearchInput.value = '';
        }

        // ===== DISCOUNT =====
        const discountEditBtn = document.getElementById('discountEditBtn');
        discountEditBtn.addEventListener('click', () => {
            const val = prompt('Enter discount amount (PKR):', '0');
            currentDiscount = parseFloat(val) || 0;
            renderCart();
        });

        // ===== HOLD, MULTIPLE, CASH =====
        document.getElementById('btnHold').addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Cart is empty, nothing to hold.');
                return;
            }
            // push a copy
            const cartCopy = JSON.parse(JSON.stringify(cart));
            holds.push(cartCopy);
            cart = [];
            currentDiscount = 0;
            renderCart();
            updateHoldBadge();
            alert('Current sale placed on hold.');
        });

        document.getElementById('btnMultiple').addEventListener('click', () => {
            alert('“Multiple” clicked. (Dummy) Here you might handle partial payments, etc.');
        });

        document.getElementById('btnCash').addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Cart is empty, nothing to pay.');
                return;
            }
            const grandTotal = cart.reduce((sum, it) => sum + it.subtotal, 0) - currentDiscount;
            alert('Sale completed for PKR ' + grandTotal);
            cart = [];
            currentDiscount = 0;
            renderCart();
        });

        // ===== HOLD LIST LINK & MODAL =====
        const holdListLink = document.getElementById('holdListLink');
        holdListLink.addEventListener('click', () => {
            // Populate hold list
            renderHoldList();
            // Show modal
            const holdModal = new bootstrap.Modal(document.getElementById('holdListModal'));
            holdModal.show();
        });

        function updateHoldBadge() {
            holdCountEl.textContent = holds.length;
        }
    </script>
</body>

</html>
