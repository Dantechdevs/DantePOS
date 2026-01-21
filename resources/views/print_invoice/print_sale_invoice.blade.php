<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>{{ $saleInvoice['customers']['name'] ?? '' }} - Sale Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 18px; /* Further increased font size */
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .container {
            width: 210mm;
            max-width: 210mm;
            margin: 10mm auto;
            padding: 25px; /* Increased padding */
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px; /* Increased margin */
            padding-bottom: 15px; /* Increased padding */
            border-bottom: 2px solid #ddd;
        }

        .header .logo img {
            max-width: 150px; /* Further increased logo size */
        }

        .header .title {
            text-align: center;
            flex-grow: 1;
        }

        .header .title h1 {
            font-size: 28px; /* Further increased font size */
            font-weight: bold;
            margin: 0;
        }

        .header .title p {
            font-size: 18px; /* Further increased font size */
            margin: 0;
            color: #666;
        }

        .details-row {
            margin-bottom: 25px; /* Increased margin */
        }

        .details-row p {
            margin: 8px 0; /* Increased margin */
        }

        .details-row strong {
            font-weight: bold;
        }

        .invoice-details {
            margin-bottom: 25px; /* Increased margin */
            font-size: 18px; /* Further increased font size */
            line-height: 1.6; /* Increased line height */
        }

        .invoice-details p {
            margin: 0;
        }

        .table {
            font-size: 18px; /* Further increased font size */
        }

        .table th,
        .table td {
            padding: 12px; /* Increased padding */
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
            margin-right: 25px; /* Increased margin */
        }

        .totals .qr-code img {
            width: 120px; /* Further increased QR code size */
            height: 120px;
        }

        footer {
            margin-top: 25px; /* Increased margin */
            text-align: center;
            font-size: 16px; /* Further increased font size */
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

        @php
            $previous_balance = 0;
            if (!empty($saleInvoice['payment_type']) && $saleInvoice['payment_type']=='credit') {
                $previous_balance = $customerBalance - $saleInvoice['grand_total'];
            }else{
                $previous_balance = $customerBalance;
            }
        @endphp

        <!-- Company and Customer Details -->
        <div class="invoice-details">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <!-- Left Section: Invoice Number and Date -->
                <div>
                    <address>
                        <strong>Customer:</strong> {{ optional($saleInvoice['customers'])['name'] }}<br>
                        <strong>Phone:</strong> {{ optional($saleInvoice['customers'])['mobile'] }} <br>
                        <strong>Balance:</strong>
                        {{ @$previous_balance < 0 ? -1 * $previous_balance . ' CR' : $previous_balance . ' DB' }} <br>
                        <strong>Address:</strong> {{ optional($saleInvoice['customers'])['address'] }}
                    </address>
                </div>

                <!-- Right Section: Customer Details -->
                <div style="text-align: right;">
                    <p><strong>Invoice #:</strong> {{ $saleInvoice['invoice_no'] }}</p>
                    <p><strong>Date:</strong> {{ date('d/m/Y | h:i A', strtotime($saleInvoice['date'])) }}</p>
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
                    @if (!empty($saleInvoice['payment_type']))
                        <p>
                            <strong>Payment:</strong> {{ strtoupper($saleInvoice['payment_type']) }}
                        </p>
                    @endif
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
                @foreach ($saleitmesAddons as $addons)
                    @php
                        $counter = $counter + 1;
                        $product = \App\Models\Product::select('id', 'qtyPerUnit', 'product_code')->find(
                            $addons['product_id'],
                        );
                        $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining(
                            $addons['quantity'],
                            $product['qtyPerUnit'],
                        );
                    @endphp
                    <tr>
                        <td>{{ $counter }}</td>
                        <td> {{ $addons['productName'] }}
                        </td>
                        <td style="text-align: center;">
                            @if ($result['boxes_sold'] > 0)
                                {{ $result['boxes_sold'] }} {{ $addons['unit'] }}
                            @endif
                            @if ($result['boxes_sold'] > 0 && $result['items_sold'] > 0)
                                ,
                            @endif
                            @if ($result['items_sold'] > 0)
                                {{ $result['items_sold'] }} pieces
                            @endif
                        </td>
                        <td style="text-align: right;">{{ number_format($addons['selling_price']) }}</td>
                        <td style="text-align: right;">{{ number_format($addons['amount']) }}</td>
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
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($url) }}"
                    alt="QR Code">
            </div>
            <div>
                <table style="width: 100%; margin-top: 15px;">
                    <tbody>
                        @if ($saleInvoice['sub_total'] > 0 && ($saleInvoice['discount'] > 0 || $saleInvoice['other_charges'] > 0))
                            <tr>
                                <td style="text-align: left;"><strong>Sub Total:</strong></td>
                                <td style="text-align: right;">{{ number_format($saleInvoice['sub_total']) }}</td>
                            </tr>
                        @endif
                        @if ($saleInvoice['other_charges'] > 0)
                            <tr>
                                <td style="text-align: left;"><strong>Other Charges:</strong></td>
                                <td style="text-align: right;">{{ number_format($saleInvoice['other_charges']) }}</td>
                            </tr>
                        @endif
                        @if ($saleInvoice['discount'] > 0)
                            <tr>
                                <td style="text-align: left;">
                                    <strong>Discount:</strong>
                                    @if (isset($saleInvoice['discount_type']))
                                        ({{ ucfirst($saleInvoice['discount_type']) }})
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if (isset($saleInvoice['discount_type']) && $saleInvoice['discount_type'] === 'percentage')
                                        ({{ number_format($saleInvoice['discount']) }}%)
                                        {{ number_format($saleInvoice['discount_amount']) }}
                                    @else
                                        {{ number_format($saleInvoice['discount']) }}
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="text-align: left;"><strong>Grand Total:</strong></td>
                            <td style="text-align: right;">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['grand_total']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($saleInvoice['description'])
            <div>
                <span><b>Note:</b></span>
                <p>
                    {{ $saleInvoice['description'] }}
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
