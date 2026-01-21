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
        body {
            background-color: #f5f7fa;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        /* Top Nav */
        .navbar-custom {
            background: linear-gradient(90deg, #00c851 0%, #6f42c1 100%);
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        /* Main Container */
        .pos-container {
            padding: 1rem;
        }

        .main-row {
            /* Ensure the row tries to fill the viewport minus nav, so we can do scroll inside columns */
            /* min-height: calc(100vh - 70px); 70px for top navbar, adjust as needed */
            display: flex;
            /* to keep columns aligned */
            gap: 1rem;
            max-height: 700px;
            overflow: hidden;
        }

        /* Left Panel: Cart/Invoice */
        .left-panel {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 1rem;
            flex: 1;
            /* take as much space as needed, but let's fix a scroll container inside */
            display: flex;
            flex-direction: column;
        }

        .left-panel h5 {
            font-weight: 600;
        }

        /* Cart scroll area */
        .cart-table-wrapper {
            margin-bottom: 0.5rem;
            flex: 1;
            /* So it expands to fill leftover space */
            overflow-y: auto;
            /* Scroll if content is tall */
        }

        /* If you want a specific height instead of flexible leftover, do:
       max-height: calc(100vh - 300px);
       overflow-y: auto;
    */

        .cart-table thead {
            background-color: #6f42c1;
            color: #fff;
        }

        .cart-table td button {
            border: none;
            background: none;
            color: #dc3545;
            cursor: pointer;
        }

        /* Action Buttons (Hold, Multiple, Pay) */
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

        .btn-pay {
            background: #00c851;
            color: #fff;
        }

        /* Right Panel: Items */
        .right-panel {
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 1rem;
            width: 300px;
            /* fixed width or you can do flex, your choice */
            display: flex;
            flex-direction: column;
        }

        .items-list-scroll {
            /* Force scrolling if item boxes exceed container height */
            flex: 1;
            overflow-y: auto;
            margin-top: 0.5rem;
        }

        /* Single item box */
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

        /* Payment Modal - card details section */
        .card-details {
            display: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1rem;
            background: #fafafa;
        }

        .card-details.active {
            display: block;
        }

        /* Hold List Items */
        .hold-item {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background: #eef7ff;
            cursor: pointer;
        }

        .hold-item:hover {
            background: #e1f0ff;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
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
                    <a class="nav-link" href="#" id="holdListLink">
                        <i class="fas fa-list"></i> Hold List
                        <span class="badge bg-danger" id="holdCount">0</span>
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
        <div class="main-row">
            <!-- LEFT PANEL: CART/INVOICE -->
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
                                <!-- or Price inc.Tax / Tax, if you prefer separate columns -->
                                <th>Subtotal</th>
                                <th style="width:40px;">X</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamically added rows go here -->
                        </tbody>
                    </table>
                </div>

                <!-- Send SMS Checkbox -->
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="sendSmsCheck">
                    <label class="form-check-label" for="sendSmsCheck">
                        Send SMS to Customer
                        <i class="fas fa-info-circle text-info" title="SMS with invoice details"></i>
                    </label>
                </div>

                <!-- Totals / Buttons -->
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
                        <div>Discount
                            <i class="fa-solid fa-pen text-primary" style="cursor:pointer;" id="discountEditBtn"></i>
                        </div>
                        <div class="h5 mb-0" id="cartDiscount">PKR 0.00</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div>Grand Total</div>
                        <div class="h5 mb-0" id="cartGrandTotal">PKR 0.00</div>
                    </div>
                </div>

                <div class="pos-actions text-center mt-3">
                    <button class="btn btn-hold" id="btnHold"><i class="fas fa-hand-paper"></i> Hold</button>
                    <button class="btn btn-multiple" id="btnMultiple"><i class="fas fa-th-large"></i> Multiple</button>
                    <button class="btn btn-pay" id="btnPay"><i class="fas fa-money-bill-wave"></i> Pay</button>
                </div>
            </div>

            <!-- RIGHT PANEL: ITEMS -->
            <div class="right-panel">
                <h6>All Items</h6>
                <div class="row g-2 mb-2 mt-1">
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

                <div class="items-list-scroll" id="itemList">
                    <!-- Dynamically created item boxes -->
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
                <div class="modal-body" id="holdListContainer">
                    <!-- Filled by JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Total Due: </strong><span id="paymentTotalDue">PKR 0.00</span></p>
                    <div class="payment-methods">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payMethod" id="payCash"
                                value="cash" checked>
                            <label class="form-check-label" for="payCash">Cash</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payMethod" id="payCard"
                                value="card">
                            <label class="form-check-label" for="payCard">Card</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payMethod" id="payOnline"
                                value="online">
                            <label class="form-check-label" for="payOnline">Online</label>
                        </div>
                    </div>

                    <!-- Card Details Section -->
                    <div class="card-details mt-3" id="cardDetails">
                        <h6>Enter Card Details</h6>
                        <div class="mb-2">
                            <label class="form-label">Card Holder Name</label>
                            <input type="text" class="form-control" placeholder="John Doe">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="xxxx xxxx xxxx xxxx">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Expiry</label>
                                <input type="text" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="col-6">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" placeholder="123">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnConfirmPayment">Confirm Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS: Bootstrap + Script -->
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
        let cart = [];
        let holds = [];
        let currentDiscount = 0;

        // DOM references
        const itemListEl = document.getElementById('itemList');
        const cartTableBody = document.querySelector('#cartTable tbody');
        const cartQtyEl = document.getElementById('cartQty');
        const cartTotalEl = document.getElementById('cartTotal');
        const cartDiscountEl = document.getElementById('cartDiscount');
        const cartGrandTotalEl = document.getElementById('cartGrandTotal');
        const holdCountEl = document.getElementById('holdCount');
        const holdListContainer = document.getElementById('holdListContainer');
        const paymentTotalDueEl = document.getElementById('paymentTotalDue');
        const cardDetailsEl = document.getElementById('cardDetails');

        /* ===================================================
           Initialization
        =================================================== */
        renderItemList(allItems);
        renderCart();

        /* ===================================================
           Render Functions
        =================================================== */
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
                tr.querySelector('button').addEventListener('click', () => removeCartItem(idx));
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
            holds.forEach((hCart, i) => {
                const itemCount = hCart.reduce((sum, it) => sum + it.qty, 0);
                const totalVal = hCart.reduce((sum, it) => sum + it.subtotal, 0);
                const div = document.createElement('div');
                div.className = 'hold-item';
                div.innerHTML = `
      <strong>Hold #${i+1}</strong> | Items: ${itemCount}, Total: PKR ${totalVal}
      <div class="mt-1">
        <button class="btn btn-sm btn-outline-primary">Restore</button>
        <button class="btn btn-sm btn-outline-danger ms-2">Delete</button>
      </div>
    `;
                // Restore
                div.querySelector('.btn-outline-primary').addEventListener('click', () => {
                    cart = JSON.parse(JSON.stringify(hCart));
                    holds.splice(i, 1);
                    currentDiscount = 0;
                    renderCart();
                    renderHoldList();
                    updateHoldBadge();
                    let modal = bootstrap.Modal.getInstance(document.getElementById('holdListModal'));
                    modal.hide();
                });
                // Delete
                div.querySelector('.btn-outline-danger').addEventListener('click', () => {
                    holds.splice(i, 1);
                    renderHoldList();
                    updateHoldBadge();
                });
                holdListContainer.appendChild(div);
            });
        }

        /* ===================================================
           Cart Logic
        =================================================== */
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

        /* ===================================================
           Filter / Search (Right Panel)
        =================================================== */
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

        /* ===================================================
           Left Search / Barcode
        =================================================== */
        const itemSearchInput = document.getElementById('itemSearchInput');
        itemSearchInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                searchLeftInput();
            }
        });

        function searchLeftInput() {
            const text = itemSearchInput.value.trim().toLowerCase();
            if (!text) return;
            const found = allItems.find(it => it.name.toLowerCase().includes(text));
            if (found) addItemToCart(found);
            itemSearchInput.value = '';
        }

        /* ===================================================
           Discount
        =================================================== */
        document.getElementById('discountEditBtn').addEventListener('click', () => {
            const val = prompt('Enter discount amount (PKR):', '0');
            currentDiscount = parseFloat(val) || 0;
            renderCart();
        });

        /* ===================================================
           Hold, Multiple, Pay
        =================================================== */
        document.getElementById('btnHold').addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Cart is empty, nothing to hold.');
                return;
            }
            holds.push(JSON.parse(JSON.stringify(cart)));
            cart = [];
            currentDiscount = 0;
            renderCart();
            updateHoldBadge();
            alert('Current sale placed on hold.');
        });

        document.getElementById('btnMultiple').addEventListener('click', () => {
            alert('Multiple payment method flow (dummy).');
        });

        document.getElementById('btnPay').addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Cart is empty, nothing to pay.');
                return;
            }
            // Show Payment Modal
            let grand = cart.reduce((sum, it) => sum + it.subtotal, 0) - currentDiscount;
            if (grand < 0) grand = 0;
            paymentTotalDueEl.textContent = 'PKR ' + grand.toFixed(2);

            // Reset payment method to Cash
            document.getElementById('payCash').checked = true;
            cardDetailsEl.classList.remove('active');

            const payModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            payModal.show();
        });

        /* ===================================================
           Payment Modal Logic
        =================================================== */
        document.getElementById('payCash').addEventListener('click', hideCardForm);
        document.getElementById('payCard').addEventListener('click', showCardForm);
        document.getElementById('payOnline').addEventListener('click', hideCardForm);

        function showCardForm() {
            cardDetailsEl.classList.add('active');
        }

        function hideCardForm() {
            cardDetailsEl.classList.remove('active');
        }

        document.getElementById('btnConfirmPayment').addEventListener('click', () => {
            // In real system, you'd process payment
            alert('Payment successful! Sale completed.');
            cart = [];
            currentDiscount = 0;
            renderCart();
            // Hide modal
            let payModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            payModal.hide();
        });

        /* ===================================================
           Hold List Modal
        =================================================== */
        document.getElementById('holdListLink').addEventListener('click', () => {
            renderHoldList();
            let modal = new bootstrap.Modal(document.getElementById('holdListModal'));
            modal.show();
        });

        function updateHoldBadge() {
            holdCountEl.textContent = holds.length;
        }
    </script>
</body>

</html>
