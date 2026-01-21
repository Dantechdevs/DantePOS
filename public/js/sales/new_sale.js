// Import functions from sales_utilities.js
import {
    initializeAutocompleteForSearch, addItemToTable, updateRowTotal, initializePaymentCalculations
} from '../common/sales_utilities.js';

import {
    initializeCustomerAutocomplete,
} from '../common/utilities.js';

import { submitForm } from '../utilities/utilities.js';

initializeFunctions();



$(document).on('click', '#searchInvoiceBtn', function () {
    const $btn = $(this);
    const invoiceNo = $('#invoiceSearch').val().trim();

    if (invoiceNo) {
        // Add spinner to button
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Searching...');
        $btn.prop('disabled', true);

        searchInvoice(invoiceNo, function () {
            // Reset button when complete
            $btn.html('<i class="fas fa-search"></i>');
            $btn.prop('disabled', false);
        });
    } else {
        showWarningToast('Please enter an invoice number');
    }
});

$(document).on('keypress', '#invoiceSearch', function (e) {
    if (e.which === 13) {
        e.preventDefault();
        $('#searchInvoiceBtn').trigger('click');
    }
});

function searchInvoice(invoiceNo, completeCallback) {
    $.ajax({
        url: '/search-invoice',
        type: 'GET',
        data: { invoice_no: invoiceNo },
        beforeSend: function () {
            // Spinner is now on the button
        },
        success: function (response) {
            if (response.success && response.invoice) {
                loadInvoiceForEdit(response);
                // Replace search with return icon
                replaceSearchWithReturn();
            } else {
                showErrorToast(response.message || 'No invoice found');
            }
            // console.clear();
        },
        error: function (xhr) {
            showErrorToast('Error searching invoice');
            console.error(xhr.responseText);
        },
        complete: completeCallback
    });
}

function replaceSearchWithReturn() {
    const $searchContainer = $('#invoiceSearch').parent();
    $searchContainer.html(`
        <button id="returnToAddForm" class="btn btn-outline-secondary" type="button">
            <i class="fas fa-arrow-left"></i> Return to New Sale
        </button>
    `);
}



