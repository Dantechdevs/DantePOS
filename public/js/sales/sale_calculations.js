import {
    initializeAutocompleteForSearch,
    updateRowTotal,
    updateAllTotals,
    checkProductStock,
    initializePaymentCalculations
} from '../common/sales_utilities.js';

// Track if we're viewing an existing invoice
// let isExistingInvoice = false;

initializeFunctions();
function initializeFunctions() {
    initializeAutocompleteForSearch('#searchItem');
initializePaymentCalculations()
}

$(function () {


    // Add event listeners for quantity updates
    // $(document).on('keyup click change', '.quantity, .unitPrice, .quantity-increase, .quantity-decrease', function () {
    //     const row = $(this).closest("tr");
    //     const quantityInput = row.find(".quantity");
    //     const unitPriceInput = row.find(".unitPrice");

    //     let currentQuantity = parseInt(quantityInput.val()) || 0;

    //     // If quantity is manually set to 0, update it to 1
    //     if (currentQuantity === 0) {
    //         currentQuantity = 1;
    //     }

    //     if ($(this).hasClass("quantity-increase")) {
    //         currentQuantity += 1;
    //     } else if ($(this).hasClass("quantity-decrease") && currentQuantity > 1) {
    //         currentQuantity -= 1;
    //     }

    //     quantityInput.val(currentQuantity);

    //     // Update row total (quantity * unit price)
    //     updateRowTotal(row);
    //     updateAllTotals();
    //     // updatePaymentCalculations();

    //     const productID = row.find("input.productID").val();
    //     const totalQuantity = calculateTotalQuantity(productID);
    //     // checkProductStock(totalQuantity, productID, row);
    //     initializePaymentCalculations();
    // });
    
    $(document).on('keyup click change', '.quantity, .unitPrice, .quantity-increase, .quantity-decrease', function () {
        const row = $(this).closest("tr");
        const quantityInput = row.find(".quantity");
        const unitPriceInput = row.find(".unitPrice");

        // Parse as float to preserve decimal values
        let currentQuantity = parseFloat(quantityInput.val()) || 0;

        // If quantity is manually set to less than 1, update it to 1
        if (currentQuantity < 1) {
            currentQuantity = 1;
        }

        if ($(this).hasClass("quantity-increase")) {
            currentQuantity += 1;
        } else if ($(this).hasClass("quantity-decrease")) {
            currentQuantity -= 1;
            // Ensure we don't go below 1
            if (currentQuantity < 1) {
                currentQuantity = 1;
            }
        }

        // Format the value appropriately - preserve integers, show decimals only when needed
        if (currentQuantity % 1 === 0) {
            quantityInput.val(currentQuantity.toFixed(0)); // Whole number - no decimals
        } else {
            quantityInput.val(currentQuantity.toFixed(1)); // Decimal - show one decimal place
        }

        // Update row total (quantity * unit price)
        updateRowTotal(row);

        const productID = row.find("input.productID").val();
        const totalQuantity = calculateTotalQuantity(productID);
        // checkProductStock(totalQuantity, productID, row);
        initializePaymentCalculations();
    });

    // Add event listener for "Other Charges" and "Discount on All"
    $(document).on('keyup change', '#otherCharges, #globalDiscount, #discountType', function () {
        updateAllTotals();
        initializePaymentCalculations();
        // updatePaymentCalculations();
    });

    // Add new payment handler
    // $(document).on('click', '#addPaymentBtn', function () {
    //     const amount = parseFloat($('#newPaymentAmount').val());

    //     if (!amount || amount <= 0) {
    //         showWarningToast('Please enter a valid payment amount');
    //         return;
    //     }

    //     // Add to payment history
    //     const paymentDate = new Date();
    //     const formattedDate = moment(paymentDate).format('DD-MM-YYYY | hh:mm A');

    //     $('#paymentHistoryBody').append(`
    //         <tr>
    //             <td>${formattedDate}</td>
    //             <td class="text-right">${amount.toFixed(2)}</td>
    //         </tr>
    //     `);

    //     // Clear the input
    //     $('#newPaymentAmount').val('');

    //     // Update calculations
    //     // updatePaymentCalculations();
    // });

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

    // Remove item
    $(document).on("click", ".remove-item", function () {
        $(this).closest("tr").remove();
        updateAllTotals();
        initializePaymentCalculations();
        // updatePaymentCalculations();
    });

    // // Payment calculations function
    // function updatePaymentCalculations() {
    //     // Get the total payable amount (remove commas if present)
    //     const totalPayable = parseFloat($('#grandTotal').text().replace(/,/g, '')) || 0;

    //     // Calculate total payments from both sources
    //     let totalPayments = 0;

    //     // 1. From the main paid amount (for existing invoices)
    //     if (isExistingInvoice) {
    //         totalPayments += parseFloat($('#amountPaid').val()) || 0;
    //     }

    //     // 2. From payment history table
    //     $('#paymentHistoryBody tr').each(function () {
    //         const amountText = $(this).find('td:nth-child(2)').text().replace(/,/g, '');
    //         totalPayments += parseFloat(amountText) || 0;
    //     });

    //     // Use let instead of const since we need to reassign these
    //     let balanceDue = totalPayable - totalPayments;
    //     let change = 0;

    //     if (totalPayments > totalPayable) {
    //         change = totalPayments - totalPayable;
    //         balanceDue = 0;
    //     }

    //     // Update the display elements
    //     $('#balanceAmount').text(balanceDue.toFixed(2));
    //     $('#changeAmount').text(change.toFixed(2));

    //     // Update hidden form fields if they exist
    //     if ($('#balanceAmountValue').length) {
    //         $('#balanceAmountValue').val(balanceDue.toFixed(2));
    //     }
    //     if ($('#changeAmountValue').length) {
    //         $('#changeAmountValue').val(change.toFixed(2));
    //     }

    //     // Visual feedback
    //     if (change > 0) {
    //         $('#changeAmount').addClass('text-success').removeClass('text-danger');
    //         $('#balanceAmount').addClass('text-success').removeClass('text-danger');
    //     } else if (balanceDue > 0) {
    //         $('#balanceAmount').addClass('text-danger').removeClass('text-success');
    //         $('#changeAmount').removeClass('text-success text-danger');
    //     } else {
    //         $('#balanceAmount').removeClass('text-danger text-success');
    //         $('#changeAmount').removeClass('text-success text-danger');
    //     }
    // }

    // Function to handle invoice search results
    window.loadInvoiceForEdit = function (response) {
        const invoice = response.invoice;
        isExistingInvoice = true;

        // Disable the paid amount field for existing invoices
        $('#amountPaid').attr('readonly', true);

        // Load payment history
        $('#paymentHistoryBody').empty();
        if (response.payments && response.payments.length > 0) {
            response.payments.forEach(payment => {
                const formattedDate = moment(payment.payment_date).format('DD-MM-YYYY | hh:mm A');
                $('#paymentHistoryBody').append(`
                    <tr>
                        <td>${formattedDate}</td>
                        <td class="text-right">${payment.amount.toFixed(2)}</td>
                    </tr>
                `);
            });
        }

        // Show the payment history section
        $('#invoicePaymentHistory').show();


    };
});
