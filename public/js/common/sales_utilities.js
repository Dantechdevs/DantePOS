// sales_utilities.js

// Add at the top of the file
let recognition;
let isVoiceEnabled = false;

// Updated voice commands implementation
export function initializeVoiceCommands() {
    // Check if browser supports speech recognition
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        console.warn("Speech recognition not supported in this browser");
        return;
    }

    // Initialize speech recognition
    recognition = new SpeechRecognition();
    recognition.continuous = false; // Changed to false to prevent multiple triggers
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    recognition.maxAlternatives = 1;

    recognition.onresult = function (event) {
        const transcript = event.results[0][0].transcript.trim().toLowerCase();

        // Show visual feedback
        $('#voice-feedback').text(`Heard: ${transcript}`).fadeIn().delay(1000).fadeOut();

        // Process commands
        if (transcript.includes('delete last row')) {
            deleteRowByPosition('last');
        } else if (transcript.includes('delete first row')) {
            deleteRowByPosition('first');
        } else if (transcript.includes('delete row') && transcript.includes('number')) {
            const rowNumberMatch = transcript.match(/number (\d+)/);
            if (rowNumberMatch) {
                deleteRowByNumber(parseInt(rowNumberMatch[1]));
            }
        }
    };

    recognition.onerror = function (event) {
        console.error('Speech recognition error', event.error);
        $('#voice-feedback').text(`Error: ${event.error}`).fadeIn().delay(2000).fadeOut();
    };

    recognition.onend = function () {
        if (isVoiceEnabled) {
            // Restart recognition if still enabled
            setTimeout(() => recognition.start(), 500);
        }
    };

    // Add button to toggle voice commands - prevent form submission
    $(`<button type="button" id="voice-toggle" class="btn btn-info btn-sm">
        <i class="fas fa-microphone"></i> Voice Commands
    </button>`).insertBefore('#searchItem').click(function (e) {
        e.preventDefault(); // Prevent form submission
        toggleVoiceCommands();
    });
}

function toggleVoiceCommands() {
    if (!recognition) {
        showWarningToast("Voice commands not supported in this browser");
        return;
    }

    if (isVoiceEnabled) {
        recognition.stop();
        isVoiceEnabled = false;
        showSuccessToast("Voice commands disabled");
        $('#voice-toggle').removeClass('btn-success').addClass('btn-info');
    } else {
        recognition.start();
        isVoiceEnabled = true;
        showSuccessToast("Voice commands enabled - Say commands like 'delete last row'");
        $('#voice-toggle').removeClass('btn-info').addClass('btn-success');
    }
}

function deleteRowByPosition(position) {
    const rows = $("#saleItems tr");
    if (rows.length === 0) {
        showWarningToast("No rows to delete");
        return;
    }

    let rowToDelete;
    if (position === 'first') {
        rowToDelete = rows.first();
    } else if (position === 'last') {
        rowToDelete = rows.last();
    }

    if (rowToDelete) {
        const productName = rowToDelete.find('.product-name').text() || 'item';
        rowToDelete.remove();
        updateAllTotals();
        showSuccessToast(`Deleted ${position} row (${productName})`);
    }
}

function deleteRowByNumber(number) {
    const rows = $("#saleItems tr");
    if (number > 0 && number <= rows.length) {
        const rowToDelete = rows.eq(number - 1);
        const productName = rowToDelete.find('.product-name').text() || 'item';
        rowToDelete.remove();
        updateAllTotals();
        showSuccessToast(`Deleted row ${number} (${productName})`);
    } else {
        showWarningToast(`Invalid row number ${number}`);
    }
}

// Call this function when the page loads

// initializeVoiceCommands();


