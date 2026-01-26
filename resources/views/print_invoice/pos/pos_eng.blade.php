<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 76mm;
            /* Reduced width to minimize margins */
            margin: 0 auto;
            font-family: Arial, sans-serif;
            font-size: 14px;
            /* Increased base font size */
            color: #000;
            background-color: #fff;
            line-height: 1.2;
        }

        .container {
            padding: 1mm 2mm;
            /* Reduced padding */
        }

        .header {
            text-align: center;
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #000;
        }

        .office-details {
            text-align: center;
            font-size: 10px;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px dashed #ccc;
        }

        .office-details a {
            color: #000;
            text-decoration: none;
        }

        .header {
            text-align: center;
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #000;
        }

        .header .logo-container {
            width: 100%;
            margin: 0;
            padding: 0;
            text-align: center;
            overflow: hidden;
            /* Prevent any overflow */
        }

        .header .logo-container img {
            width: 100%;
            max-width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0;
            padding: 0;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            padding: 0 2mm;
            /* Add back padding for text content */
            font-weight: bold;
        }

        .header p {
            font-size: 12px;
            margin: 1px 0;
            padding: 0 2mm;
            /* Add back padding for text content */
        }

        .details {
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #000;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
        }

        .details-left,
        .details-right {
            width: 48%;
        }

        .details p {
            margin: 2px 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 12px;
        }

        table th,
        table td {
            padding: 2px;
            border: 1px dotted #000;
            word-wrap: break-word;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 12px;
        }

        .totals {
            margin-top: 3px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .qr-code img {
            width: 50px;
            height: 50px;
        }

        .summary {
            text-align: right;
        }

        .summary p {
            margin: 2px 0;
            font-size: 12px;
        }

        footer {
            text-align: center;
            font-size: 11px;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px dashed #000;
        }

        .note {
            font-size: 11px;
            margin: 3px 0;
            padding: 2px;
            border-top: 1px dashed #000;
        }

        /* Print button styles (only visible on screen) */
        .print-button-container {
            text-align: center;
            margin: 5px 0;
        }

        .print-button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Hide print button in print output */
        @media print {
            .print-button-container {
                display: none;
            }
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 76mm;
            }

            .container {
                padding: 1mm 2mm;
            }

            /* Ensure header extends full width in print */
            .header {
                margin: 0 -2mm;
                width: 76mm;
                /* Full paper width for print */
            }

            @page {
                size: 76mm auto;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <?php
    // echo "<pre>"; print_r($customerBalance); "</pre>"; exit;
    ?>
    <div class="container">
        <!-- Header -->
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                    ? asset(optional($settings)['invoice_logo'])
                    : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                        ? asset(optional($settings)['default_image'])
                        : asset('images/default-image.png')) }}"
                    alt="Logo">
            </div>
            <h1>{{ optional($settings)['site_name'] }}</h1>
            <p>{{ optional($settings)['site_address'] }}</p>
            <p>{{ optional($settings)['mobile_numbers'] }}</p>
        </div>



        <!-- Invoice Details -->
        <div class="details">
            <div class="details-row">
                <div class="details-left">
                    @if (@$saleInvoice['customer'] && !empty(@$saleInvoice['customer']['name']))
                        <p><strong>Customer:</strong> {{ optional($saleInvoice['customer'])['name'] ?? 'N/A' }}</p>
                    @endif
                    @if (@$saleInvoice['customer'] && !empty(@$saleInvoice['customer']['mobile']))
                        <p><strong>Mobile:</strong> {{ optional($saleInvoice['customer'])['mobile'] ?? 'N/A' }}</p>
                    @endif

                </div>

                <div class="details-right">
                    <p><strong>Invoice:</strong> {{ $saleInvoice['invoice_no'] }}</p>
                    <p><strong>Date:</strong> {{ date('d/m/Y h:i A', strtotime($saleInvoice['date'])) }}</p>
                    <p><strong>Status:</strong>
                        {{ $saleInvoice['status'] == 0
                            ? 'Canceled'
                            : ($saleInvoice['status'] == 1
                                ? 'Billed'
                                : ($saleInvoice['status'] == 2
                                    ? 'Pending'
                                    : ($saleInvoice['status'] == 3
                                        ? 'Return'
                                        : 'Unknown'))) }}
                    </p>

                </div>
            </div>
        </div>

        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th width="8%">#</th>
                    <th width="42%">Product</th>
                    <th width="15%">Qty</th>
                    <th width="15%">Price</th>
                    <th width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 0; @endphp
                @foreach ($saleitmesAddons as $addons)
                    @php
                        $counter = $counter + 1;
                        $productName = App\Models\Product::find($addons['product_id'])->name ?? 'Unknown Product';
                    @endphp
                    <tr>
                        <td>{{ $counter }}</td>
                        <td>{{ $productName }}</td>
                        <td style="text-align: center;">
                            {{ $addons['quantity'] }} {{ $addons['unit'] }}
                        </td>
                        <td style="text-align: right;">{{ number_format($addons['selling_price'], 2) }}</td>
                        <td style="text-align: right;">{{ number_format($addons['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals and QR Code -->
        <div class="totals">
            <div class="qr-code">
                @php
                    $url = route('print.invoice', $saleInvoice['invoice_no']);
                @endphp
                {{-- <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data={{ urlencode($url) }}" alt="QR"> --}}
            </div>
            <div class="summary">
                @if ($saleInvoice['sub_total'] > 0 && ($saleInvoice['discount'] > 0 || $saleInvoice['other_charges'] > 0))
                    <p><strong>Sub Total:</strong> {{ number_format($saleInvoice['sub_total'], 2) }}</p>
                @endif
                @if ($saleInvoice['other_charges'] > 0)
                    <p><strong>Other Charges:</strong> {{ number_format($saleInvoice['other_charges'], 2) }}</p>
                @endif
                @if ($saleInvoice['discount'] > 0)
                    <p><strong>Discount:</strong>
                        @if ($saleInvoice['discount_type'] === 'percentage')
                            ({{ $saleInvoice['discount'] }}%)
                        @else
                            (Fixed)
                        @endif
                        {{ number_format($saleInvoice['discount_amount'], 2) }}
                    </p>
                @endif
                <p><strong>TOTAL:</strong> {{ number_format($saleInvoice['grand_total'], 2) }}</p>

                @if ($saleInvoice['paid_amount'] > 0)
                    <p><strong>Paid:</strong> {{ number_format($saleInvoice['paid_amount'], 2) }}</p>
                @endif
                @if ($saleInvoice['change_amount'] > 0)
                    <p><strong>Refund:</strong> {{ number_format($saleInvoice['change_amount'], 2) }}</p>
                @endif
                @if ($saleInvoice['balance_amount'] > 0)
                    <p><strong>Due:</strong> {{ number_format($saleInvoice['balance_amount'], 2) }}</p>
                @endif
                @if ($customerBalance > 0)
                    @php

                        $preblnc = $customerBalance;
                    @endphp

                    <p><strong>Previous Balance:</strong> (+{{ number_format($preblnc, 2) }})</p>
                @endif
                @if ($customerBalance > 0)
                    @php

                        $customerBalance =
                            $customerBalance +
                            ($saleInvoice['balance_amount'] > 0 ? $saleInvoice['balance_amount'] : 0);
                    @endphp
                    <p><strong>Total Balance:</strong> {{ number_format($customerBalance, 2) }}</p>
                @endif

            </div>
        </div>

        @if ($saleInvoice['description'])
            <div class="note">
                <p><strong>Note:</strong> {{ $saleInvoice['description'] }}</p>
            </div>
        @endif

        <!-- Footer -->
        <footer>
            <p>{{ optional($settings)['footer_text'] }}</p>
        </footer>

        <!-- Office Details -->
        <div class="office-details">
            <p>Developed by: <strong><i>Dantechdevs - IT Company</i></strong></p>
            <p>Website: <a href="https://dantechdevelopers.com">https://dantechdevelopers.com</a></p>
            <p>Mobile: +254712328150</p>
        </div>
    </div>

    <!-- Print Button (visible only on screen) -->
    <div class="print-button-container">
        <button class="print-button" onclick="window.print()">
            <i class="fas fa-print"></i> Print Invoice
        </button>
    </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Check if we're not already in print preview
            if (!window.matchMedia || !window.matchMedia('print').matches) {
                window.print();
            }
        };

        // Add Font Awesome for the print icon (only needed if not already included)
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const fa = document.createElement('link');
            fa.rel = 'stylesheet';
            fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
            document.head.appendChild(fa);
        }
    </script>

    {{-- <script>
        window.onload = function() {
            window.print();
        };
    </script> --}}
</body>

</html>
