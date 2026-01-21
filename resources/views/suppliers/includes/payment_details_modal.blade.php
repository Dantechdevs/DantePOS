<div class="modal fade" id="viewSupplierPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addSupplierModalLabel">
                    <i class="fas fa-user"></i> Supplier Details
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
                                <td class="text-bold font-italic text-primary">Supplier Balance</td>
                                <td class="text-right text-bold">{{ @$balance }}</td>
                            </tr>
                            <tr>
                                <td>Voucher#</td>
                                <td class="text-left text-bold">{{ @$supplierPayment->purchase_no }}</td>
                            </tr>
                            <tr>
                                <td>Date</td>
                                <td class="text-left text-bold">{{ date('d-m-Y | h:i A', strtotime(@$supplierPayment->date)) }}
                                </td>
                            </tr>
                            <tr>
                                <td>Supplier Name</td>
                                <td class="text-left text-bold">{{ @$supplierPayment->supplier->name }}</td>
                            </tr>

                            <tr>
                                <td>Amount</td>
                                <td class="text-left text-bold">{{ @$supplierPayment->amount }}</td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td class="text-left text-bold">{!! @$supplierPayment->description !!}</td>
                            </tr>
                            <tr>
                                <td>Created By</td>
                                <td class="text-left text-bold">
                                    <address>{{ @$supplierPayment->users->name }}</address>
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
