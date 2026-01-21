@extends('layouts.layout')
@section('title', '| Edit Purchase')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/sale/sales.css') }}">
    <style>
        .custom-file-label::after {
            content: "Browse";
        }
        .attachment-preview {
            max-width: 200px;
            margin-top: 10px;
        }
        .current-attachment {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
@endsection

<div class="content-wrapper">
    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div id="supplierModalContainer"></div>
            <!-- Form -->
            <form action="{{ route('update.purchase', $updatePurchase['id']) }}" method="post" id="purchaseForm" enctype="multipart/form-data">
                <!-- Invoice Details -->
                <div class="card shadow mt-1">
                    <div class="card-body">
                        <!-- Small Title -->
                        <h5 class="mb-3">Edit Purchase</h5>

                        <!-- Form Fields -->
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="purchase_no">Purchase# <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm text-black"
                                    style="background-color: #D8FDCD;" name="purchase_no" id="purchase_no"
                                    value="{{ $updatePurchase['purchase_no'] }}" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="datepicker">Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm datepicker" name="date"
                                    id="datepicker" placeholder="DD-MM-YYYY"
                                    value="{{ date('d-m-Y', strtotime($updatePurchase['date'])) }}" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="supplierName">Supplier <span class="text-danger">*</span></label>
                                <div class="input-group searchUsers">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Search Supplier" name="supplierName" id="supplierName"
                                        value="{{ optional($updatePurchase['supplier'])['name'] }}">
                                    <input type="hidden" id="supplier_id" name="supplier_id"
                                        value="{{ $updatePurchase['supplier_id'] }}">
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
                                    style="background-color: #D8FDCD;" value="{{ $customerBalance }}"
                                    id="supplierBalance" readonly>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control form-control-sm select2">
                                    <option disabled {{ !isset($updatePurchase['status']) ? 'selected' : '' }}>Select
                                    </option>
                                    @foreach (['cancel' => 'Cancel', 'pending' => 'Pending', 'received' => 'Received'] as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ isset($updatePurchase['status']) && $updatePurchase['status'] == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
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
                            <input type="text" class="form-control" name="barcode" id="searchItem"
                                placeholder="Item name / Barcode / Itemcode">
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
                                @foreach ($enhancedItems as $item)
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control-plaintext text-left"
                                                name="productName[]" value="{{ $item['productName'] }}" readonly>
                                            <input type="hidden" class="productID" name="product_id[]"
                                                value="{{ $item['product_id'] }}">

                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger quantity-decrease">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="text" name="quantity[]"
                                                class="form-control form-control-sm quantity text-center d-inline-block"
                                                value="{{ $item['quantity'] }}" min="1" style="width: 50px;">
                                            <button type="button" class="btn btn-sm btn-success quantity-increase">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <select class="form-control form-control-sm unit-select" name="unit[]">
                                                @foreach ($item['unitInfo'] as $unit)
                                                    <option value="{{ $unit['unit_id'] }}"
                                                        data-purchase-price="{{ $unit['purchase_price'] }}"
                                                        data-selling-price="{{ $unit['selling_price'] }}"
                                                        {{ $unit['unit_id'] == $item['unit_id'] ? 'selected' : '' }}>
                                                        {{ $unit['unit'] }}
                                                        ({{ $unit['purchase_price'] }}/{{ $unit['selling_price'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm text-right unit-price unitPrice"
                                                name="price[]" value="{{ @$item['price'] }}" step="0.01">
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control-plaintext total-amount text-right" name="amount[]"
                                                value="{{ $item['amount'] }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Section -->
                    <div class="row summary-section">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="otherCharges">Other Charges</label>
                                <input type="number" id="otherCharges"
                                    class="form-control form-control-sm text-right"
                                    value="{{ $updatePurchase['other_charges'] }}"
                                    placeholder="Enter additional charges">
                            </div>
                            <div class="form-group">
                                <label for="globalDiscount">Discount</label>
                                <div class="input-group">
                                    <input type="number" id="globalDiscount"
                                        class="form-control form-control-sm text-right" name="discount"
                                        placeholder="Enter discount" value="{{ $updatePurchase['discount'] }}">
                                    <div class="input-group-append">
                                        <select id="discountType" name="discount_type"
                                            class="form-control form-control-sm">
                                            @php
                                                // Map the database values to dropdown labels
                                                $discountTypes = [
                                                    'percentage' => '%',
                                                    'fixed' => 'Fixed',
                                                ];
                                            @endphp

                                            @foreach ($discountTypes as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ isset($updatePurchase['discount_type']) && $updatePurchase['discount_type'] == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="attachment">Attachment (Optional)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                    <label class="custom-file-label" for="attachment">
                                        @if($updatePurchase['attachment'])
                                            Current: {{ $updatePurchase['attachment'] }}
                                        @else
                                            Choose file (JPG, PNG, PDF, DOC)
                                        @endif
                                    </label>
                                </div>
                                <small class="form-text text-muted">Upload original purchase bill (max 5MB)</small>

                                <!-- Current Attachment Preview -->
                                @if($updatePurchase['attachment'])
                                <div class="current-attachment">
                                    <strong>Current Attachment:</strong>
                                    <div class="d-flex align-items-center mt-2">
                                        @if(in_array(pathinfo($updatePurchase['attachment'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ asset('purchase_attachments/' . $updatePurchase['attachment']) }}"
                                                 class="img-thumbnail attachment-preview mr-2"
                                                 alt="Current attachment"
                                                 style="max-height: 100px;">
                                        @else
                                            <i class="fas fa-file-pdf fa-2x text-danger mr-2"></i>
                                        @endif
                                        <div>
                                            <a href="{{ asset('purchase_attachments/' . $updatePurchase['attachment']) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View Current Attachment
                                            </a>
                                            <span class="ml-2">{{ $updatePurchase['attachment'] }}</span>
                                        </div>
                                    </div>
                                    {{-- <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="removeAttachmentCheckbox" name="remove_attachment">
                                        <label class="form-check-label text-danger" for="removeAttachmentCheckbox">
                                            Remove current attachment
                                        </label>
                                    </div> --}}
                                </div>
                                @endif

                                <!-- New Attachment Preview -->
                                <div id="attachmentPreview" class="mt-2 d-none">
                                    <strong>New Attachment Preview:</strong>
                                    <img id="previewImage" class="img-thumbnail attachment-preview d-block mt-2">
                                    <div id="previewFileName" class="mt-1"></div>
                                    <button type="button" id="removeAttachment" class="btn btn-sm btn-danger mt-1">
                                        <i class="fas fa-times"></i> Remove New Attachment
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="note">Notes</label>
                                <textarea id="note" class="form-control form-control-sm" rows="2" name="description"
                                    placeholder="Optional notes">{!! $updatePurchase['description'] !!}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table summary-table">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td><span id="subtotal">{{ $updatePurchase['sub_total'] }}</span><input
                                            type="hidden" id="subtotalVal" name="sub_total"
                                            value="{{ $updatePurchase['sub_total'] }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Other Charges:</th>
                                    <td><span
                                            id="otherChargesTotal">{{ $updatePurchase['other_charges'] }}</span><input
                                            type="hidden" id="otherChargesTotalVal" name="other_charges"
                                            value="{{ $updatePurchase['other_charges'] }}"></td>
                                </tr>
                                <tr>
                                    <th>Discount:</th>
                                    <td><span id="discountTotal">{{ $updatePurchase['discount_amount'] }}</span><input
                                            type="hidden" id="discountVal" name="discount_amount"
                                            value="{{ $updatePurchase['discount_amount'] }}"></td>
                                </tr>
                                <tr>
                                    <th>Grand Total:</th>
                                    <td><strong id="grandTotal">{{ $updatePurchase['grand_total'] }}</strong><input
                                            type="hidden" id="grandTotalVal" name="grand_total"
                                            value="{{ $updatePurchase['grand_total'] }}"></td>
                                </tr>
                            </table>
                            <div class="col-md-12 text-right mt-3">
                                <button type="submit" class="btn btn-success add-sale-button btn-sm"><i
                                        class="fas fa-save"></i>
                                    Update Purchase</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/purchase/new_purchase.js') }}"></script>
<script type="module" src="{{ asset('js/purchase/purchase_calculations.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attachment file input handling
    const attachmentInput = document.getElementById('attachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const previewImage = document.getElementById('previewImage');
    const previewFileName = document.getElementById('previewFileName');
    const removeAttachmentBtn = document.getElementById('removeAttachment');
    const removeAttachmentCheckbox = document.getElementById('removeAttachmentCheckbox');

    // Update file input label
    if (attachmentInput) {
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
                    previewImage.classList.remove('d-none');
                }.bind(this);
                reader.readAsDataURL(this.files[0]);
            } else if (this.files[0]) {
                // For non-image files, show file name only
                attachmentPreview.classList.remove('d-none');
                previewImage.classList.add('d-none');
                previewFileName.textContent = this.files[0].name;
            } else {
                attachmentPreview.classList.add('d-none');
            }
        });
    }

    // Remove new attachment
    if (removeAttachmentBtn) {
        removeAttachmentBtn.addEventListener('click', function() {
            if (attachmentInput) {
                attachmentInput.value = '';
                document.querySelector('.custom-file-label').textContent = 'Choose file (JPG, PNG, PDF, DOC)';
                attachmentPreview.classList.add('d-none');
            }
        });
    }

    // Handle remove attachment checkbox
    if (removeAttachmentCheckbox) {
        removeAttachmentCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Optional: You can add visual feedback when removing current attachment
                document.querySelector('.current-attachment').style.opacity = '0.6';
            } else {
                document.querySelector('.current-attachment').style.opacity = '1';
            }
        });
    }
});
</script>
@endpush
