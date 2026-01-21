<!DOCTYPE html>
<html lang="ur" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $saleInvoice['customers']['name'] ?? '' }} - رسید</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu&family=Roboto:wght@300;400;500;700&display=swap"
        rel="stylesheet">
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
            font-family: 'Noto Nastaliq Urdu', 'Roboto', sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: var(--light-gray);
            line-height: 1.8;
            text-align: right;
            direction: rtl;
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
            flex-direction: row-reverse;
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
            letter-spacing: 1px;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .header .title p {
            font-size: 14px;
            margin: 0;
            color: var(--dark-gray);
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .company-info {
            text-align: right;
            margin-bottom: 5px;
        }

        .company-info strong {
            display: block;
            font-size: 18px;
            color: var(--secondary-color);
            margin-bottom: 3px;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .invoice-meta {
            background-color: var(--light-gray);
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-direction: row-reverse;
        }

        .invoice-meta .left-section,
        .invoice-meta .right-section {
            width: 48%;
        }

        .invoice-meta p {
            margin: 8px 0;
            font-size: 14px;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .invoice-meta strong {
            font-weight: 500;
            color: var(--secondary-color);
            min-width: 100px;
            display: inline-block;
            margin-left: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .table th {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 500;
            padding: 12px 15px;
            text-align: right;
            font-size: 14px;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--medium-gray);
            vertical-align: top;
            text-align: right;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            display: flex;
            justify-content: flex-start;
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
            padding: 10px 15px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid var(--medium-gray);
            font-size: 13px;
            color: var(--dark-gray);
            text-align: center;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .qr-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            flex-direction: row-reverse;
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
            font-size: 12px;
            color: var(--dark-gray);
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .notes {
            margin-top: 20px;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border-right: 3px solid var(--primary-color);
            font-size: 14px;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .notes strong {
            color: var(--secondary-color);
        }

        .office-details {
            text-align: center;
            font-size: 12px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            color: var(--dark-gray);
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .office-details a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            font-family: 'Noto Nastaliq Urdu', serif;
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
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            background-color: #e2e3e5;
            color: #383d41;
            font-family: 'Noto Nastaliq Urdu', serif;
        }

        .ltr-date {
            direction: ltr;
            display: inline-block;
            unicode-bidi: embed;
        }

        @media print {
            body {
                background-color: white;
                font-size: 14px;
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
    <div class="container">
        <!-- Header -->
        <h1 class="text-center" style="font-size: 16px; font-weight:bold; font-family: 'Noto Nastaliq Urdu';">رسید فروخت
        </h1>
        <div class="header">
            <div class="logo">
                <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                    ? asset(optional($settings)['invoice_logo'])
                    : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                        ? asset(optional($settings)['default_image'])
                        : asset('images/default-image.png')) }}"
                    alt="لوگو">
            </div>
            <div class="title">
                <div class="company-info">
                    <strong>{{ optional($settings)['site_name_ur'] }}</strong>
                    <p>{{ optional($settings)['site_address_urdu'] }}</p>
                    <p>{{ $settings['mobile_numbers'] }}</p>
                </div>
            </div>
        </div>

        @php


            $statusClass = '';
            $statusText = '';
            switch ($saleInvoice['status']) {
                case 0:
                    $statusClass = 'status-canceled';
                    $statusText = 'منسوخ';
                    break;
                case 1:
                    $statusClass = 'status-billed';
                    $statusText = 'بل شدہ';
                    break;
                case 2:
                    $statusClass = 'status-pending';
                    $statusText = 'زیر التوا';
                    break;
                case 3:
                    $statusClass = 'status-return';
                    $statusText = 'واپسی';
                    break;
                default:
                    $statusClass = '';
                    $statusText = 'نامعلوم';
            }
        @endphp

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <div class="right-section">
                <p><strong>رسید نمبر:</strong> {{ $saleInvoice['invoice_no'] }}</p>
                <p><strong>تاریخ:</strong> <span
                        class="ltr-date">{{ date('d M, Y | h:i A', strtotime($saleInvoice['date'])) }}</span></p>
                <p><strong>حالت:</strong> <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span></p>
            </div>
            <div class="left-section">
                <p><strong>گاہک:</strong> {{ optional($saleInvoice['customer'])['name_ur'] }}</p>
                @if (@$saleInvoice['customer'] && !empty(@$saleInvoice['customer']['mobile']))
                    <p><strong>فون:</strong> {{ optional($saleInvoice['customer'])['mobile'] }}</p>
                    @endif

            </div>
        </div>

        <!-- Products Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>تفصیل</th>
                    <th class="text-center">مقدار</th>
                    <th class="text-right">فی اکائی قیمت</th>
                    <th class="text-right">رقم</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 0; @endphp
                @foreach ($saleitmesAddons as $addons)
                    @php
                        $counter++;
                        $productName = App\Models\Product::find($addons['product_id'])->name_ur ?? 'نامعلوم پروڈکٹ';
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
                        <td class="text-left">{{ number_format($addons['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div style="clear: both; display: flex; justify-content: flex-start;">
            <div class="totals-box" style="width: 300px; margin-left: 0; margin-right: auto;">
                <table class="totals-table" style="width: 100%;">
                    @if ($saleInvoice['sub_total'] > 0 && ($saleInvoice['discount'] > 0 || $saleInvoice['other_charges'] > 0))
                        <tr>
                            <td style="text-align: right; padding-right: 10px; font-family: 'Noto Nastaliq Urdu';">
                                ذیلی کل:</td>
                            <td style="text-align: left; padding-left: 10px;">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['sub_total'], 2) }}</td>
                        </tr>
                    @endif

                    @if ($saleInvoice['other_charges'] > 0)
                        <tr>
                            <td style="text-align: right; padding-right: 10px; font-family: 'Noto Nastaliq Urdu';">
                                دیگر چارجز:</td>
                            <td style="text-align: left; padding-left: 10px;">{{ optional($settings)['currency'] }}
                                {{ number_format($saleInvoice['other_charges'], 2) }}</td>
                        </tr>
                    @endif

                    @if ($saleInvoice['discount'] > 0)
                        <tr>
                            <td style="text-align: right; padding-right: 10px; font-family: 'Noto Nastaliq Urdu';">
                                رعایت:</td>
                            <td style="text-align: left; padding-left: 10px;">
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

                    <tr style="border-top: 1px dashed #999; font-weight: bold;">
                        <td style="text-align: right; padding-right: 10px; font-family: 'Noto Nastaliq Urdu';">کل
                            رقم:
                        </td>
                        <td style="text-align: left; padding-left: 10px;">{{ optional($settings)['currency'] }}
                            {{ number_format($saleInvoice['grand_total'], 2) }}</td>
                    </tr>
                    @if ($saleInvoice['paid_amount'] > 0)
                    <tr>
                        <td><strong>ادا کیا:</strong></td>
                        <td class="text-left">{{ optional($settings)['currency'] }}
                            {{ number_format($saleInvoice['paid_amount'], 2) }}</td>
                    </tr>
                @endif
                @if ($saleInvoice['change_amount'] > 0)
                    <tr>
                        <td><strong>رقم کی واپسی:</strong></td>
                        <td class="text-left">{{ optional($settings)['currency'] }}
                            {{ number_format($saleInvoice['change_amount'], 2) }}</td>
                    </tr>
                @endif
                 @if ($saleInvoice['balance_amount'] > 0)
                    <tr>
                        <td><strong>واجب الادا:</strong></td>
                        <td class="text-left">{{ optional($settings)['currency'] }}
                            {{ number_format($saleInvoice['balance_amount'], 2) }}</td>
                    </tr>
                @endif

                @if ($customerBalance > 0 )
                    @php

                        $preblnc = $customerBalance;
                    @endphp
                    <tr>
                        <td><strong>پچھلا بیلنس:</strong></td>
                        <td class="text-left">{{ optional($settings)['currency'] }}
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
                        <td><strong>ٹوٹل بیلنس:</strong></td>
                        <td class="text-left">{{ optional($settings)['currency'] }}
                            {{ number_format($customerBalance, 2) }}</td>
                    </tr>
                @endif
                </table>

            </div>
        </div>

        <!-- QR Code and Notes -->
        <div class="qr-section">


            <div class="qr-code">
                {{-- @php
                    $url = route('print.invoice', $saleInvoice['invoice_no']);
                @endphp
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($url) }}"
                    alt="کیو آر کوڈ">
                <p>رسید کی تصدیق کے لیے سکین کریں</p> --}}
            </div>

            @if ($saleInvoice['description'])
                <div class="notes">
                    <strong>نوٹس:</strong> {{ $saleInvoice['description'] }}
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ optional($settings)['footer_text'] }}</p>
        </div>

        <!-- Office Details -->
        <div class="office-details">
            <p>Invoice generated on {{ date('d M Y h:i') }} | Developed by: <strong>Devpeller - Software
                    Solutions</strong></p>
            <p>Website: <a href="https://devpeller.com">https://devpeller.com</a> | Mobile: 0336-6667686</p>
        </div>
        {{-- <div class="office-details">
            <p>{{ date('d M Y H:i') }} کو تیار شدہ | ڈویلپر: <strong>ڈیوپیلیئر - سافٹ ویئر سولوشنز</strong></p>
            <p>ویب سائٹ: <a href="https://devpeller.com">https://devpeller.com</a> | موبائل: 0336-6667686</p>
        </div> --}}
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
