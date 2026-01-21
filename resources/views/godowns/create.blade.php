<!-- Add this modal code right before the closing </div> of content-wrapper -->
<div class="modal fade" id="godownModal" tabindex="-1" role="dialog" aria-labelledby="godownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="godownModalLabel">Add New Godown</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="godownForm" method="POST" action="{{ route('godown.store') }}" autocomplete="off">

                <input type="hidden" id="godown_id" name="godown_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="godown_name" class="font-weight-bold">Godown Name <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                            </div>
                            <input type="text" class="form-control" id="godown_name" name="name"
                                placeholder="Enter godown name" required>
                        </div>
                        <small class="form-text text-muted">e.g. Main Warehouse, Storage Room, etc.</small>
                        <div class="invalid-feedback" id="godown_name_error">Please provide a valid godown name.</div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-power-off"></i></span>
                            </div>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">Set godown availability status</small>
                        <div class="invalid-feedback" id="status_error">Please select a status.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveGodownBtn">
                        <i class="fas fa-save"></i> Save Godown
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Updated CSS -->
<style>
    #godownModal .modal-header {
        padding: 12px 20px;
    }

    #godownModal .modal-content {
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    #godownModal .form-control {
        height: 45px;
        border-radius: 4px;
    }

    #godownModal .input-group-text {
        background-color: #f8f9fa;
    }

    #godownModal .invalid-feedback {
        display: none;
    }

    #godownModal .was-validated .form-control:invalid~.invalid-feedback,
    #godownModal .was-validated select:invalid~.invalid-feedback {
        display: block;
    }

    #saveGodownBtn {
        min-width: 120px;
    }

    /* Status dropdown styling */
    #status {
        cursor: pointer;
    }
</style>
