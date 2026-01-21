// purchase_utilities.js


// export function initializeAutocompleteForSearch(selector) {
//     $(selector).autocomplete({
//         source: function (request, response) {
//             $.ajax({
//                 url: "/search-raw-product",
//                 type: "GET",
//                 data: { term: request.term },
//                 success: function (data) {
//                     if (data.length === 1) {
//                         addItemToTable(data[0]);
//                         $(selector).val("");
//                     } else {
//                         response(
//                             $.map(data, function (item) {
//                                 return {
//                                     label: item.productName,
//                                     value: item.productName,
//                                     data: item,
//                                 };
//                             })
//                         );
//                     }
//                 },
//                 error: function (xhr) {
//                     if (xhr.status === 404) {
//                         const message = xhr.responseJSON?.message || "No products found.";
//                         showWarningToast(message);
//                     } else {
//                         console.error("Error fetching search results.");
//                     }
//                 },
//             });
//         },
//         minLength: 1,
//         select: function (event, ui) {
//             addItemToTable(ui.item.data);
//             $(selector).val("");
//             return false;
//         },
//     }).autocomplete("instance")._renderItem = function (ul, item) {
//         return $("<li>")
//             .append(
//                 `<div style="display: flex; align-items: center;">
//                     <span style="width: 100px; color: #ff0000;">[${item.data.code}]</span>
//                     <span style="width: 50px; color: green; margin-left:5px;">Qty: ${item.data.productQty}</span>
//                     <span>${item.data.productName}</span>
//                 </div>`
//             )
//             .appendTo(ul);
//     };
// }

export function initializeAutocompleteForSearch(selector) {
    let currentCurrency = 'USD';
    $(selector).autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/search-raw-product",
                type: "GET",
                data: { term: request.term },
                success: function (data) {
                    currentCurrency = data.currency || '$';
                    // Handle array response (successful with results)
                    if (data.results && data.results.length > 0) {
                        if (data.results.length === 1) {
                            addItemToTable(data.results[0]);
                            $(selector).val("");
                        } else {
                            response(
                                $.map(data.results, function (item) {
                                    return {
                                        label: item.productName,
                                        value: item.productName,
                                        data: item,
                                    };
                                })
                            );
                        }
                    }
                    // Handle object response (no results)
                    else if (data.results) {
                        showWarningToast(data.message);
                        response([]);
                    }
                },
                error: function (xhr) {
                    console.error("Error fetching search results:", xhr.statusText);
                    showWarningToast("Error connecting to server. Please try again.");
                },
            });
        },
        minLength: 1,
        select: function (event, ui) {
            addItemToTable(ui.item.data);
            $(selector).val("");
            return false;
        },
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append(
                `<div style="display: flex; align-items: center; padding: 8px 12px;">
                    <span style="min-width: 80px; color: #007bff; font-weight: 500;">[${item.data.code}]</span>
                    <span style="min-width: 100px; margin: 0 15px;">
                        <span style="color: #28a745;">Stock:</span>
                        ${item.data.productQty}
                    </span>
                    <span style="flex-grow: 1;">${item.data.productName}</span>
                    <span style="color: #6c757d; margin-left: 15px;">${currentCurrency} ${item.data.purchasePrice}</span>
                </div>`
            )
            .appendTo(ul);
    };
}