export function initializePaymentCalculations() {
    // console.log("Initializing payment calculations...");
    const amountPaidInput = document.getElementById('amountPaid');
    if (!amountPaidInput) return; // Exit if element doesn't exist

    const grandTotalSpan = document.getElementById('grandTotal');
    const grandTotalInput = document.getElementById('grandTotalVal');
    const balanceAmountSpan = document.getElementById('balanceAmount');
    const balanceAmountInput = document.getElementById('balanceAmountValue');
    const changeAmountSpan = document.getElementById('changeAmount');
    const changeAmountInput = document.getElementById('changeAmountValue');

    function updatePaymentCalculations() {
        const paidAmount = parseFloat(amountPaidInput.value) || 0;
        const grandTotal = parseFloat(grandTotalInput.value) || 0;

        if (paidAmount < grandTotal) {
            const balance = grandTotal - paidAmount;
            balanceAmountSpan.textContent = Math.round(balance);
            balanceAmountInput.value = Math.round(balance);
            changeAmountSpan.textContent = '0.00';
            changeAmountInput.value = '0';
        } else {
            const change = paidAmount - grandTotal;
            balanceAmountSpan.textContent = '0.00';
            balanceAmountInput.value = '0';
            changeAmountSpan.textContent = change.toFixed(2);
            changeAmountInput.value = change.toFixed(2);
        }
    }

    amountPaidInput.addEventListener('input', updatePaymentCalculations);
    amountPaidInput.addEventListener('blur', function () {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
            updatePaymentCalculations();
        }
    });

    updatePaymentCalculations();
    window.updatePaymentCalculations = updatePaymentCalculations;
}


export function checkProductStock(quantity, productID, row) {
    $.ajax({
        url: "/check-product-stock/",
        type: "GET",
        data: { quantity, productID },
        success: function (response) {
            if (response.error) {
                $.notify(response.message, {
                    globalPosition: "top right",
                    className: "error",
                });

                const availableStock = response.availableQuantity || 0;
                if (row) {
                    row.find(".quantity").val(availableStock > 0 ? availableStock : 1);
                    updateRowTotal(row);
                }
            }
            updateAllTotals();
        },
    });
}

export function calculateTotalQuantity(productID) {
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

// export function initializeAutocompleteForSearch(selector) {
//     $(selector).autocomplete({
//         source: function (request, response) {
//             $.ajax({
//                 url: "/search-product",
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

            // Get the selected customer type
            const customerType = $('input[name="customerType"]:checked').val();
            console.log("Customer Type: ", customerType);
            $.ajax({
                url: "/search-product",
                type: "GET",
                data: { term: request.term, customerType:customerType },
                success: function (data) {
                    currentCurrency = data.currency || '$';
                    // Handle the standardized response format
                    if (data.results && data.results.length > 0) {
                        // Auto-select if single result
                        if (data.results.length === 1) {
                            addItemToTable(data.results[0]);
                            $(selector).val("");
                        } else {
                            response($.map(data.results, function (item) {
                                return {
                                    label: `${item.productName} (${item.code})`,
                                    value: item.productName,
                                    data: item,
                                };
                            }));
                        }
                    } else {
                        showWarningToast(data.message || "No products found");
                        response([]);
                    }
                },
                error: function (xhr) {
                    console.error("Search error:", xhr.statusText);
                    showWarningToast("Error connecting to server");
                    response([]);
                },
            });
        },
        minLength: 1, // Match backend minimum search length
        select: function (event, ui) {
            addItemToTable(ui.item.data);
            $(selector).val("");
            return false;
        },
        focus: function (event, ui) {
            // Prevent auto-fill of input field
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append(`
                <div class="autocomplete-item">
                    <div class="code">${item.data.code}</div>
                    <div class="details">
                        <div class="name">${item.data.productName}</div>
                        <div class="meta">
                            <span class="stock">Stock: ${item.data.stock}</span>
                            <span class="prices">
                                <span class="cost">Cost: ${currentCurrency} ${item.data.cost}</span>
                                <span class="price">Price: ${currentCurrency} ${item.data.sellingPrice}</span>
                            </span>
                        </div>
                    </div>
                </div>
            `)
            .appendTo(ul);
    };
}

