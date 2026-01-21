<!DOCTYPE html>
<html lang="ur" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تھرمل انوائس</title>
    <style>
        @font-face {
            font-family: 'Noori Nastaleeq';
            src: local('Noori Nastaleeq'), local('NooriNastaleeq');
            /* Add other font formats if needed */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 76mm;
            margin: 0 auto;
            font-family: 'Noori Nastaleeq', Arial, sans-serif;
            font-size: 14px;
            color: #000;
            background-color: #fff;
            line-height: 1.2;
            direction: rtl;
            text-align: right;
        }

        .container {
            padding: 1mm 2mm;
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

        .header img {
            max-width: 80px;
            max-height: 80px;
            margin-bottom: 3px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            font-size: 12px;
            margin: 1px 0;
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
            text-align: left;
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
            font-family: Arial, sans-serif;
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

            @page {
                size: 76mm auto;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                ? asset(optional($settings)['invoice_logo'])
                : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                    ? asset(optional($settings)['default_image'])
                    : asset('images/default-image.png')) }}"
                alt="لوگو">
            <h1>{{ optional($settings)['site_name'] }}</h1>
            <p>{{ optional($settings)['site_address'] }}</p>
            <p>{{ optional($settings)['mobile_numbers'] }}</p>
        </div>

        @php
            $previous_balance = 0;
            if (!empty($saleInvoice['payment_type']) && $saleInvoice['payment_type']=='credit') {
                $previous_balance = $customerBalance - $saleInvoice['grand_total'];
            }else{
                $previous_balance = $customerBalance;
            }
        @endphp

        <!-- Invoice Details -->
        <div class="details">
            <div class="details-row">
                <div class="details-right">
                    @if (@$saleInvoice['customers'] && !empty(@$saleInvoice['customers']['name']))
                        <p><strong>گاہک:</strong> {{ optional($saleInvoice['customers'])['name'] ?? 'N/A' }}</p>
                    @endif
                    @if (@$saleInvoice['customers'] && !empty(@$saleInvoice['customers']['mobile']))
                        <p><strong>موبائل:</strong> {{ optional($saleInvoice['customers'])['mobile'] ?? 'N/A' }}</p>
                    @endif
                    <p><strong>بقیہ:</strong> {{ @$previous_balance < 0 ? -1 * $previous_balance . ' جمع' : $previous_balance . ' واجب الادا' }}</p>
                </div>

                <div class="details-left">
                    <p><strong>انوائس:</strong> {{ $saleInvoice['invoice_no'] }}</p>
                    <p><strong>تاریخ:</strong> {{ date('d/m/Y h:i A', strtotime($saleInvoice['date'])) }}</p>
                    <p><strong>حالت:</strong>
                        {{ $saleInvoice['status'] == 0 ? 'منسوخ' :
                          ($saleInvoice['status'] == 1 ? 'بل شدہ' :
                          ($saleInvoice['status'] == 2 ? 'زیر التوا' :
                          ($saleInvoice['status'] == 3 ? 'واپسی' : 'نامعلوم'))) }}
                    </p>
                    <p><strong>ادائیگی:</strong> {{ strtoupper($saleInvoice['payment_type'] ?? 'N/A') == 'CREDIT' ? 'ادھار' : 'نقد' }}</p>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <table>
            <thead>
                <tr>
                    <th width="8%">#</th>
                    <th width="42%">مصنوعات</th>
                    <th width="15%">مقدار</th>
                    <th width="15%">قیمت</th>
                    <th width="20%">کل</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 0; @endphp
                @foreach ($saleitmesAddons as $addons)
                    @php
                        $counter = $counter + 1;
                    @endphp
                    <tr>
                        <td>{{ $counter }}</td>
                        <td>{{ $addons['productName'] }}</td>
                        <td style="text-align: center;">
                            {{ $addons['quantity'] }} {{ $addons['unit'] }}
                        </td>
                        <td style="text-align: left;">{{ number_format($addons['selling_price']) }}</td>
                        <td style="text-align: left;">{{ number_format($addons['amount']) }}</td>
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
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data={{ urlencode($url) }}" alt="QR">
            </div>
            <div class="summary">
                @if ($saleInvoice['sub_total'] > 0 && ($saleInvoice['discount'] > 0 || $saleInvoice['other_charges'] > 0))
                    <p><strong>ذیلی کل:</strong> {{ number_format($saleInvoice['sub_total']) }}</p>
                @endif
                @if ($saleInvoice['other_charges'] > 0)
                    <p><strong>دیگر چارجز:</strong> {{ number_format($saleInvoice['other_charges']) }}</p>
                @endif
                @if ($saleInvoice['discount'] > 0)
                    <p><strong>رعایت:</strong>
                        @if ($saleInvoice['discount_type'] === 'percentage')
                            ({{ $saleInvoice['discount'] }}%)
                        @endif
                        {{ number_format($saleInvoice['discount_amount']) }}
                    </p>
                @endif
                <p><strong>کل رقم:</strong> {{ optional($settings)['currency'] }} {{ number_format($saleInvoice['grand_total']) }}</p>
            </div>
        </div>

        @if ($saleInvoice['description'])
            <div class="note">
                <p><strong>نوٹ:</strong> {{ $saleInvoice['description'] }}</p>
            </div>
        @endif

        <!-- Footer -->
        <footer>
            <p>{{ optional($settings)['footer_text'] }}</p>
        </footer>

        <!-- Office Details -->
        <div class="office-details">
            <p>تیار کردہ: <strong><i>ڈیو پیلیئر - سافٹ ویئر حل</i></strong></p>
            <p>ویب سائٹ: <a href="https://devpeller.com">https://devpeller.com</a></p>
            <p>موبائل: 0336-6667686</p>
        </div>
    </div>

    <!-- Print Button (visible only on screen) -->
    <div class="print-button-container">
        <button class="print-button" onclick="window.print()">
            <i class="fas fa-print"></i> انوائس پرنٹ کریں
        </button>
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
</body>
</html>
