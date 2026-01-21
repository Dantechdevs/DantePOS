<div class="modal fade" id="viewCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel"
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
                                <td class="text-right text-bold">{{@$balance}}</td>
                            </tr>
                            <tr>
                                <td>Customer Name</td>
                                <td class="text-left text-bold">{{@$customer->name}}</td>
                            </tr>
                            <tr>
                                <td>Customer Name Urdu</td>
                                <td class="text-right text-bold">{{@$customer->name_ur}}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td class="text-left text-bold">{{@$customer->email}}</td>
                            </tr>
                            <tr>
                                <td>Mobile</td>
                                <td class="text-left text-bold">{{@$customer->mobile}}</td>
                            </tr>
                            <tr>
                                <td>National ID</td>
                                <td class="text-left text-bold">{{@$customer->national_id}}</td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td class="text-left text-bold">
                                    <address>{!!@$customer->address!!}</address>
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
