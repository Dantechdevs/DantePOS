<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Sale Invoices</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: var(--light-gray);
            line-height: 1.5;
        }

        .container {
            width: 210mm;
            max-width: 210mm;
            margin: 10mm auto;
            padding: 15px 25px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            position: relative;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .header .logo img {
            max-height: 80px;
            width: auto;
        }

        .header .title {
            text-align: right;
        }

        .header .title h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .title p {
            font-size: 12px;
            margin: 0;
            color: var(--dark-gray);
        }

        .company-info {
            text-align: left;
            margin-bottom: 5px;
        }

        .company-info strong {
            display: block;
            font-size: 16px;
            color: var(--secondary-color);
            margin-bottom: 3px;
        }

        .invoice-meta {
            background-color: var(--light-gray);
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .invoice-meta .left-section,
        .invoice-meta .right-section {
            width: 48%;
        }

        .invoice-meta p {
            margin: 5px 0;
            font-size: 13px;
        }

        .invoice-meta strong {
            font-weight: 500;
            color: var(--secondary-color);
            min-width: 120px;
            display: inline-block;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .table th {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 500;
            padding: 10px 12px;
            text-align: left;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--medium-gray);
            vertical-align: top;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .totals-table {
            width: 300px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            overflow: hidden;
        }

        .totals-table tr:last-child {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 500;
        }

        .totals-table td {
            padding: 8px 15px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid var(--medium-gray);
            font-size: 11px;
            color: var(--dark-gray);
            text-align: center;
        }

        .qr-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }

        .qr-code {
            text-align: center;
        }

        .qr-code img {
            width: 80px;
            height: 80px;
            border: 1px solid var(--medium-gray);
            padding: 5px;
            background: white;
        }

        .qr-code p {
            margin-top: 5px;
            font-size: 10px;
            color: var(--dark-gray);
        }

        .notes {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-left: 3px solid var(--primary-color);
            font-size: 12px;
        }

        .notes strong {
            color: var(--secondary-color);
        }

        .office-details {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            color: var(--dark-gray);
        }

        .office-details a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-billed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-return {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .payment-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            background-color: #e2e3e5;
            color: #383d41;
            text-transform: uppercase;
        }

        @media print {
            body {
                background-color: white;
                font-size: 13px;
            }

            .container {
                box-shadow: none;
                padding: 10px 15px;
                margin: 0;
                width: 100%;
            }

            .header {
                margin-bottom: 15px;
            }

            .table th {
                background-color: var(--secondary-color) !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            .totals-table tr:last-child {
                background-color: var(--secondary-color) !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            footer {
                position: fixed;
                bottom: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    @foreach ($invoices as $data)
        @php
            $saleInvoice = $data['saleInvoice'];
            $saleitmesAddons = $data['saleitmesAddons'];
            $customerBalance = $data['customerBalance'];

            if (!empty($saleInvoice['payment_type']) && $saleInvoice['payment_type'] == 'credit') {
                $previous_balance = $customerBalance - $saleInvoice['grand_total'];
            } else {
                $previous_balance = $customerBalance;
            }

            $statusClass = '';
            $statusText = '';
            switch ($saleInvoice['status']) {
                case 0:
                    $statusClass = 'status-canceled';
                    $statusText = 'Canceled';
                    break;
                case 1:
                    $statusClass = 'status-billed';
                    $statusText = 'Billed';
                    break;
                case 2:
                    $statusClass = 'status-pending';
                    $statusText = 'Pending';
                    break;
                case 3:
                    $statusClass = 'status-return';
                    $statusText = 'Return';
                    break;
                default:
                    $statusClass = '';
                    $statusText = 'Unknown';
            }
        @endphp

        <div class="container">
            <!-- Header -->
            <h1 class="text-center" style="font-size: 16px; font-weight:bold;">SALE INVOICE</h1>
            <div class="header">
                <div class="logo">
                    <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                        ? asset(optional($settings)['invoice_logo'])
                        : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                            ? asset(optional($settings)['default_image'])
                            : asset('images/default-image.png')) }}"
                        alt="Company Logo">
                </div>
                <div class="title">

                    <div class="company-info">
                        <strong>{{ optional($settings)['site_name'] }}</strong>
                        <p>{{ optional($settings)['site_address'] }}</p>
                        <p>{{ $settings['mobile_numbers'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Meta -->
            <div class="invoice-meta">
                <div class="left-section">
                    <p><strong>Invoice #:</strong> {{ $saleInvoice['invoice_no'] }}</p>
                    <p><strong>Date:</strong> {{ date('d M, Y | h:i A', strtotime($saleInvoice['date'])) }}</p>
                    <p><strong>Status:</strong> <span
                            class="status-badge {{ $statusClass }}">{{ $statusText }}</span></p>
                </div>
                <div class="right-section">
                    <p><strong>Customer:</strong> {{ optional($saleInvoice['customer'])['name'] }}</p>
                    @if (@$saleInvoice['customer'] && !empty(@$saleInvoice['customer']['mobile']))
                        <p><strong>Phone:</strong> {{ optional($saleInvoice['customer'])['mobile'] }}</p>
                    @endif


                </div>
            </div>

            <!-- Products Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 0; @endphp
                    @foreach ($saleitmesAddons as $addons)
                        @php
                        $counter++;
                            $productName = App\Models\Product::find($addons['product_id'])->name ?? 'Unknown Product';
                        @endphp
                        <tr>
                            <td>{{ $counter }}</td>
                            <td>
                                <strong>{{ $productName }}</strong>
                                @if (!empty($addons['description']))
                                    <br><small>{{ $addons['description'] }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $addons['quantity'] }} {{ $addons['unit'] }}</td>
                            <td class="text-right">{{ number_format($addons['selling_price'], 2) }}</td>
                            <td class="text-right">{{ number_format($addons['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals Section -->
            <div class="totals-section">
                <table class="totals-table">
                    @if ($saleInvoice['sub_total'] > 0 && ($saleInvoice['discount'] > 0 || $saleInvoice['other_charges'] > 0))
                        <tr>
                            <td><strong>Sub Total:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['sub_total'], 2) }}</td>
                        </tr>
                    @endif
                    @if ($saleInvoice['other_charges'] > 0)
                        <tr>
                            <td><strong>Other Charges:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['other_charges'], 2) }}</td>
                        </tr>
                    @endif
                    @if ($saleInvoice['discount'] > 0)
                        <tr>
                            <td>
                                <strong>Discount:</strong>
                                @if (isset($saleInvoice['discount_type']))
                                    <small>({{ ucfirst($saleInvoice['discount_type']) }})</small>
                                @endif
                            </td>
                            <td class="text-right">
                                @if (isset($saleInvoice['discount_type']) && $saleInvoice['discount_type'] === 'percentage')
                                    ({{ number_format($saleInvoice['discount']) }}%)
                                    {{ optional($settings)['currency'] }}
                                    {{ number_format($saleInvoice['discount_amount'], 2) }}
                                @else
                                    {{ optional($settings)['currency'] }}
                                    {{ number_format($saleInvoice['discount'], 2) }}
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>Total:</strong></td>
                        <td class="text-right">{{ optional($settings)['currency'] }}
                            {{ number_format($saleInvoice['grand_total'], 2) }}</td>
                    </tr>
                    @if ($saleInvoice['paid_amount'] > 0)
                        <tr>
                            <td><strong>Paid:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['paid_amount'], 2) }}</td>
                        </tr>
                    @endif
                    @if ($saleInvoice['change_amount'] > 0)
                        <tr>
                            <td><strong>Refund:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['change_amount'], 2) }}</td>
                        </tr>
                    @endif
                    @if ($saleInvoice['balance_amount'] > 0)
                        <tr>
                            <td><strong>Due:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['balance_amount'], 2) }}</td>
                        </tr>
                    @endif

                    @if ($customerBalance > 0)
                        @php

                            $preblnc = $customerBalance;
                        @endphp
                        <tr>
                            <td><strong>Previous Balance:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($preblnc, 2) }}</td>
                        </tr>
                    @endif
                    @if ($customerBalance > 0)
                        @php

                            $customerBalance =
                                $customerBalance +
                                ($saleInvoice['balance_amount'] > 0 ? $saleInvoice['balance_amount'] : 0);
                        @endphp
                        <tr>
                            <td><strong>Total Balance:</strong></td>
                            <td class="text-right">{{ optional($settings)['currency'] }}
                                {{ number_format($customerBalance, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <!-- QR Code and Notes -->
            <div class="qr-section">
                <div class="notes">
                    @if ($saleInvoice['description'])
                        <strong>Notes:</strong> {{ $saleInvoice['description'] }}
                    @endif
                </div>
                <div class="qr-code">
                    {{-- @php
                    $url = route('print.invoice', $saleInvoice['invoice_no']);
                @endphp
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($url) }}" alt="QR Code">
                <p>Scan to verify invoice</p> --}}
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>{{ optional($settings)['footer_text'] }}</p>
            </div>

            <!-- Office Details -->
            <div class="office-details">
                <p>Invoice generated on {{ date('d M Y H:i') }} | Developed by: <strong>Devpeller - Software
                        Solutions</strong></p>
                <p>Website: <a href="https://devpeller.com">https://devpeller.com</a> | Mobile: 0336-6667686</p>
            </div>
        </div>

        <!-- Page break for next invoice -->
        <div style="page-break-after: always;"></div>
    @endforeach

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