export function addItemToTable_newxxx(item) {
    if (item.productQty < 1) {
        showWarningToast(`${item.productName} has only ${item.productQty} in stock`);
        return false;
    }

    let existingRow = $(`#saleItems tr[data-product-id="${item.productID}"]`);

    if (existingRow.length) {
        // Update existing row
        const quantityInput = existingRow.find(".quantity");
        const currentQuantity = parseInt(quantityInput.val()) || 0;
        const newQuantity = currentQuantity + 1;

        if (newQuantity > item.productQty) {
            showWarningToast(`Cannot add more ${item.productName}. Only ${item.productQty} available.`);
            return false;
        }

        quantityInput.val(newQuantity).trigger('change');
        updateRowTotal(existingRow);

        // Focus and select the quantity input
        quantityInput.focus().select();
    } else {
        // console.log("Adding new row for item: ", item);
        // Create new row
        const tableRow = `
            <tr data-product-id="${item.productID}">
                <td class="align-middle">
                    <div class="d-flex flex-column">
                        <span class="product-name">${item.productName}</span>
                        <input type="hidden" class="productID" name="product_id[]" value="${item.productID}">
                        <input type="hidden" class="productQty" name="productOldQty[]" value="${item.productQty}">
                        <input type="hidden" class="cost" name="cost[]" value="${item.cost}">
                        <input type="hidden" class="calculatedCost" name="calculatedCost[]" value="${item.cost}">
                    </div>
                </td>
                <td class="align-middle text-center">
                    <span class="stock-amount">${item.productQty}</span>
                </td>
                <td class="align-middle">
                    <div class="d-flex justify-content-center quantity-controls">
                        <button type="button" class="btn btn-xs btn-outline-danger quantity-decrease">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" name="quantity[]"
                               class="form-control form-control-sm quantity text-center mx-1"
                               value="1" min="1" max="${item.productQty}"
                               style="width: 50px;">
                        <button type="button" class="btn btn-xs btn-outline-success quantity-increase">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td class="align-middle text-center">
                    <span class="unit-name">${item.productUnit}</span>
                </td>
                <td class="align-middle">
                    <input type="number" class="form-control form-control-sm unit-price text-right"
                           name="selling_price[]" value="${item.sellingPrice}">
                </td>
                <td class="align-middle text-right">
                    <span class="total-amount">${item.sellingPrice}</span>
                    <input type="hidden" name="amount[]" value="${item.sellingPrice}">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-xs btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;

        $("#saleItems").append(tableRow);
        const newRow = $("#saleItems tr:last");
        updateRowTotal(newRow);

        // Focus and select the quantity input in new row
        newRow.find('.quantity').trigger('focus')[0].select();
    }

    updateAllTotals();

    // Add keyboard shortcut (F2) to focus search bar
    $(document).off('keydown.focusSearch').on('keydown.focusSearch', function (e) {
        if (e.key === 'F2') {
            e.preventDefault();
            $("#searchItem").trigger('focus')[0].select();
        }
    });

    // Also allow Ctrl+F to focus search (common convention)
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            const search = document.getElementById("searchItem");
            if (search) {
                search.focus();
                search.select();
            }
            return false;
        }
    }, true);
}

// Initialize search focus on page load
// $(document).ready(function() {
//     $("#searchItem").focus();
// });



export function addItemToTable(item) {
    // console.log("Adding: ", item);
    // if (item.productQty < 1) {
    //     showWarningToast(`${item.productName} has only ${item.productQty} in stock`);
    //     return false;
    // }


    let existingRow = null;
    $("#saleItems tr").each(function () {
        const itemID = $(this).find("input.productID").val();
        if (parseInt(itemID) === parseInt(item.productID)) {
            existingRow = $(this);
        }
    });

    if (existingRow) {
        const quantityInput = existingRow.find(".quantity");
        const currentQuantity = parseInt(quantityInput.val()) || 0;
        quantityInput.val(currentQuantity + 1);

        const productID = existingRow.find(".productID").val();
        const totalQuantity = calculateTotalQuantity(productID);
        // checkProductStock(totalQuantity, productID, existingRow);
        // Update the total for the row
        updateRowTotal(existingRow);
    } else {

        // Create unit dropdown options
        let unitOptions = '';
        let defaultUnitPrice = item.sellingPrice; // Default to the sale price

        // Check if unitInfo exists and is an array
        if (item.unitInfo && Array.isArray(item.unitInfo)) {
            item.unitInfo.forEach(unit => {
                // console.log("Unit ID: ", item.unit_id);
                const isSelected = parseInt(unit.unit_id) === parseInt(item.unit_id) ? 'selected' : '';
                if (isSelected) {
                    defaultUnitPrice = item.sellingPrice; // Use the default unit's price
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
            <span class="item-counter badge badge-secondary mr-2">${$("#saleItems tr").length + 1}</span>
            </td>
                <td>
                    <input type="text" class="form-control-plaintext text-left" name="productName[]" value="${item.productName}" readonly>
                    <input type="hidden" class="productID" name="product_id[]" value="${item.productID}">
                    <input type="hidden" class="productQty" name="productOldQty[]" value="${item.productQty}">
                    <input type="hidden" class="cost" name="cost[]" value="${item.cost}">
                    <input type="hidden" data-txt="abc" class="calculatedCost" name="calculatedCost[]" value="${parseFloat(item.calculatedCost)}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center bg-warning marquee-input" style="width:100%;" value="${item.stock}" readonly>
                </td>
                <td class="align-middle">
                    <div class="d-flex justify-content-center quantity-controls">
                        <button type="button" class="btn btn-xs btn-outline-danger quantity-decrease">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" name="quantity[]"
                               class="form-control form-control-sm quantity text-center mx-1"
                               value="${item.inputQty}" min="1" step="0.1"
                               style="width: 50px;">
                        <button type="button" class="btn btn-xs btn-outline-success quantity-increase">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <select class="form-control form-control-sm unit-select" name="unit[]">
                        ${unitOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm unit-price text-right unitPrice" name="selling_price[]" value="${defaultUnitPrice}">
                </td>
                <td>
                    <input type="text" class="form-control-plaintext total-amount text-right" name="amount[]" value="${defaultUnitPrice}" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $("#saleItems").append(tableRow);

        // Add event handler for unit selection change
        $("#saleItems tr:last .unit-select").on('change', function () {
            const selectedOption = $(this).find('option:selected');
            const purchasePrice = selectedOption.data('purchase-price');
            const sellingPrice = selectedOption.data('selling-price');

            // Update the price input (using purchase price by default)
            $(this).closest('tr').find('.cost').val(purchasePrice);
            $(this).closest('tr').find('.unit-price').val(sellingPrice);

            // Update the total
            updateRowTotal($(this).closest('tr'));
            updatePaymentCalculations();
        });

        const totalQuantity = calculateTotalQuantity(item.productID);
        // checkProductStock(totalQuantity, item.productID, $("#saleItems tr:last"));
        // Call updateRowTotal for the new row
        const newRow = $("#saleItems tr:last");
        updateRowTotal(newRow);
        // Focus and select the quantity input in new row
        newRow.find('.quantity').trigger('focus')[0].select();
    }


    updateAllTotals();
    updatePaymentCalculations();
    // Add keyboard shortcut (F2) to focus search bar
    $(document).off('keydown.focusSearch').on('keydown.focusSearch', function (e) {
        if (e.key === 'F2') {
            e.preventDefault();
            $("#searchItem").trigger('focus')[0].select();
        }
    });

    // Also allow Ctrl+F to focus search (common convention)
    $(document).on('keydown', function (e) {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            $("#searchItem").trigger('focus')[0].select();
        }
    });
}

// Track if we're viewing an existing invoice
let isExistingInvoice = false;
// Payment calculations function
export function updatePaymentCalculationsxxx() {
    // Get the total payable amount (remove commas if present)
    const totalPayable = parseFloat($('#grandTotal').text().replace(/,/g, '')) || 0;

    // Calculate total payments from both sources
    let totalPayments = 0;

    // 1. From the main paid amount (for existing invoices)
    if (isExistingInvoice) {
        totalPayments += parseFloat($('#amountPaid').val()) || 0;
    }

    // 2. From payment history table
    $('#paymentHistoryBody tr').each(function () {
        const amountText = $(this).find('td:nth-child(2)').text().replace(/,/g, '');
        totalPayments += parseFloat(amountText) || 0;
    });

    // Use let instead of const since we need to reassign these
    let balanceDue = totalPayable - totalPayments;
    let change = 0;

    if (totalPayments > totalPayable) {
        change = totalPayments - totalPayable;
        balanceDue = 0;
    }

    // Update the display elements
    $('#balanceAmount').text(balanceDue.toFixed(2));
    $('#changeAmount').text(change.toFixed(2));

    // Update hidden form fields if they exist
    if ($('#balanceAmountValue').length) {
        $('#balanceAmountValue').val(balanceDue.toFixed(2));
    }
    if ($('#changeAmountValue').length) {
        $('#changeAmountValue').val(change.toFixed(2));
    }

    // Visual feedback
    if (change > 0) {
        $('#changeAmount').addClass('text-success').removeClass('text-danger');
        $('#balanceAmount').addClass('text-success').removeClass('text-danger');
    } else if (balanceDue > 0) {
        $('#balanceAmount').addClass('text-danger').removeClass('text-success');
        $('#changeAmount').removeClass('text-success text-danger');
    } else {
        $('#balanceAmount').removeClass('text-danger text-success');
        $('#changeAmount').removeClass('text-success text-danger');
    }
}
$("#searchItem").trigger('focus');
export function updateRowTotal(row) {
    const quantity = parseFloat(row.find(".quantity").val()) || 0;
    const unitPrice = parseFloat(row.find(".unitPrice").val()) || 0;
    const cost = parseFloat(row.find(".cost").val()) || 0;

    const itemTotal = quantity * unitPrice;
    const calculatedCost = quantity * cost;

    row.find(".total-amount").val(itemTotal.toFixed(2));
    row.find(".calculatedCost").val(calculatedCost.toFixed(2));
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

// sales_utilities.js



// export function submitCustomerForm(selector) {
//     $(document).on("submit", selector, function (e) {
//         e.preventDefault();
//         const submitButton = $(this).find("button[type='submit']");
//         const spinnerText = "Processing...";
//         const originalText = addSpinner(submitButton, spinnerText);
//         const formData = serializeFormToObject(this);
//         const url = $(this).attr("action");

//         $.ajax({
//             url,
//             type: "POST",
//             data: formData,
//             success: function (response) {
//                 if (response.success) {
//                     $('#customersTable').DataTable().ajax.reload();
//                     $("#addCustomerModal").modal("hide");
//                     $("#searchCustomer").val(response.customerName);
//                     $("#customer_id").val(response.customerID);
//                     $("#area_id").val(response.areaID);
//                     showSuccessToast("Customer added successfully!");
//                 } else if (response.errors) {
//                     $.each(response.errors, (field, messages) =>
//                         messages.forEach((message) => showWarningToast(message))
//                     );
//                 }
//                 removeSpinner(submitButton, originalText);
//             },
//             error: function () {
//                 showErrorToast("Failed to add customer.");
//                 removeSpinner(submitButton, originalText);
//             },
//         });
//     });
// }





