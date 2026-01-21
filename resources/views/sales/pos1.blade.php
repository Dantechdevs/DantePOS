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
        /* ========== COLOR SCHEME & GLOBALS ========== */
        body {
            background-color: #f5f7fa;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #20c997 0%, #6f42c1 100%);
        }

        .navbar-brand,
        .navbar-custom .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        /* ========== LAYOUT ========== */
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

        /* ========== CART TABLE ========== */
        .cart-table thead {
            background-color: #6f42c1;
            /* Same as second color in gradient */
            color: #fff;
        }

        .cart-table td button {
            border: none;
            background: none;
            color: #dc3545;
            cursor: pointer;
        }

        /* ========== ACTIONS AT BOTTOM OF CART ========== */
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
            background: #20c997;
            color: #fff;
        }

        /* ========== ITEM LIST (RIGHT PANEL) ========== */
        .item-list .item-box {
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
            color: #20c997;
        }

        .item-box .name {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* ========== HOLD LIST ========== */
        .hold-list-container {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 0.5rem;
        }

        .hold-item {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background: #e7f1ff;
            cursor: pointer;
        }

        .hold-item:hover {
            background: #d7e9ff;
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
                    <a class="nav-link" href="#">
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

                    <!-- Cart Table -->
                    <div class="table-responsive">
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
                            <div>Discount <i class="fas fa-edit text-primary" style="cursor:pointer;"
                                    title="Click to apply discount" id="discountEditBtn"></i></div>
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

                    <!-- List of Holds (if any) -->
                    <div class="hold-list-container" id="holdListContainer" style="display:none;">
                        <h6 class="mt-3">Hold List</h6>
                        <!-- Dynamically added hold items -->
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
                    <div class="item-list" id="itemList">
                        <!-- Dynamically created item boxes -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery JS -->
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
        let holds = []; // array of cart snapshots
        let currentDiscount = 0; // numeric discount in PKR

        // ===== SELECT DOM ELEMENTS =====
        const itemListEl = document.getElementById('itemList');
        const cartTableBody = document.querySelector('#cartTable tbody');
        const cartQtyEl = document.getElementById('cartQty');
        const cartTotalEl = document.getElementById('cartTotal');
        const cartDiscountEl = document.getElementById('cartDiscount');
        const cartGrandTotalEl = document.getElementById('cartGrandTotal');
        const holdListContainer = document.getElementById('holdListContainer');
        const holdCountEl = document.getElementById('holdCount');

        // ===== INITIAL RENDER =====
        renderItemList(allItems); // show all items on the right panel

        // ===== RENDER FUNCTIONS =====

        function renderItemList(items) {
            // Clear itemListEl
            itemListEl.innerHTML = '';
            items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'item-box';
                div.innerHTML = `
      <div class="price">PKR ${item.price}</div>
      <div class="name">${item.name}</div>
      <small class="text-muted">${item.stock} in stock</small>
    `;
                // On click, add to cart
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
      <td class="text-center"><button><i class="fas fa-times"></i></button></td>
    `;
                // Remove button
                tr.querySelector('button').addEventListener('click', () => {
                    removeCartItem(idx);
                });
                // Qty change
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
            // If no holds, hide
            if (holds.length === 0) {
                holdListContainer.style.display = 'none';
                return;
            }
            holdListContainer.style.display = 'block';
            // Clear old
            holdListContainer.innerHTML = '<h6 class="mt-3">Hold List</h6>';
            holds.forEach((hCart, i) => {
                let totalItems = hCart.reduce((sum, it) => sum + it.qty, 0);
                let sumSubtotal = hCart.reduce((sum, it) => sum + it.subtotal, 0);
                const div = document.createElement('div');
                div.className = 'hold-item';
                div.textContent = `Hold #${i+1} | Items: ${totalItems}, Total: PKR ${sumSubtotal}`;
                // restore cart on click
                div.addEventListener('click', () => {
                    cart = JSON.parse(JSON.stringify(hCart)); // deep clone
                    holds.splice(i, 1);
                    renderCart();
                    renderHoldList();
                    updateHoldBadge();
                });
                holdListContainer.appendChild(div);
            });
        }

        function updateHoldBadge() {
            holdCountEl.textContent = holds.length;
        }

        // ===== CART LOGIC =====
        function addItemToCart(item) {
            // Check if item already in cart
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
                    subtotal: item.price * 1
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

        // ===== FILTER / SEARCH LOGIC =====
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

        // ===== ITEM SEARCH ON LEFT (Barcode input) =====
        const itemSearchInput = document.getElementById('itemSearchInput');
        itemSearchInput.addEventListener('input', () => {
            const text = itemSearchInput.value.trim().toLowerCase();
            if (!text) return;
            // find first match
            const found = allItems.find(it => it.name.toLowerCase().includes(text));
            // if found, add to cart
            if (found) {
                addItemToCart(found);
                itemSearchInput.value = ''; // clear
            }
        });

        // ===== DISCOUNT =====
        const discountEditBtn = document.getElementById('discountEditBtn');
        discountEditBtn.addEventListener('click', () => {
            const val = prompt('Enter discount amount (PKR):', '0');
            currentDiscount = parseFloat(val) || 0;
            renderCart();
        });

        // ===== HOLD, MULTIPLE, CASH =====
        const btnHold = document.getElementById('btnHold');
        btnHold.addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Cart is empty, nothing to hold.');
                return;
            }
            // push a copy of cart to holds
            const cartCopy = JSON.parse(JSON.stringify(cart));
            holds.push(cartCopy);
            // reset current cart
            cart = [];
            currentDiscount = 0;
            renderCart();
            renderHoldList();
            updateHoldBadge();
            alert('Current sale placed on hold.');
        });

        const btnMultiple = document.getElementById('btnMultiple');
        btnMultiple.addEventListener('click', () => {
            alert(
                '“Multiple” clicked. (Dummy) Normally you might handle multiple payments or partial transactions here.');
        });

        const btnCash = document.getElementById('btnCash');
        btnCash.addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Cart is empty, nothing to pay.');
                return;
            }
            // in a real system, you'd finalize sale in DB, print receipt, etc.
            alert('Sale completed for ' + cartGrandTotalEl.textContent);
            cart = [];
            currentDiscount = 0;
            renderCart();
        });
    </script>
</body>

</html>
