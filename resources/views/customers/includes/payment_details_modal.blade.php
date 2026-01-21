<div class="modal fade" id="viewCustomerPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="fas fa-user"></i> Customer Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">

                <div class="row">
                    <table class="table table-bordered table-hover table-sm " id="report-data-4">

                        <!-- Total Expenses -->
                        <tbody>

                            <tr>
                                <td class="text-bold font-italic text-primary">Customer Balance</td>
                                <td class="text-right text-bold">{{ @$balance }}</td>
                            </tr>
                            <tr>
                                <td>Voucher#</td>
                                <td class="text-left text-bold">{{ @$customerPayments->invoice_no }}</td>
                            </tr>
                            <tr>
                                <td>Date</td>
                                <td class="text-left text-bold">{{ date('d-m-Y | h:i A', strtotime(@$customerPayments->date)) }}
                                </td>
                            </tr>
                            <tr>
                                <td>Customer Name</td>
                                <td class="text-left text-bold">{{ @$customerPayments->customers->name }}</td>
                            </tr>
                            <tr>
                                <td>Payment Type</td>
                                <td class="text-left text-bold">{{ strtoupper(@$customerPayments->type) }}</td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td class="text-left text-bold">{{ @$customerPayments->amount }}</td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td class="text-left text-bold">{!! @$customerPayments->description !!}</td>
                            </tr>
                            <tr>
                                <td>Created By</td>
                                <td class="text-left text-bold">
                                    <address>{{ @$customerPayments->users->name }}</address>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>

            </div>

        </div>
    </div>
</div>
