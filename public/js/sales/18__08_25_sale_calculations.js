import {
    initializeAutocompleteForSearch,
    updateRowTotal,
    updateAllTotals,
    checkProductStock

} from '../common/sales_utilities.js';

initializeFunctions();
function initializeFunctions() {
    initializeAutocompleteForSearch('#searchItem'); // Replace with your product search input selector
}

$(function () {

    // window.updatePaymentCalculations();
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
        // checkProductStock(totalQuantity, productID, row);
    });



    // Add event listener for "Other Charges" and "Discount on All"
    $(document).on('keyup change', '#otherCharges, #globalDiscount, #discountType', function () {
        updateAllTotals(); // Call the calculation function on any change
    });



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
    });
});
