<!-- Add this modal code right before the closing </div> of content-wrapper -->
<div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="unitModalLabel">Add New Unit</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="unitForm" method="POST" action="{{ route('unit.store') }}" autocomplete="off">

                <input type="hidden" id="unit_id" name="unit_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="unit_name" class="font-weight-bold">Unit Name <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                            </div>
                            <input type="text" class="form-control" id="unit_name" name="name"
                                placeholder="Enter unit name" required>
                        </div>
                        <div class="invalid-feedback" id="unit_name_error">Please provide a valid unit name.</div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveUnitBtn">
                        <i class="fas fa-save"></i> Save Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Updated CSS -->
<style>
    #unitModal .modal-header {
        padding: 12px 20px;
    }

    #unitModal .modal-content {
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    #unitModal .form-control {
        height: 45px;
        border-radius: 4px;
    }

    #unitModal .input-group-text {
        background-color: #f8f9fa;
    }

    #unitModal .invalid-feedback {
        display: none;
    }

    #unitModal .was-validated .form-control:invalid~.invalid-feedback,
    #unitModal .was-validated select:invalid~.invalid-feedback {
        display: block;
    }

    #saveGodownBtn {
        min-width: 120px;
    }

</style>