function loadInvoiceForEdit(response) {
    // $('#amountPaid').prop('readonly', true); // Lock the field
    const invoice = response.invoice;
    const url = response.update_url;
    // console.log('Loading invoice for edit:', invoice);
    // Clear existing items
    $('#saleItems').empty();

    // Set basic invoice info
    $('#invoice_no').val(invoice.invoice_no);
    // $('#datepicker').val(invoice.date.split(' ')[0]);
    // const dateObj = new Date(invoice.date);
    // const day = String(dateObj.getDate()).padStart(2, '0');
    // const month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    // const year = dateObj.getFullYear();
    // const formattedDate = `${day}-${month}-${year}`;
    const formattedDate = moment(invoice.date).format('DD-MM-YYYY');

    $('#datepicker').val(formattedDate);

    const formattedDueDate = moment(invoice.due_date).format('DD-MM-YYYY');
    $('#endDate').val(formattedDueDate);
    // Set customer
    if (invoice.customer) {
        $('#searchCustomer').val(invoice.customer.name);
        $('#customer_id').val(invoice.customer.id);
        $('#area_id').val(invoice.customer.area_id);
        $('.customerBalance').text(response.customerBalance);
    }

    $("#godown_id").val(invoice.godown_id).trigger('change');
    // Define status options
    const statusOptions = {
        '0': 'Cancel',
        '1': 'Billed',
        '2': 'Pending',
        '3': 'Return'
    };

    // Clear and rebuild status dropdown
    const $statusDropdown = $('#status').empty();
    $.each(statusOptions, function (value, text) {
        $statusDropdown.append($('<option>', {
            value: value,
            text: text
        }));
    });

    // Set the selected status from invoice
    $statusDropdown.val(invoice.status.toString()).trigger('change');
    // Set  payment
    $('#payment_type').val(invoice.payment_type);

    // Add items to table
    invoice.items.forEach(item => {
        // console.log('single item to table:', item.productQty);
        const itemData = {
            productID: item.productID,
            productName: item.productName,
            productQty: item.productQty,
            inputQty: item.inputQty,
            stock: item.stock,
            cost: item.cost,
            calculatedCost: item.calculatedCost,
            sellingPrice: item.sellingPrice,
            unitInfo: item.unitInfo,
            unit_id: item.unit_id,
        };
        // console.log('itemData:', itemData);

        addItemToTable(itemData);
        // let quantity = (item.productQty > 1) ? item.productQty : 1;
        // console.log(invoice);
        // Set the correct quantity from the original sale
        const lastRow = $('#saleItems tr:last');
        // lastRow.find('.quantity').val(
        //     invoice.items.find(i => i.productID === item.productID)?.quantity || 1
        // );

        lastRow.find('.quantity').val(
            (item.productQty > 1) ? item.productQty : 1
        );
    });

    // Set financial values
    $('#sub_total').val(invoice.sub_total);
    $('#globalDiscount').val(invoice.discount);
    $('#discountType').val(invoice.discount_type);
    $('#otherChargesTotal').text(invoice.discount);
    $('#otherChargesTotalVal').val(invoice.discount);
    $('#otherCharges').val(invoice.other_charges);
    $('#otherChargesTotalVal').val(invoice.other_charges);
    $('#otherChargesTotal').text(invoice.other_charges);
    $('#grand_total').val(invoice.grand_total);
    $('#amountPaid').val(invoice.paid_amount);
    $('#balanceAmount').text(invoice.balance_amount);
    $('#balanceAmountValue').val(invoice.balance_amount);
    $('#changeAmount').text(invoice.change_amount);
    $('#changeAmountValue').val(invoice.change_amount);
    $('#note').val(invoice.notes || '');

    $('#saleForm').attr('action', url);
    $('#saleForm').find('input[name="_method"]').remove();
    // $('#saleForm').append('<input type="hidden" name="_method" value="PUT">');
    $('#saveSaleBtn').html('<i class="fas fa-save"></i> Update Sale');

    // Convert to update form
    $('#saleForm').attr('action', url);
    $('#saleForm').find('input[name="_method"]').remove();
    // $('#saleForm').append('<input type="hidden" name="_method" value="PUT">');
    $('#saveSaleBtn').html('<i class="fas fa-save"></i> Update Sale');

    // Load payment history dynamically
    updatePaymentHistory(response.invoicePayments || []);
    // setupPaymentSystem(invoice);
    //  setupAdditionalPayments(invoice);
    $('#invoicePaymentHistory').show();
    showSuccessToast('Invoice loaded');
}

//// add invoice payment history start

// function updatePaymentHistory(payments) {
//     const $paymentBody = $('#paymentHistoryBody');
//     $paymentBody.empty();

//     if (payments.length > 0) {
//         payments.forEach(payment => {
//             // const paymentDate = new Date(payment.payment_date);
//             const formattedDate = moment(payment.payment_date).format('DD-MM-YYYY | hh:mm A');

//             $paymentBody.append(`
//                 <tr>
//                     <td>${formattedDate}</td>
//                     <td class="text-right">${payment.amount}</td>

//                 </tr>
//             `);
//         });
//     } else {
//         $paymentBody.append(`
//             <tr>
//                 <td colspan="3" class="text-center">No payments recorded</td>
//             </tr>
//         `);
//     }
// }

function updatePaymentHistory(payments) {
    const $paymentBody = $('#paymentHistoryBody').empty();

    if (payments.length > 0) {
        payments.forEach(payment => {
            const formattedDate = moment(payment.payment_date).format('DD-MM-YYYY | hh:mm A');
            $paymentBody.append(`
                <tr>
                    <td>${formattedDate}</td>
                    <td class="text-right">${Math.round(payment.amount)}</td>
                </tr>
            `);
        });
    } else {
        $paymentBody.append('<tr><td colspan="2" class="text-center">No payments recorded</td></tr>');
    }
}

