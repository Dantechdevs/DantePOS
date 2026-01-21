<!-- Add this to your blade template where modals are included -->
<div class="modal fade" id="invoicePaymentModal" tabindex="-1" role="dialog" aria-labelledby="invoicePaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="invoicePaymentModalLabel">Payment Processing</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <!-- Left Column - Customer & Invoice Info -->
                        <div class="col-md-6 border-right">
                            <div class="payment-info-card mb-4">
                                <h5 class="card-header bg-light">Customer Information</h5>
                                <div class="card-body">
                                    <div class="customer-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Name:</span>
                                            <span class="detail-value"
                                                id="customer-name">{{ optional($sale->customers)->name }}</span>
                                        </div>
                                        @if ($sale->customers && $sale->customers->mobile)
                                            <div class="detail-item">
                                                <span class="detail-label">Mobile:</span>
                                                <span class="detail-value"
                                                    id="customer-mobile">{{ optional($sale->customers)->mobile }}</span>
                                            </div>
                                        @endif

                                        @if ($sale->customers && $sale->customers->email)
                                            <div class="detail-item">
                                                <span class="detail-label">Email:</span>
                                                <span class="detail-value"
                                                    id="customer-email">{{ optional($sale->customers)->email }}</span>
                                            </div>
                                        @endif
                                        @if ($sale->customers && $sale->customers->address)
                                            <div class="detail-item">
                                                <span class="detail-label">Address:</span>
                                                <span class="detail-value"
                                                    id="customer-gst">{{ optional($sale->customers)->address }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="invoice-info-card">
                                <h5 class="card-header bg-light">Invoice Information</h5>
                                <div class="card-body">
                                    <div class="invoice-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Invoice #:</span>
                                            <span class="detail-value"
                                                id="invoice-number">{{ $sale->invoice_no }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Date:</span>
                                            <span class="detail-value"
                                                id="invoice-date">{{ date('d-m-Y | H:i A', strtotime($sale->date)) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Grand Total:</span>
                                            <span class="detail-value font-weight-bold"
                                                id="invoice-total">{{ number_format($sale->grand_total) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Amount Paid:</span>
                                            <span class="detail-value text-success"
                                                id="amount-paid">{{ number_format($totalPaid) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Balance Due:</span>
                                            <span class="detail-value text-danger font-weight-bold"
                                                id="balance-due">{{ number_format($dueAmount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Payment Form -->
                        <div class="col-md-6">
                            <form id="paymentForm" action="{{ route('invoice.payment.submit') }}">
                                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                <div class="form-group">
                                    <label for="paymentAmount">Payment Amount *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ optional($settings)['currency'] }}</span>
                                        </div>
                                        <input type="number" name="amount" class="form-control" id="paymentAmount"
                                            step="0.01" min="0" value="{{ $dueAmount }}"
                                            max="{{ $dueAmount }}" required>
                                    </div>
                                    <small class="form-text text-muted">Enter amount to pay</small>
                                </div>

                                <div class="form-group">
                                    <label for="paymentDate">Payment Date</label>
                                    <input type="date" class="form-control" id="paymentDate" name="date"
                                        value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="form-group">
                                    <label for="paymentNote">Payment Note</label>
                                    <textarea class="form-control" name="notes" id="paymentNote" rows="2"
                                        placeholder="Optional notes about this payment"></textarea>
                                </div>

                                <div class="payment-actions mt-4">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-success float-right">
                                        <i class="fas fa-check-circle"></i> Process Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payment History Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="payment-history-card">
                                <h5 class="card-header bg-light">Payment History</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th>Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="paymentHistory">
                                                @foreach ($invoicePaymentJistory as $payment)
                                                    <tr data-payment-id="{{ $payment['id'] }}">
                                                        <td>{{ date('d-m-Y | h:i A', strtotime($payment['payment_date'])) }}</td>
                                                        <td>{{ $payment['notes'] ?? '' }}</td>
                                                        <td>{{ optional($settings)['currency'] }} {{ number_format($payment['amount'], 2) }}</td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-danger delete-payment"
                                                                    data-payment-id="{{ $payment['id'] }}"
                                                                    title="Delete Payment">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payment record? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Payment Modal Styles */
    #invoicePaymentModal .modal-header {
        padding: 12px 20px;
    }

    #invoicePaymentModal .modal-body {
        padding: 20px;
    }

    .payment-info-card,
    .invoice-info-card,
    .payment-history-card {
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .payment-info-card .card-header,
    .invoice-info-card .card-header,
    .payment-history-card .card-header {
        padding: 10px 15px;
        font-weight: 600;
    }

    .detail-item {
        display: flex;
        margin-bottom: 8px;
    }

    .detail-label {
        font-weight: 500;
        color: #555;
        min-width: 100px;
    }

    .detail-value {
        color: #333;
    }

    #paymentForm label {
        font-weight: 500;
        color: #555;
    }

    .payment-actions {
        display: flex;
        justify-content: space-between;
    }

    #paymentHistory tr:last-child td {
        border-bottom: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        #invoicePaymentModal .modal-dialog {
            margin: 0.5rem auto;
        }

        #invoicePaymentModal .col-md-6 {
            padding: 0;
        }

        #invoicePaymentModal .border-right {
            border-right: none !important;
        }

        .delete-payment {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
