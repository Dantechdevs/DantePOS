<!-- Add this modal code right before the closing </div> of content-wrapper -->
<div class="modal fade" id="areaModal" tabindex="-1" role="dialog" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="areaModalLabel">Add New Area</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="areaForm" method="POST" action="{{ route('area.store') }}" autocomplete="off">

                <input type="hidden" id="area_id" name="area_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="area_name" class="font-weight-bold">Area Name <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                            </div>
                            <input type="text" class="form-control" id="area_name" name="name"
                                placeholder="Enter area name" required>
                        </div>
                        <div class="invalid-feedback" id="area_name_error">Please provide a valid area name.</div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveAreaBtn">
                        <i class="fas fa-save"></i> Save Area
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Updated CSS -->
<style>
    #areaModal .modal-header {
        padding: 12px 20px;
    }

    #areaModal .modal-content {
        border: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    #areaModal .form-control {
        height: 45px;
        border-radius: 4px;
    }

    #areaModal .input-group-text {
        background-color: #f8f9fa;
    }

    #areaModal .invalid-feedback {
        display: none;
    }

    #areaModal .was-validated .form-control:invalid~.invalid-feedback,
    #areaModal .was-validated select:invalid~.invalid-feedback {
        display: block;
    }

    #saveAreaBtn {
        min-width: 120px;
    }

</style>
