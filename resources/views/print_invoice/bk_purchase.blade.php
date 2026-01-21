<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>{{ $purchaseInvoice['customers']['name'] ?? '' }} - Sale Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .container {
            width: 210mm;
            /* A5 width */
            max-width: 210mm;
            margin: 10mm auto;
            /* Center the content */
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }

        .header .logo img {
            max-width: 100px;
        }

        .header .title {
            text-align: center;
            flex-grow: 1;
        }

        .header .title h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .header .title p {
            font-size: 12px;
            margin: 0;
            color: #666;
        }

        .details-row {
            margin-bottom: 20px;
        }

        .details-row p {
            margin: 3px 0;
        }

        .details-row strong {
            font-weight: bold;
        }

        .invoice-details {
            margin-bottom: 20px;
            font-size: 12px;
            line-height: 1.2;
        }

        .invoice-details p {
            margin: 0;
        }

        .table {
            font-size: 12px;
        }

        .table th,
        .table td {
            padding: 8px;
            vertical-align: middle;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .totals-row {
            font-weight: bold;
        }

        .totals {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .totals .qr-code {
            margin-right: 20px;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        @media print {
            body {
                margin-left: 10mm;
                background-color: #fff;
            }

            .container {
                border: none;
                box-shadow: none;
            }

            footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                    ? asset(optional($settings)['invoice_logo'])
                    : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                        ? asset(optional($settings)['default_image'])
                        : asset('images/default-image.png')) }}"
                    alt="Logo">
            </div>

            <div class="title">
                <h1>INVOICE</h1>
                <p>{{ optional($settings)['site_name'] }}, {{ optional($settings)['site_address'] }} <br>
                    {{ $settings['mobile_numbers'] }}
                </p>

            </div>
        </div>

        <!-- Company and Customer Details -->
        <!-- Invoice Details -->
        <div class="invoice-details">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <!-- Left Section: Invoice Number and Date -->
                <div>
                    <p><strong>Invoice #:</strong> {{ $purchaseInvoice['invoice_no'] }}</p>
                    <p><strong>Date:</strong> {{ date('d/m/Y | h:i A', strtotime($purchaseInvoice['date'])) }}</p>
                    <p><strong>Status:</strong>
                        {{ $purchaseInvoice['status'] == 'cancel'
                                ? 'Canceled'
                                : ($purchaseInvoice['status'] == 'received'
                                    ? 'Received'
                                    : ($purchaseInvoice['status'] == 'pending'
                                        ? 'Pending'
                                        : 'Unknown')) }}
                    </p>
                </div>

                <!-- Right Section: Supplier Details -->
                <div style="text-align: right;">
                    <p><strong>Supplier:</strong> {{ optional($purchaseInvoice['supplier'])['name'] }}</p>
                    <p><strong>Phone:</strong> {{ optional($purchaseInvoice['supplier'])['mobile'] }}</p>
                    <address><strong>Phone:</strong> {{ optional($purchaseInvoice['supplier'])['address'] }}</address>
                </div>
            </div>
        </div>


        <!-- Products Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Item Total</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 0; @endphp
                @foreach ($purchaseitmesAddons as $addons)
                    @php
                        $counter = $counter + 1;

                    @endphp
                    <tr>
                        <td>{{ $counter }}</td>
                        <td>{{ $addons['productName'] }}</td>
                        <td style="text-align: center;">
                            {{ $addons['quantity'] }} {{ $addons['unit'] }}
                        </td>
                        <td style="text-align: right;">{{ number_format($addons['price']) }}</td>
                        <td style="text-align: right;">{{ number_format($addons['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals and QR Code -->
        <div class="totals">
            <div class="qr-code">
                @php
                    $url = route('print.purchase.invoice', $purchaseInvoice['purchase_no']);
                @endphp
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ urlencode($url) }}"
                    alt="QR Code">
            </div>
            <div>
                <table style="width: 100%; margin-top: 10px;">
                    <tbody>
                        @if ($purchaseInvoice['sub_total'] > 0)
                            <tr>
                                <td style="text-align: left;"><strong>Sub Total:</strong></td>
                                <td style="text-align: right;">{{ number_format($purchaseInvoice['sub_total']) }}</td>
                            </tr>
                        @endif
                        @if ($purchaseInvoice['other_charges'] > 0)
                            <tr>
                                <td style="text-align: left;"><strong>Other Charges:</strong></td>
                                <td style="text-align: right;">{{ number_format($purchaseInvoice['other_charges']) }}</td>
                            </tr>
                        @endif
                        @if ($purchaseInvoice['discount'] > 0)
                            <tr>
                                <td style="text-align: left;">
                                    <strong>Discount:</strong>
                                    @if (isset($purchaseInvoice['discount_type']))
                                        ({{ ucfirst($purchaseInvoice['discount_type']) }})
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if (isset($purchaseInvoice['discount_type']) && $purchaseInvoice['discount_type'] === 'percentage')
                                        ({{ number_format($purchaseInvoice['discount']) }}%)
                                        {{ number_format($purchaseInvoice['discount_amount']) }}
                                    @else
                                        {{ number_format($purchaseInvoice['discount']) }}
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="text-align: left;"><strong>Grand Total:</strong></td>
                            <td style="text-align: right;">{{ number_format($purchaseInvoice['grand_total']) }}</td>
                        </tr>
                        @if ($supplierBalance > 0)
                            @php
                                $previousBalance = 0;
                                // $previousBalance = $supplierBalance - $purchaseInvoice['grand_total'];
                                $status = $supplierBalance['status'] ?? null; // Ensure 'status' exists
                                $previousBalance =
                                    $status === 1
                                        ? $supplierBalance - $purchaseInvoice['grand_total']
                                        : $supplierBalance - $purchaseInvoice['grand_total'];
                            @endphp

                            <tr>
                                <td style="text-align: left;"><strong>Previous Balance:</strong></td>
                                <td style="text-align: right;">
                                    {{ $previousBalance < 0 ? -1 * $previousBalance . ' CR' : $previousBalance . ' DB' }}
                                </td>
                            </tr>


                            <tr>
                                <td style="text-align: left;"><strong>Total Balance:</strong></td>
                                <td style="text-align: right;">
                                    {{ optional($settings)['currency'] }}
                                    {{ $supplierBalance < 0 ? -1 * $supplierBalance . ' CR' : $supplierBalance . ' DB' }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
        @if ($purchaseInvoice['description'])
            <div>
                <span><b>Note:</b></span>
                <p>
                    {{ $purchaseInvoice['description'] }}
                </p>
            </div>
        @endif

        <!-- Footer -->
        <footer>
            <p>{{ optional($settings)['footer_text'] }}</p>
        </footer>
    </div>
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
