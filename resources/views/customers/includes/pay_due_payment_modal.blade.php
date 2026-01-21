<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="customerPaymentForm" action="{{ route('customer.payment.submit') }}">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> PAYMENT SETTLEMENT
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <!-- Customer Header with Opening Balances -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="font-weight-bold mb-1">Customer: {{ $customer->name }}</h4>
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="text-muted mr-3">Opening Balance:</span>
                                    <span class="font-weight-bold {{ $openingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        PKR {{ number_format(abs($openingBalance), 2) }}
                                        <small>({{ $openingBalance >= 0 ? 'DR' : 'CR' }})</small>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="text-muted mr-3">Opening Balance Due:</span>
                                    {{-- <span class="font-weight-bold {{ $openingBalanceDue >= 0 ? 'text-danger' : 'text-success' }}">
                                        PKR {{ number_format(abs($openingBalanceDue), 2) }}
                                        <small>({{ $openingBalanceDue >= 0 ? 'DR' : 'CR' }})</small>
                                    </span> --}}

                                    <span class="font-weight-bold text-success {{ $dueOP_Balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        PKR
                                        {{ number_format(abs($dueOP_Balance),2) }}
                                        <small>({{ $dueOP_Balance >= 0 ? 'DR' : 'CR' }})</small>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-danger p-2" style="font-size: 1rem;">
                                Balance: {{ number_format($balance, 2) }}
                            </span>
                            <div class="text-muted small mt-1">As of {{ date('d-M-Y') }}</div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="border p-3 rounded">
                                <h6 class="text-uppercase text-muted small font-weight-bold">Total Sales</h6>
                                <h3 class="font-weight-bold mb-0">PKR {{ number_format($totalSales) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border p-3 rounded">
                                <h6 class="text-uppercase text-muted small font-weight-bold">Paid Amount</h6>
                                <h3 class="font-weight-bold text-success mb-0">PKR {{ number_format($paidAmount) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border p-3 rounded">
                                <h6 class="text-uppercase text-muted small font-weight-bold">Sales Due</h6>
                                <h3 class="font-weight-bold text-danger mb-0">PKR {{ number_format($dueSalesAmount) }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div class="bg-light p-4 rounded">
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3">PAYMENT DETAILS</h6>

                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Payment Amount (PKR)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-money-bill-wave"></i></span>
                                    </div>
                                    <input type="number" name="amount" class="form-control form-control-sm"
                                           value="{{ $dueAmount }}" step="0.01" max="{{ $dueAmount }}">
                                </div>
                                <small class="form-text text-muted">Maximum payable: PKR {{ number_format($dueAmount, 2) }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Discount Amount (PKR)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-tag"></i></span>
                                    </div>
                                    <input type="number" name="discount_amount" class="form-control form-control-sm"
                                           value="0" step="0.01" min="0" max="{{ $dueAmount }}" id="discountAmount">
                                </div>
                                <small class="form-text text-muted">Maximum discount: PKR {{ number_format($dueAmount, 2) }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Payment Date</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" class="form-control" id="paymentDate" name="payment_date"
                                        value="{{ date('Y-m-d') }}">
                                    {{-- <input type="date" name="payment_date" class="form-control form-control-lg"
                                           value="{{ date('Y-m-d') }}" readonly> --}}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                            <div class="form-group mb-0">
                            <label class="font-weight-bold">Payment Reference</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Enter payment notes or reference"></textarea>
                        </div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check mr-2"></i> Process Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-lg {
        max-width: 800px;
    }
    .modal-header {
        padding: 1.25rem 2rem;
        border-bottom: none;
    }
    .modal-title {
        font-size: 1.35rem;
        letter-spacing: 0.5px;
    }
    .modal-body {
        padding: 2rem;
    }
    .modal-footer {
        padding: 1.25rem 2rem;
    }
    .form-control-lg {
        height: calc(2.875rem + 2px);
        font-size: 1.1rem;
    }
    .bg-gradient-primary {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
    }
    .border {
        border: 1px solid #e9ecef !important;
    }
    .rounded {
        border-radius: 0.375rem !important;
    }
    .badge-danger {
        background-color: #f5365c;
    }
</style>

<script>
    $(document).ready(function() {
        // Amount validation
        $('input[type="number"]').on('change', function() {
            const maxAmount = parseFloat($(this).attr('max'));
            const enteredAmount = parseFloat($(this).val());

            if (enteredAmount > maxAmount) {
                alert('Payment amount cannot exceed due amount of PKR ' + maxAmount.toFixed(2));
                $(this).val(maxAmount.toFixed(2));
            }
        });
    });
</script>
