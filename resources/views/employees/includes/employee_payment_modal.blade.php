<style>
    /* Make the modal body vertically scrollable */
    .modal-dialog {
        max-height: 90%;
        /* Adjust as needed */
        display: flex;
        flex-direction: column;
    }

    .modal-content {
        flex-grow: 1;
        overflow-y: auto;
    }

    /* Adjust table to remove horizontal scroll */
    table.dataTable {
        table-layout: auto;
        /* Ensure flexible column widths */
        width: 100%;
        /* Ensure the table fits its container */
    }

    .dataTables_wrapper .row {
        margin-left: 0;
        margin-right: 0;
    }

    /* Body blur effect when the modal is open */
    body.modal-open {
        overflow: hidden;
        /* Disable scrolling on the background */
    }

    body.modal-open .blur-background {
        filter: blur(8px);
        /* Adjust the blur intensity */
        pointer-events: none;
        /* Prevent interactions with the blurred content */
    }

    /* Modal fade-in animation */
    .modal.fade .modal-dialog {
        transform: translateY(-50px);
        opacity: 0;
        transition: all 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
        opacity: 1;
    }

    /* Ensure modal content is sharp and not blurred */
    .modal-content {
        filter: none;
        z-index: 1050;
        /* Ensures modal content is above blurred background */
    }

    /* Darken the modal backdrop for better focus */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
        /* Semi-transparent backdrop */
        backdrop-filter: blur(3px);
        /* Subtle blur for backdrop */
    }
</style>
<!-- Modal -->
<div class="modal fade" id="advancePaymentModal" tabindex="-1" role="dialog" aria-labelledby="advancePaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="advancePaymentModalLabel">Employee Payment Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form -->
                <form id="employeePaymentForm" class="mb-4">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="date">Date</label>
                            <input type="text" class="form-control" value="{{ date('d-m-Y') }}" id="datepicker"
                                name="date" required readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount"
                                placeholder="Enter amount" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="note">Description</label>
                        <div class="input-group">
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Add description"></textarea>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Responsive Table -->
                <div>
                    <h5 class="text-primary" id="tableHeading"></h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="employeeTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Descrition</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