function setupPaymentSystem(invoice) {
    const $newPaymentInput = $('#newPaymentAmount');
    const $amountPaid = $('#amountPaid');
    const $balanceAmount = $('#balanceAmount');
    const $changeAmount = $('#changeAmount');
    const $grandTotal = $('#grandTotal');

    // Initialize state
    let currentPayable = Math.round(parseFloat(invoice.grand_total) || 0);
    let originalPaid = Math.round(parseFloat(invoice.paid_amount) || 0);
    let newPayments = 0;
    let balanceDue = currentPayable - originalPaid;

    // Initialize display
    $amountPaid.val(originalPaid);
    $grandTotal.text(currentPayable);
    $balanceAmount.text(balanceDue);
    $changeAmount.text('0');

    // Handle new payments
    $newPaymentInput.on('input', function () {
        const paymentInput = Math.round(parseFloat($(this).val()) || 0);

        // Prevent overpayment
        if (paymentInput > balanceDue) {
            $(this).val(balanceDue);
            newPayments = balanceDue;
        } else {
            newPayments = paymentInput;
        }

        // Update display
        const remainingBalance = balanceDue - newPayments;
        $balanceAmount.text(remainingBalance);
        $changeAmount.text('0');
    });

    // Handle cart updates
    function updateCartTotals(newTotal) {
        currentPayable = Math.round(newTotal);
        balanceDue = currentPayable - originalPaid;

        // Adjust payment if needed
        if (newPayments > balanceDue) {
            $newPaymentInput.val(balanceDue);
            newPayments = balanceDue;
        }

        // Update display
        $grandTotal.text(currentPayable);
        $balanceAmount.text(balanceDue - newPayments);
    }

    // Handle form submission
    // $('#saleForm').off('submit').on('submit', function(e) {
    //     e.preventDefault();
    //     if (newPayments > 0) {
    //         $(this).append(`<input type="hidden" name="new_payment" value="${newPayments}">`);
    //     }
    //     this.submit();
    // });

    return { updateCartTotals };
}

// Also update your payment add/delete functions to use this dynamic body
// $(document).on('click', '#addPaymentBtn', function(e) {
//     e.preventDefault();
//     const amount = parseFloat($('#newPaymentAmount').val());
//     const date = $('#newPaymentDate').val();

//     if (!amount || amount <= 0) {
//         showWarningToast('Please enter a valid payment amount');
//         return;
//     }

//     const formattedDate = new Date(date).toLocaleString();
//     const $paymentBody = $('#paymentHistoryBody');

//     // Remove "no payments" row if it exists
//     if ($paymentBody.find('tr td').text() === 'No payments recorded') {
//         $paymentBody.empty();
//     }

//     $paymentBody.append(`
//         <tr>
//             <td>${formattedDate}</td>
//             <td class="text-right">${amount}</td>

//         </tr>
//     `);

//     $('#newPaymentAmount').val('');
//     updatePaymentCalculations();

//     // AJAX call to save payment...
// });

$(document).on('click', '.delete-payment', function () {
    const row = $(this).closest('tr');
    const paymentId = $(this).data('payment-id');
    const $paymentBody = $('#paymentHistoryBody');

    if (confirm('Are you sure you want to delete this payment?')) {
        row.remove();

        if ($paymentBody.find('tr').length === 0) {
            $paymentBody.append(`
                <tr>
                    <td colspan="3" class="text-center">No payments recorded</td>
                </tr>
            `);
        }

        // updatePaymentCalculations();

        // AJAX call to delete payment...
    }
});

//// add invoice payment history end

// Live clock update
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    $('#liveClock').text(timeString);
}

setInterval(updateClock, 1000);
updateClock(); // Initialize immediately

function initializeFunctions() {
    // Initialize the datepicker
    $('#datepicker').datepicker({
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
    });

    $('#endDate').datepicker({
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
    });

    // Initialize Select2 elements
    $('.select2').select2({
        placeholder: 'Select an option', // Placeholder text
        allowClear: true, // Allow clearing the selection
    });

    $('.select2bs4').select2({
        theme: 'bootstrap4', // Use the Bootstrap 4 theme
        placeholder: 'Select an option', // Placeholder text
        allowClear: true, // Allow clearing the selection
    });

    // Call sales utilities functions
    initializeAutocompleteForSearch('#searchItem'); // Replace with your product search input selector
    initializeCustomerAutocomplete('#searchCustomer'); // Replace with your customer search input selector

    initializePaymentCalculations();
    console.log('All functions initialized!');
}