export function addItemToTable(item) {
    let existingRow = null;
    $("#purchaseItems tr").each(function () {
        const itemID = $(this).find("input.productID").val();
        if (parseInt(itemID) === parseInt(item.productID)) {
            existingRow = $(this);
        }
    });

    if (existingRow) {
        const quantityInput = existingRow.find(".quantity");
        const currentQuantity = parseInt(quantityInput.val()) || 0;
        quantityInput.val(currentQuantity + 1);
        updateRowTotal(existingRow);
    } else {
        // Create unit dropdown options
        let unitOptions = '';
        let defaultUnitPrice = item.purchasePrice; // Default to the purchase price

        // Check if unitInfo exists and is an array
        if (item.unitInfo && Array.isArray(item.unitInfo)) {
            item.unitInfo.forEach(unit => {
                const isSelected = unit.is_default ? 'selected' : '';
                if (isSelected) {
                    defaultUnitPrice = unit.purchase_price; // Use the default unit's price
                }
                unitOptions += `
                    <option value="${unit.unit_id}"
                            data-purchase-price="${unit.purchase_price}"
                            data-selling-price="${unit.selling_price}"
                            ${isSelected}>
                        ${unit.unit} (${unit.purchase_price}/${unit.selling_price})
                    </option>`;
            });
        }

        const tableRow = `
            <tr>
                <td>
                    <input type="text" class="form-control-plaintext text-left" name="productName[]" value="${item.productName}" readonly>
                    <input type="hidden" class="productID" name="product_id[]" value="${item.productID}">
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
                    <select class="form-control form-control-sm unit-select" name="unit[]">
                        ${unitOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm unit-price text-right unitPrice"
                           name="price[]" value="${defaultUnitPrice}">
                </td>
                <td>
                    <input type="text" class="form-control-plaintext total-amount text-right"
                           name="amount[]" value="${defaultUnitPrice}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

        $("#purchaseItems").append(tableRow);

        // Add event handler for unit selection change
        $("#purchaseItems tr:last .unit-select").on('change',function() {
            const selectedOption = $(this).find('option:selected');
            const purchasePrice = selectedOption.data('purchase-price');
            const sellingPrice = selectedOption.data('selling-price');

            // Update the price input (using purchase price by default)
            $(this).closest('tr').find('.unit-price').val(purchasePrice);

            // Update the total
            updateRowTotal($(this).closest('tr'));
        });

        const newRow = $("#purchaseItems tr:last");
        updateRowTotal(newRow);
    }

    updateAllTotals();
}

export function addItemToTablexxx(item) {


    let existingRow = null;
    $("#purchaseItems tr").each(function () {
        const itemID = $(this).find("input.productID").val();
        if (parseInt(itemID) === parseInt(item.productID)) {
            existingRow = $(this);
        }
    });

    if (existingRow) {
        const quantityInput = existingRow.find(".quantity");
        const currentQuantity = parseInt(quantityInput.val()) || 0;
        quantityInput.val(currentQuantity + 1);
        // Update the total for the row
        updateRowTotal(existingRow);
    } else {
        const tableRow = `
            <tr>
                <td>
                    <input type="text" class="form-control-plaintext text-left" name="productName[]" value="${item.productName}" readonly>
                    <input type="hidden" class="productID" name="product_id[]" value="${item.productID}">
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
                    <input type="number" class="form-control form-control-sm unit-price text-right unitPrice" name="price[]" value="${item.purchasePrice}">
                </td>
                <td>
                    <input type="text" class="form-control-plaintext total-amount text-right" name="amount[]" value="${item.purchasePrice}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $("#purchaseItems").append(tableRow);
        // Call updateRowTotal for the new row
        const newRow = $("#purchaseItems tr:last");
        updateRowTotal(newRow);

    }

    updateAllTotals();
}

// Update row total
export function updateRowTotal(row) {
    const quantity = parseFloat(row.find(".quantity").val()) || 0;
    const unitPrice = parseFloat(row.find(".unitPrice").val()) || 0;
    const cost = parseFloat(row.find(".cost").val()) || 0; // Get cost from hidden input
    const itemTotal = quantity * unitPrice;
    const calculatedCost = quantity * cost; // Calculate cost based on quantity

    row.find(".total-amount").val(itemTotal.toFixed(2));
    row.find(".calculatedCost").val(calculatedCost.toFixed(2)); // Update calculated cost
    updateAllTotals();
}


export function updateAllTotals() {
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

