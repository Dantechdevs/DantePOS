$(function () {
    // Add event listeners for quantity updates
    $(document).on('keyup click change', '.quantity, .unitPrice, .quantity-increase, .quantity-decrease', function () {
        const row = $(this).closest("tr");

        // Ensure the row quantity and unit price are updated
        const quantityInput = row.find(".quantity");
        const unitPriceInput = row.find(".unitPrice"); // Corrected class name

        let currentQuantity = parseInt(quantityInput.val()) || 0;

        // If quantity is manually set to 0, update it to 1
        if (currentQuantity === 0) {
            currentQuantity = 1;
        }

        if ($(this).hasClass("quantity-increase")) {
            currentQuantity += 1;
        } else if ($(this).hasClass("quantity-decrease") && currentQuantity > 1) {
            currentQuantity -= 1;
        }

        quantityInput.val(currentQuantity);

        // Update row total (quantity * unit price)
        updateRowTotal(row);

        const productID = row.find("input.productID").val();

        // Perform stock check
        const totalQuantity = calculateTotalQuantity(productID);
        checkProductStock(totalQuantity, productID, row);
    });



    // Add event listener for "Other Charges" and "Discount on All"
    $(document).on('keyup change', '#otherCharges, #globalDiscount, #discountType', function () {
        updateAllTotals(); // Call the calculation function on any change
    });

    // Function to check product stock
    function checkProductStock(quantity, productID, row) {
        $.ajax({
            url: "/check-product-stock/", // Replace with your actual API endpoint
            type: 'GET',
            data: {
                quantity: quantity,
                productID: productID
            },
            success: function (response) {
                console.log(response);
                if (response.error === true) {
                    // Display notification for out-of-stock
                    $.notify(response.message, {
                        globalPosition: 'top right',
                        className: 'error'
                    });

                    // Set quantity to the available stock
                    const availableStock = response.availableQuantity || 0;
                    if (row) {
                        row.find(".quantity").val(availableStock > 0 ? availableStock : 1);
                        updateRowTotal(row);
                    }
                }

                // Call calculation functions
                updateAllTotals();
            }
        });
    }

    // Function to calculate total quantity of a product across all rows
    function calculateTotalQuantity(productID) {
        let totalQuantity = 0;

        $("#saleItems tr").each(function () {
            const rowProductID = $(this).find("input.productID").val();
            if (rowProductID === productID) {
                const rowQuantity = parseFloat($(this).find(".quantity").val()) || 0;
                totalQuantity += rowQuantity;
            }
        });

        return totalQuantity;
    }

    // Search Items with Autocomplete
    // Search Items with Autocomplete
    $("#searchItem").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/search-product",
                type: "GET",
                data: { term: request.term },
                success: function (data) {
                    if (data.length === 1) {
                        // If only one item is found, directly add it to the table
                        addItemToTable(data[0]);
                        $("#searchItem").val(""); // Clear the search input
                    } else {
                        // Show autocomplete suggestions if multiple results are found
                        response(
                            $.map(data, function (item) {
                                return {
                                    label: item.productName,
                                    value: item.productName,
                                    data: item
                                };
                            })
                        );
                    }
                },
                error: function (xhr) {
                    // Handle 404 or custom error response
                    if (xhr.status === 404) {
                        const message = xhr.responseJSON?.message || "No products found.";
                        showWarningToast(message); // Show toaster with custom message
                    } else {
                        console.error("Error fetching search results.");
                    }
                }
            });
        },
        minLength: 1,
        select: function (event, ui) {
            addItemToTable(ui.item.data); // Add the selected item to the table
            $("#searchItem").val(""); // Clear the search input
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        // Customize the autocomplete dropdown UI
        return $("<li>")
            .append(
                `<div style="display: flex; align-items: center;">
                    <span style="width: 100px; color: #ff0000;">[${item.data.code}]</span>
                    <span style="width: 50px; color: green; margin-left:5px;">Qty: ${item.data.productQty}</span>
                    <span>${item.data.productName}</span>
                </div>`
            )
            .appendTo(ul);
    };






    // Function to Add Item to Table
    function addItemToTable(item) {
        console.log("item :" + item.productQty)
        if (item.productQty < 1) {
            showWarningToast(`${item.productName} has only ${item.productQty} in stock`);
            return false;
        }
        // Check if the item already exists in the table
        let existingRow = null;
        $("#saleItems tr").each(function () {
            const itemID = $(this).find("input.productID").val();
            if (parseInt(itemID) === parseInt(item.productID)) {
                existingRow = $(this);
            }
        });

        if (existingRow) {
            // If the item exists, increase its quantity
            const quantityInput = existingRow.find(".quantity");
            const currentQuantity = parseInt(quantityInput.val()) || 0;
            quantityInput.val(currentQuantity + 1);

            const productID = existingRow.find(".productID").val();
            const totalQuantity = calculateTotalQuantity(productID);

            // Perform stock check for the updated quantity
            checkProductStock(totalQuantity, productID, existingRow);
            // Update the total for the row
            updateRowTotal(existingRow);
        } else {
            // If the item doesn't exist, add a new row
            const tableRow = `
                <tr>
                    <td>
                        <input type="text" class="form-control-plaintext text-left" name="productName[]" value="${item.productName}" readonly>
                        <input type="hidden" class="productID" name="product_id[]" value="${item.productID}">
                        <input type="hidden" class="productQty" name="productOldQty[]" value="${item.productQty}">
                        <input type="hidden" class="cost" name="cost[]" value="${item.cost}">
                        <input type="hidden" class="calculatedCost" name="calculatedCost[]" value="${item.cost}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger quantity-decrease">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="text" name="quantity[]" class="form-control form-control-sm quantity text-center d-inline-block" value="1" style="width: 50px;">
                        <button type="button" class="btn btn-sm btn-success quantity-increase">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm text-center" name="unit[]" value="${item.productUnit}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm unit-price text-right unitPrice" name="selling_price[]" value="${item.sellingPrice}">
                    </td>
                    <td>
                        <input type="text" class="form-control-plaintext total-amount text-right" name="amount[]" value="${item.sellingPrice}" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            $("#saleItems").append(tableRow);

            // Perform stock check for the newly added item
            const totalQuantity = calculateTotalQuantity(item.productID);
            checkProductStock(totalQuantity, item.productID, $("#saleItems tr:last"));
            // Call updateRowTotal for the new row
            const newRow = $("#saleItems tr:last");
            updateRowTotal(newRow);
        }

        updateAllTotals(); // Update totals after adding or updating the row
    }

    // Update row total
    function updateRowTotal(row) {
        const quantity = parseFloat(row.find(".quantity").val()) || 0;
        const unitPrice = parseFloat(row.find(".unitPrice").val()) || 0;
        const cost = parseFloat(row.find(".cost").val()) || 0; // Get cost from hidden input
        const itemTotal = quantity * unitPrice;
        const calculatedCost = quantity * cost; // Calculate cost based on quantity

        row.find(".total-amount").val(itemTotal.toFixed(2));
        row.find(".calculatedCost").val(calculatedCost.toFixed(2)); // Update calculated cost
        updateAllTotals();
    }

    // Update totals dynamically
    function updateAllTotals() {
        let subtotal = 0;
        $(".total-amount").each(function () {
            subtotal += parseFloat($(this).val()) || 0;
        });

        $("#subtotal").text(subtotal.toFixed(2));
        const otherCharges = parseFloat($("#otherCharges").val()) || 0;
        const discountValue = parseFloat($("#globalDiscount").val()) || 0;
        const discountType = $("#discountType").val();

        let discount = 0;
        if (discountType === "percentage") {
            discount = ((parseInt(subtotal) + parseInt(otherCharges)) * discountValue) / 100;
        } else {
            discount = discountValue;
        }

        const grandTotal = subtotal + otherCharges - discount;
        $("#grandTotal").text(grandTotal.toFixed(2));

        // Update UI
        $("#subtotal").text(subtotal.toFixed(2));
        $("#subtotalVal").val(subtotal.toFixed(2));
        $("#otherChargesTotal").text(otherCharges.toFixed(2));
        $("#otherChargesTotalVal").val(otherCharges.toFixed(2));
        $("#discountTotal").text(discount.toFixed(2));
        $("#discountVal").val(discount.toFixed(2));
        $("#grandTotal").text(grandTotal.toFixed(2));
        $("#grandTotalVal").val(grandTotal.toFixed(2));
    }

    // Remove item
    $(document).on("click", ".remove-item", function () {
        $(this).closest("tr").remove();
        updateAllTotals();
    });
});
