@extends('layouts.layout')
@section('title', '| Customers')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('css/datatables_styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trash.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* Optimized Receipt Styles */
        .receipt-modal-content { border: none; border-radius: 8px; }
        .receipt-scrollable-container { max-height: 70vh; overflow-y: auto; padding: 0; }
        .receipt-scrollable-container::-webkit-scrollbar { width: 4px; }
        .receipt-scrollable-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .receipt-scrollable-container::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 2px; }

        .receipt-container {
            font-family: "Courier New", monospace;
            font-size: 11px;
            line-height: 1.2;
            max-width: 320px;
            margin: 0 auto;
            padding: 15px;
            background: white;
            border: 1px solid #ddd;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .receipt-header h2 {
            font-weight: bold;
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
        }
        .company-name { font-weight: bold; margin: 2px 0; font-size: 11px; }
        .company-info { margin: 1px 0; color: #666; font-size: 9px; }

        .receipt-details { margin-bottom: 10px; }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            padding: 2px 0;
        }
        .detail-label { font-weight: bold; font-size: 10px; }
        .detail-value { text-align: right; font-size: 10px; }

        .receipt-items {
            margin: 10px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 0;
        }
        .items-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding: 1px 0;
        }
        .item-desc { flex: 2; font-size: 10px; }
        .item-amount { flex: 1; text-align: right; font-weight: bold; font-size: 10px; }

        .notes-section { margin: 10px 0; }
        .notes-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 10px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
        }
        .notes-content { font-size: 10px; text-align: center; padding: 4px 0; font-style: italic; }

        .receipt-totals {
            background: #f5f5f5;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding: 2px 0;
            font-size: 10px;
        }
        .total-row.grand-total {
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin-top: 5px;
            padding: 4px 0;
            font-size: 11px;
        }

        .signature-area {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #999;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            padding-top: 3px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #999;
            color: #666;
            font-size: 8px;
            line-height: 1.1;
        }
        .receipt-footer p { margin: 1px 0; }

        .status-paid {
            background: #000;
            color: white;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            margin-left: 5px;
        }

        /* Print Styles */
        @media print {
            body * { visibility: hidden; }
            .receipt-scrollable-container,
            .receipt-scrollable-container * { visibility: visible; }
            .receipt-scrollable-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-height: none;
                overflow: visible;
            }
            .modal-footer, .modal-header { display: none !important; }
            .receipt-container {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                padding: 10mm !important;
                margin: 0 auto !important;
            }
        }

        /* Modal Button Fix */
        .modal-footer .btn { margin: 0 2px; }
    </style>
@endsection

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <!-- <h1>Customers</h1> -->
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Customers</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div id="customerModalContainer"></div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">View Customers</h3>
                        <a href="javascript:void(0);" data-url="{{ route('load.customer.form') }}"
                            data-saveCustomerUrl="{{ route('create.customer') }}"
                            class="btn btn-block btn-success btn-sm addCustomer"
                            style="width: 150px; float: right; display: inline-block;">Create Customer</a>
                    </div>
                    <div class="card-body">
                        @include('flash_messages')
                        <div class="table-responsive">
                            <table id="customersTable" data-url="{{ route('customers.list') }}"
                                class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Balance</th>
                                        <th>Attachments</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content receipt-modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Payment Receipt</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="receipt-scrollable-container">
                    <div id="receiptContent" class="receipt-container"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                {{-- <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    Close
                </button> --}}
                <button type="button" class="btn btn-sm btn-success" onclick="printReceipt()">
                    Print
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="downloadReceipt()">
                    PDF
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script type="module" src="{{ asset('js/customer/customer.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>

// Fix for modal close issue
document.addEventListener('DOMContentLoaded', function() {
    // Ensure modal close buttons work properly
    const receiptModal = document.getElementById('receiptModal');
    if (receiptModal) {
        receiptModal.addEventListener('hidden.bs.modal', function () {
            const receiptContent = document.getElementById('receiptContent');
            if (receiptContent) {
                receiptContent.innerHTML = '';
            }
        });
    }
});
</script>
@endpush
