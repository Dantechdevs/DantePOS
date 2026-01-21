@extends('layouts.layout')
@section('title', '| Create Quotation')
@section('content')

@section('custom_styles')
<style>
    .quotation-wrapper {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        padding: 2rem;
    }

    .section-header {
        border-left: 4px solid #3f80ea;
        padding-left: 1rem;
        margin: 1.5rem 0;
    }

    .section-header h6 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .form-control-sm {
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .search-container {
        position: relative;
        margin: 1.5rem 0;
    }

    .search-results {
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.05);
        background: #fff;
        margin-top: 0.5rem;
    }

    .product-table {
        border: 1px solid #f0f0f0;
    }

    .product-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
    }

    .total-summary {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
    }

    .quantity-input {
        width: 80px;
        text-align: center;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 0.3rem;
    }

    .btn-action {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
    }

</style>
@endsection

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="quotation-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">New Quotation</h4>
                    <small class="text-muted">Create professional customer quotations</small>
                </div>
                <div>
                    <button type="reset" class="btn btn-light btn-action">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary btn-action">
                        <i class="fas fa-save mr-1"></i>Save
                    </button>
                </div>
            </div>

            <form id="quotationForm" method="POST" action="">
                @csrf

                <!-- Customer Section -->
                <div class="section-header">
                    <h6>Customer Details</h6>
                    <small class="text-muted">Basic customer information</small>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm"
                               placeholder="Customer name" required>
                    </div>
                    <div class="col-md-4">
                        <input type="email" class="form-control form-control-sm"
                               placeholder="Email address">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm"
                               placeholder="Phone number">
                    </div>
                </div>

                <!-- Product Search -->
                <div class="section-header">
                    <h6>Product Selection</h6>
                    <small class="text-muted">Add products to quotation</small>
                </div>
                <div class="search-container">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="productSearch"
                               class="form-control form-control-sm"
                               placeholder="Search products by name or code..."
                               data-search-url="{{ route('search.raw.product') }}">
                    </div>
                    <div class="search-results" id="searchResults"></div>
                </div>

                <!-- Selected Products -->
                <div class="mb-4">
                    <table class="table product-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                                <th class="text-center" style="width: 40px"></th>
                            </tr>
                        </thead>
                        <tbody id="selectedProductsBody">
                            <!-- Products will be added here -->
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="section-header">
                    <h6>Pricing Summary</h6>
                    <small class="text-muted">Final quotation calculation</small>
                </div>
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="total-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (16%):</span>
                                <span id="tax">$0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Grand Total:</span>
                                <span id="grandTotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('custom-script')
<script src="{{ asset('js/quotation/quotation.js') }}?v=1.3"></script>
@endpush
