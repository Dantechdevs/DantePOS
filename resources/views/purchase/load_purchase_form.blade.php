<div id="supplierModalContainer"></div>
<!-- Form -->
<form action="{{ route('post.purchase') }}" method="post" id="purchaseForm" enctype="multipart/form-data">
    <!-- Invoice Details -->
    <div class="card shadow mt-1">
        <div class="card-body">
            <!-- Small Title -->
            <h5 class="mb-3">New Purchase</h5>

            <!-- Form Fields -->
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="purchase_no">Purchase# <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm text-black"
                        style="background-color: #D8FDCD;" name="purchase_no" id="purchase_no" value="{{ @$purchase_no }}"
                        readonly>
                </div>
                <div class="form-group col-md-3">
                    <label for="datepicker">Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm datepicker" name="date" id="datepicker"
                        placeholder="DD-MM-YYYY" value="{{date('d-m-Y')}}" readonly>
                </div>
                <div class="form-group col-md-3">
                    <label for="supplierName">Supplier <span class="text-danger">*</span></label>
                    <div class="input-group searchUsers">
                        <input type="text" class="form-control form-control-sm" placeholder="Search Supplier"
                            name="supplierName" id="supplierName">
                        <input type="hidden" id="supplier_id" name="supplier_id">
                        <div class="input-group-append input-group-append-btn">
                            <button class="btn btn-sm btn-success addSupplier" type="button"
                                data-url="{{ route('load.supplier.form') }}"
                                data-saveSupplierUrl="{{ route('create.supplier') }}" id="addSupplier">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-2">
                    <label for="supplierBalance">Balance</label>
                    <input type="text" class="form-control form-control-sm text-black"
                        style="background-color: #D8FDCD;" id="supplierBalance" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control form-control-sm select2">
                        <option disabled>Select Status</option>
                        <option value="pending" selected>Pending</option>
                        <option value="received">Received</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="card-body">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                </div>
                <input type="text" class="form-control" name="barcode" id="searchItem" placeholder="Item name / Barcode / Itemcode">
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive table-container">
            <table class="table table-bordered table-sm">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="25%">Item Name</th>
                        <th width="25%">Quantity</th>
                        <th width="10%">Unit</th>
                        <th width="15%">Unit Price</th>
                        <th width="15%">Total Amount</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody id="purchaseItems">
                    <!-- Dynamic rows will be appended here -->
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="row summary-section">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="otherCharges">Other Charges</label>
                    <input type="number" id="otherCharges" class="form-control form-control-sm text-right"
                        placeholder="Enter additional charges" value="0">
                </div>
                <div class="form-group">
                    <label for="globalDiscount">Discount</label>
                    <div class="input-group">
                        <input type="number" id="globalDiscount" class="form-control form-control-sm text-right"
                            name="discount" placeholder="Enter discount" value="0">
                        <div class="input-group-append">
                            <select id="discountType" name="discount_type" class="form-control form-control-sm">
                                <option value="percentage">%</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="attachment">Attachment (Optional)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <label class="custom-file-label" for="attachment">Choose file (JPG, PNG, PDF, DOC)</label>
                    </div>
                    <small class="form-text text-muted">Upload original purchase bill (max 5MB)</small>
                    <div id="attachmentPreview" class="mt-2 d-none">
                        <img id="previewImage" class="img-thumbnail" style="max-height: 150px;">
                        <div id="previewFileName" class="mt-1"></div>
                        <button type="button" id="removeAttachment" class="btn btn-sm btn-danger mt-1">Remove</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="note">Notes</label>
                    <textarea id="note" class="form-control form-control-sm" rows="2" name="description"
                        placeholder="Optional notes"></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <table class="table summary-table">
                    <tr>
                        <th>Subtotal:</th>
                        <td><span id="subtotal">0.00</span><input type="hidden" id="subtotalVal" name="sub_total" value="0">
                        </td>
                    </tr>
                    <tr>
                        <th>Other Charges:</th>
                        <td><span id="otherChargesTotal">0.00</span><input type="hidden" id="otherChargesTotalVal"
                                name="other_charges" value="0"></td>
                    </tr>
                    <tr>
                        <th>Discount:</th>
                        <td><span id="discountTotal">0.00</span><input type="hidden" id="discountVal"
                                name="discount_amount" value="0"></td>
                    </tr>
                    <tr>
                        <th>Grand Total:</th>
                        <td><strong id="grandTotal">0.00</strong><input type="hidden" id="grandTotalVal"
                                name="grand_total" value="0"></td>
                    </tr>
                </table>
                <div class="col-md-12 text-right mt-3">
                    <button type="submit" class="btn btn-success add-sale-button btn-sm"><i class="fas fa-save"></i>
                        Add Purchase</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attachment file input handling
    const attachmentInput = document.getElementById('attachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const previewImage = document.getElementById('previewImage');
    const previewFileName = document.getElementById('previewFileName');
    const removeAttachmentBtn = document.getElementById('removeAttachment');

    // Update file input label
    attachmentInput.addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'Choose file';
        document.querySelector('.custom-file-label').textContent = fileName;

        // Show preview for image files
        if (this.files[0] && this.files[0].type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                attachmentPreview.classList.remove('d-none');
                previewFileName.textContent = this.files[0].name;
            }.bind(this);
            reader.readAsDataURL(this.files[0]);
        } else if (this.files[0]) {
            // For non-image files, show file name
            attachmentPreview.classList.remove('d-none');
            previewImage.classList.add('d-none');
            previewFileName.textContent = this.files[0].name;
        } else {
            attachmentPreview.classList.add('d-none');
        }
    });

    // Remove attachment
    removeAttachmentBtn.addEventListener('click', function() {
        attachmentInput.value = '';
        document.querySelector('.custom-file-label').textContent = 'Choose file (JPG, PNG, PDF, DOC)';
        attachmentPreview.classList.add('d-none');
        previewImage.classList.remove('d-none');
    });

    // Form validation for file size
    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('attachment');
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 5) {
                e.preventDefault();
                alert('File size must be less than 5MB');
                return false;
            }
        }
    });
});
</script>

<style>
.custom-file-label::after {
    content: "Browse";
}
</style>
