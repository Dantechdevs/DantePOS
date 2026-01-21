import {
    initializeDataTable,
    handleDeleteAction
} from '../common/utilities.js';

import { dynamicFormData, submitForm } from '../utilities/utilities.js';


$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /************************* Load Data **************************/
    const customersListUrl = $('#customersTable').data('url');

    initializeDataTable({
        tableSelector: '#customersTable',
        ajaxUrl: customersListUrl,
        columns: [
            { data: null, name: 'id', title: '#', className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
            { data: 'name', name: 'name', title: 'Name' },
            { data: 'mobile', name: 'mobile', title: 'Mobile', className: 'text-center' },
            { data: 'balance', name: 'balance', title: 'Balance', className: 'text-right' },
            { data: 'attachments', name: 'attachments', title: 'Attachments', className: 'text-center', orderable: false, searchable: false },
            { data: 'createdBy', name: 'user.name', title: 'Created By' },
            { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
        ],
    });

    /************************* Add Form **********************/
    $(document).on('click', '.addCustomer', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveUrl = $(this).attr('data-saveCustomerUrl');
        const title = "New Customer";
        const container = "#customerModalContainer";
        const modalId = "#addCustomerModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /*********************** Edit Form ***********************/

    $(document).on('click', '.editCustomer', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = $(this).attr('data-saveCustomerUrl');
        var title = "Update Customer";
        const container = "#customerModalContainer";
        const modalId = "#addCustomerModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    })

    /*********************** Submit Form **********************/
    submitForm({
        formSelector: "#addCustomerForm",
        reloadTableSelector: "#customersTable",
        modalSelector: "#addCustomerModal",
        successToastMessage: "Customer added successfully.",
        extraFieldUpdates: function (response) {
            $("#searchCustomer").val(response.customerName);
            $("#customer_id").val(response.customerID);
            $("#area_id").val(response.areaID);
            $("#customerBalance").val(response.customerBalance);
            $(".customerBalance").text(response.customerBalance);
        },
        onSuccessCallback: function (response) {
            // console.log("Custom success logic executed.", response);
        },
        onErrorCallback: function (error) {
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


    /************************ View Data *********************/
    $(document).on('click', '.view', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-url');
        var saveUrl = null;
        var title = "Customer Information";
        const container = "#customerModalContainer";
        const modalId = "#viewCustomerModal";
        dynamicFormData(url, title, saveUrl, container, modalId)
    });

    /*********************** Delete Data **********************/

    $(document).on('click', '.delete', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get URL from the data attribute
        const tableId = 'customersTable'; // ID of your DataTable

        handleDeleteAction({
            url: url,
            tableId: tableId,
            successMessage: "The record has been successfully deleted.",
            errorMessage: "Unable to delete the record. Please try again.",
            isDelete: true
        });
    });

    /*********************** Pay Due Payment **********************/
    $(document).on('click', '.payDuePayment', function () {
        var url = $(this).data('url');

        $.get(url, function (data) {
            $('#customerModalContainer').html(data);
            $('#paymentModal').modal('show');
        });
    });

    /*********************** Submit Customer Payment Form **********************/
    // Define functions in global scope first
window.showPaymentReceipt = function(receiptData) {
    const receiptContent = document.getElementById('receiptContent');

    let notesHTML = '';
    if (receiptData.payment_notes) {
        notesHTML = `
        <div class="notes-section">
            <div class="notes-header">Payment Notes</div>
            <div class="notes-content">${receiptData.payment_notes}</div>
        </div>`;
    }

    receiptContent.innerHTML = `
        <div class="receipt-header">
            <h2>PAYMENT RECEIPT</h2>
            <div class="company-name">TRADESPHERE SOLUTIONS</div>
            <div class="company-info">123 Business Street, City</div>
            <div class="company-info">Phone: (555) 123-4567</div>
        </div>

        <div class="receipt-details">
            <div class="detail-row">
                <span class="detail-label">Receipt#:</span>
                <span class="detail-value">${receiptData.receipt_number}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">${receiptData.payment_date}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer:</span>
                <span class="detail-value">${receiptData.customer_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Method:</span>
                <span class="detail-value">${receiptData.payment_method} <span class="status-paid">PAID</span></span>
            </div>
        </div>

        <div class="receipt-items">
            <div class="items-header">
                PAYMENT DETAILS
            </div>
            ${receiptData.receipt_items.map(item => `
                <div class="item-row">
                    <span class="item-desc">${item.description}</span>
                    <span class="item-amount">${parseFloat(item.amount).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
                </div>
            `).join('')}
        </div>

        ${notesHTML}

        <div class="receipt-totals">
            <div class="total-row">
                <span>Total Paid:</span>
                <span>${parseFloat(receiptData.total_amount).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
            </div>
            <div class="total-row">
                <span>Applied:</span>
                <span>${parseFloat(receiptData.amount_applied).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
            </div>
            ${receiptData.remaining_amount > 0 ? `
            <div class="total-row">
                <span>Remaining:</span>
                <span>${parseFloat(receiptData.remaining_amount).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
            </div>
            ` : ''}
            <div class="total-row grand-total">
                <span>NET RECEIVED:</span>
                <span>${parseFloat(receiptData.amount_applied).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
            </div>
        </div>

        <div class="signature-area">
            <div class="detail-row">
                <span class="detail-label">Processed By:</span>
                <span class="detail-value">${receiptData.processed_by}</span>
            </div>
            <div class="signature-line">
                Authorized Signature
            </div>
        </div>

        <div class="receipt-footer">
            <p>** COMPUTER GENERATED RECEIPT **</p>
            <p>Thank you for your business!</p>
            <p>For queries: support@tradesphere.com</p>
        </div>
    `;

    // Show the receipt modal
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    receiptModal.show();

    // Close payment modal
    $('#paymentModal').modal('hide');
};

window.downloadReceipt = function() {
    const receiptContent = document.getElementById('receiptContent');

    if (typeof html2pdf === 'undefined') {
        showErrorToast('PDF library not available');
        return;
    }

    const opt = {
        margin: 5,
        filename: `receipt-${Date.now()}.pdf`,
        image: { type: 'jpeg', quality: 0.8 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            logging: false
        },
        jsPDF: {
            unit: 'mm',
            format: [80, 150], // Small format for receipt
            orientation: 'portrait'
        }
    };

    const downloadBtn = event?.target;
    const originalText = downloadBtn?.innerHTML;

    if (downloadBtn) {
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';
        downloadBtn.disabled = true;
    }

    html2pdf()
        .set(opt)
        .from(receiptContent)
        .save()
        .finally(() => {
            if (downloadBtn) {
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            }
        });
};

window.printReceipt = function() {
    const receiptContent = document.getElementById('receiptContent');

    const printWindow = window.open('', '_blank', 'width=400,height=600');

    // Get the exact HTML and CSS from the receipt modal
    const receiptHTML = receiptContent.innerHTML;
    const currentStyles = Array.from(document.querySelectorAll('style'))
        .map(style => style.innerHTML)
        .join('');

    const printStyles = `
        <style>
            @media print {
                @page {
                    margin: 0;
                    padding: 0;
                    size: 76mm auto;
                }

                body {
                    font-family: "Courier New", monospace;
                    font-size: 11px;
                    line-height: 1.2;
                    margin: 0;
                    padding: 5mm 3mm;
                    color: #000;
                    background: #ffffff;
                    width: 76mm;
                    max-width: 76mm;
                }
                * {
                    box-sizing: border-box;
                }
                .receipt-container {
                    width: 100%;
                    margin: 0 auto;
                }
                .receipt-header {
                    text-align: center;
                    padding-bottom: 6px;
                    margin-bottom: 6px;
                    border-bottom: 1px solid #000;
                }
                .receipt-header h2 {
                    font-size: 14px;
                    font-weight: bold;
                    margin: 0 0 4px 0;
                    text-transform: uppercase;
                }
                .company-name {
                    font-size: 11px;
                    font-weight: bold;
                    margin: 2px 0;
                }
                .company-info {
                    font-size: 9px;
                    margin: 1px 0;
                    line-height: 1.1;
                }
                .receipt-details {
                    margin: 8px 0;
                }
                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 2px;
                }
                .detail-label {
                    font-weight: bold;
                    font-size: 10px;
                }
                .detail-value {
                    font-size: 10px;
                    text-align: right;
                }
                .receipt-items {
                    margin: 8px 0;
                }
                .items-header {
                    text-align: center;
                    font-weight: bold;
                    margin-bottom: 4px;
                    font-size: 10px;
                    text-transform: uppercase;
                    border-top: 1px solid #000;
                    border-bottom: 1px solid #000;
                    padding: 3px 0;
                }
                .item-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 3px;
                }
                .item-desc {
                    font-size: 10px;
                    flex: 2;
                }
                .item-amount {
                    font-size: 10px;
                    text-align: right;
                    flex: 1;
                    font-weight: bold;
                }
                .notes-section {
                    margin: 8px 0;
                }
                .notes-header {
                    text-align: center;
                    font-weight: bold;
                    margin-bottom: 3px;
                    font-size: 10px;
                    border-top: 1px dashed #000;
                    border-bottom: 1px dashed #000;
                    padding: 3px 0;
                }
                .notes-content {
                    font-size: 10px;
                    text-align: center;
                    padding: 4px 0;
                }
                .receipt-totals {
                    margin: 8px 0;
                    padding: 6px 0;
                    border-top: 1px solid #000;
                    border-bottom: 1px solid #000;
                }
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 3px;
                    font-size: 10px;
                }
                .grand-total {
                    font-weight: bold;
                    border-top: 1px solid #000;
                    margin-top: 4px;
                    padding-top: 4px;
                    font-size: 11px;
                }
                .signature-area {
                    margin-top: 10px;
                    padding-top: 6px;
                    border-top: 1px dashed #000;
                }
                .signature-line {
                    border-top: 1px solid #000;
                    margin-top: 15px;
                    padding-top: 2px;
                    text-align: center;
                    font-size: 8px;
                }
                .receipt-footer {
                    text-align: center;
                    margin-top: 10px;
                    padding-top: 6px;
                    border-top: 1px dashed #000;
                    font-size: 8px;
                    line-height: 1.1;
                }
                .receipt-footer p {
                    margin: 2px 0;
                }
                .status-paid {
                    background: #000;
                    color: white;
                    padding: 1px 4px;
                    border-radius: 2px;
                    font-size: 8px;
                    margin-left: 5px;
                }

                /* Hide print button and modal elements */
                .btn, .modal-footer, .modal-header {
                    display: none !important;
                }
            }

            @media screen {
                body {
                    padding: 20px;
                    background: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                }
                .receipt-container {
                    border: 1px solid #000;
                    padding: 15px;
                    background: white;
                }
            }

            /* Ensure proper printing */
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        </style>
    `;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            ${printStyles}
        </head>
        <body>
            <div class="receipt-container">
                ${receiptHTML}
            </div>
            <script>
                // Auto-print with proper timing
                setTimeout(function() {
                    window.print();

                    // Close after print
                    window.onafterprint = function() {
                        setTimeout(function() {
                            window.close();
                        }, 100);
                    };

                    // Fallback close
                    setTimeout(function() {
                        if (!window.closed) {
                            window.close();
                        }
                    }, 3000);
                }, 500);
            <\/script>
        </body>
        </html>
    `);

    printWindow.document.close();
};



// Then your submitForm code
submitForm({
    formSelector: "#customerPaymentForm",
    reloadTableSelector: "#customersTable",
    modalSelector: "#paymentModal",
    successToastMessage: "Payment recorded successfully.",
    extraFieldUpdates: function (response) {
        // Show receipt popup
        if (response.receipt_data) {
            window.showPaymentReceipt(response.receipt_data);
        }
    },
    onSuccessCallback: function (response) {
        console.log("Payment processed successfully", response);
    },
    onErrorCallback: function (error) {
        console.error("Payment processing failed", error);
    },
    spinnerText: "Processing Payment...",
    requestOptions: {
        timeout: 150000,
    },
});

// Add event listeners for modal closing
document.addEventListener('DOMContentLoaded', function() {
    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) {
        receiptModal.addEventListener('hidden.bs.modal', function () {
            // Clear receipt content when modal is closed
            const receiptContent = document.getElementById('receiptContent');
            if (receiptContent) {
                receiptContent.innerHTML = '';
            }
        });
    }
});

// Add event listener for view attachments button
$(document).on('click', '.view-attachments', function() {
    const url = $(this).data('url');
    const customerName = $(this).data('customer-name');

    // Show loading state
    const button = $(this);
    const originalHtml = button.html();
    button.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

    // AJAX call to get attachments
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            button.html(originalHtml).prop('disabled', false);
            showAttachmentsModal(response.attachments, customerName);
        },
        error: function(xhr) {
            button.html(originalHtml).prop('disabled', false);
            alert('Error loading attachments');
            console.error('Error:', xhr.responseText);
        }
    });
});

// Function to show attachments in modal
function showAttachmentsModal(attachments, customerName) {
    let attachmentsHtml = '';

    if (attachments && attachments.length > 0) {
        attachmentsHtml = `
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>File Name</th>
                            <th>Uploaded At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;

        attachments.forEach((attachment, index) => {
            const uploadedAt = new Date(attachment.created_at).toLocaleDateString();

            attachmentsHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${attachment.file_name}</td>
                    <td>${uploadedAt}</td>
                    <td>
                        <button class="btn btn-primary btn-sm view-attachment"
                                data-url="${attachment.file_url}"
                                data-filename="${attachment.file_name}"
                                title="View ${attachment.file_name}">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>`;
        });

        attachmentsHtml += `
                    </tbody>
                </table>
            </div>`;
    } else {
        attachmentsHtml = '<div class="alert alert-info text-center">No attachments found for this customer.</div>';
    }

    // Create and show modal
    const modalHtml = `
        <div class="modal fade" id="attachmentsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attachments for ${customerName}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${attachmentsHtml}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if any
    $('#attachmentsModal').remove();

    // Append and show new modal
    $('body').append(modalHtml);
    $('#attachmentsModal').modal('show');
}



// Event listener for viewing individual attachment
$(document).on('click', '.view-attachment', function() {
    const fileUrl = $(this).data('url');
    const fileName = $(this).data('filename');

    // Open file in new tab or handle based on file type
    window.open(fileUrl, '_blank');
});


});