$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });
    var status = $("#status").val();
    checkStatus(status)

    $(document).on('change', '#status', function (e) {
        e.preventDefault();
        var status = $(this).val();
        // console.log(status)
        checkStatus(status)
    })

    function checkStatus(status) {
        if (status == 1) {
            $('.paymentTypeDiv').show();
        } else {
            $('.paymentTypeDiv').hide();
        }
    }

    // Prevent form auto-submit when scanning a barcode
    $(document).on("keydown", "#saleForm input[name='barcode']", function (e) {
        if (e.key === "Enter") {
            // Prevent the default behavior of the Enter key
            e.preventDefault();
        }
    });
    $("#saleItems tr:last .unit-select").on('change', function () {
        const selectedOption = $(this).find('option:selected');
        const purchasePrice = selectedOption.data('purchase-price');
        const sellingPrice = selectedOption.data('selling-price');

        // Update the price input (using purchase price by default)
        $(this).closest('tr').find('.cost').val(purchasePrice);
        $(this).closest('tr').find('.unit-price').val(sellingPrice);

        // Update the total
        updateRowTotal($(this).closest('tr'));
    })
    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#saleForm",
        reloadTableSelector: "#AllSalesTable",
        modalSelector: null,
        successToastMessage: "Sale added successfully.",
        extraFieldUpdates: function (response) {
        },
        onSuccessCallback: function (response) {
            // console.log(response.sale_type)
            // console.log(response.invoice_url)
            // console.log("Custom success logic executed.", response);
            if (response.sale_type == 'new') {
                var saleUrl = $('.renderSalesForm').attr('data-loadFormUrl');
                // var saleUrl = '/add-sale';
                var renderCurrentForm = '.renderSalesForm';
                reloadFormComponent(saleUrl, renderCurrentForm);
                const windowFeatures = "scrollbars=yes,resizable=yes,height=500,width=500";
                window.open(response.invoice_url, "_blank", windowFeatures);
            } else if (response.sale_type == 'update') {
                setTimeout(function () {
                    window.location.href = response.url;
                }, 1000);
            }
            // console.clear();
        },
        onErrorCallback: function (error) {
            if (error.responseJSON && error.responseJSON.error) {
                const errors = error.responseJSON.error.split('<br>');
                errors.forEach(function (err) {
                    showErrorToast(err.trim());
                });
            } else if (error.status === 422) {
                const validationErrors = error.responseJSON.errors;
                $.each(validationErrors, function (field, messages) {
                    messages.forEach(function (message) {
                        showWarningToast(message); // Display each error as a toast
                    });
                });
            } else {
                showErrorToast("Failed to add sale. Please try again.");
            }
            // console.error("Custom error logic executed.", error);
        },
        beforeSendCallback: function () {
            // console.log("Custom: Show global loader...");
        },
        completeCallback: function () {
            // console.log("Custom: Hide global loader...");
        },
        spinnerText: "Submitting...",
        requestOptions: {
            timeout: 150000, // Custom timeout
        },
    });


    const reloadFormComponent = function (url, renderCurrentForm) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.html) {
                    $(renderCurrentForm).html(response.html);

                    // Remove previous assets
                    $('link[href^="css/"]').remove();
                    $('script[src^="js/"]').remove();

                    // Load new assets
                    let scriptsToLoad = response.scripts || [];
                    let stylesToLoad = response.styles || [];

                    // Load CSS files
                    stylesToLoad.forEach((styleUrl) => loadCSS(styleUrl));

                    // Load JS files
                    if (scriptsToLoad.length) {
                        let scriptsLoaded = 0;
                        scriptsToLoad.forEach((scriptUrl) => {
                            loadScript(scriptUrl.url, function () {
                                scriptsLoaded++;
                                if (scriptsLoaded === scriptsToLoad.length) {
                                    // All scripts loaded, initialize functions
                                    initializeFunctions();
                                }
                            }, scriptUrl.isModule);
                        });
                    } else {
                        // No scripts to load, initialize functions directly
                        initializeFunctions();
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Error reloading form:', error);
                alert('Failed to reload form view.');
            },
        });
    }

    // Click handler for return button
    $(document).on('click', '#returnToAddForm', function (e) {
        e.preventDefault();
        var link = '/add-sale';
        var currentForm = '.renderSalesForm';
        reloadFormComponent(link, currentForm);
    });

    function loadCSS(url) {
        if (!$("link[href='" + url + "']").length) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            document.head.appendChild(link);
        }
    }

    function loadScript(url, callback, isModule = false) {
        if (!$("script[src='" + url + "']").length) {
            const script = document.createElement('script');
            script.src = url;

            // Set type to module or regular script
            script.type = isModule ? 'module' : 'text/javascript';

            script.onload = () => callback && callback();
            script.onerror = () => console.error(`Failed to load script: ${url}`);

            document.head.appendChild(script);
        } else {
            if (callback) {
                callback();
            }
        }
    }
});
