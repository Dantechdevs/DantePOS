<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسید</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            width:72mm;
            font-family:'Jameel Noori Nastaleeq','Noori Nastaleeq','Noto Nastaliq Urdu',Arial,sans-serif;
            font-size:13px;
            line-height:1.4;
            direction:rtl;
            padding:2mm 1.8mm;
            color:#000;
        }
        .c{width:100%;}
        .tc{text-align:center;}
        .b{font-weight:bold;}
        .logo{max-width:42mm;max-height:14mm;margin:0 auto 2mm;display:block;}
        /* .header{border-bottom:2px solid #000;padding-bottom:3mm;margin-bottom:3mm;text-align:center;} */
        .header {
        text-align: center;
        margin-bottom: 3px;
        padding-bottom: 3px;
        border-bottom: 1px dashed #000;
    }

    .header .logo-container {
        width: 100%;
        margin-bottom: 3px;
        text-align: center;
    }

    .header .logo-container img {
        width: 100%;
        max-width: 100%;
        height: auto;
        max-height: 80px;
        object-fit: contain;
    }
        .shop{font-size:17px;margin:2mm 0;}
        .info{font-size:12.5px;margin-bottom:3mm;border-bottom:1px dashed #000;padding-bottom:2mm;}
        .row{display:flex;justify-content:space-between;margin:1.5mm 0;font-size:12.8px;}

        /* DOTTED GRID TABLE - Works 100% on all thermal printers */
        table{width:100%;border-collapse:collapse;margin:3mm 0;font-size:12.5px;}
        th, td{padding:4px 3px;text-align:center;position:relative;}
        th{border-top:2px solid #000;border-bottom:2px solid #000;}
        td{border-bottom:1px dotted #555;}

        /* Vertical dotted lines using ::after */
        th:not(:first-child)::before,
        td:not(:first-child)::before{
            content:"";
            position:absolute;
            left:0;top:0;bottom:0;
            border-left:1px dotted #666;
        }

        .sno{width:9%;}
        .desc{width:42%;text-align:right;}
        .qty{width:14%;}
        .rate,.amt{width:17.5%;text-align:left;direction:ltr;font-weight:bold;}

        .totals{margin-top:4mm;border-top:2px solid #000;padding-top:3mm;font-size:13.5px;}
        .trow{display:flex;justify-content:space-between;padding:2px 0;}
        .grand{font-size:15px;font-weight:bold;border-top:2px solid #000;padding-top:4mm;margin-top:4mm;}
        .note{margin:4mm 0;padding:2mm 0;border-top:1px dashed #000;border-bottom:1px dashed #000;font-size:12.5px;}
        .footer{text-align:center;margin-top:5mm;font-size:11px;line-height:1.5;}

        @media print{
            body{padding:1.5mm;font-size:12.8px;}
            td{border-bottom:1px dotted #666;}
            th:not(:first-child)::before, td:not(:first-child)::before{border-left:1px dotted #666;}
            @page{size:72mm auto;margin:0;}
        }
    </style>
</head>
<body onload="setTimeout(() => window.print(), 500)">
<?php
    // echo "<pre>"; print_r($customerBalance); "</pre>"; exit;
    ?>
<div class="c">

    <!-- Header -->
    <div class="header tc">
       <div class="logo-container">
                <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                    ? asset(optional($settings)['invoice_logo'])
                    : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                        ? asset(optional($settings)['default_image'])
                        : asset('images/default-image.png')) }}"
                    alt="Logo">
            </div>
        <div class="shop b">{{ $settings['site_name'] ?? 'دکان کا نام' }}</div>
        <div style="font-size:12px;">
            {{ $settings['site_address'] ?? '' }}<br>
            {{ $settings['mobile_numbers'] ?? '' }}
        </div>
    </div>

    <!-- Customer + Invoice Info -->
    <div class="info">
        <div class="row">
            <div><b>گاہک:</b> {{ $saleInvoice['customer']['name_ur'] ?? 'والک ان' }}</div>
            <div><b>رسید #:</b> {{ $saleInvoice['invoice_no'] }}</div>
        </div>
        <div class="row">
            <div><b>وقت:</b> {{ date('d-m-Y h:i A', strtotime($saleInvoice['date'])) }}</div>
            <div>
                <b>حالت:</b>
                @php
                    $st = $saleInvoice['status'] ?? 1;
                    $statusText = $st == 0 ? 'منسوخ' : ($st == 1 ? 'ادا شدہ' : ($st == 2 ? 'زیر التوا' : 'واپسی'));
                @endphp
                <span style="font-size:11px;">
                    {{ $statusText }}
                </span>
            </div>
        </div>
        @if(!empty($saleInvoice['customer']['mobile']))
        <div class="row">
            <div><b>موبائل:</b> {{ $saleInvoice['customer']['mobile'] }}</div>
        </div>
        @endif
    </div>

    <!-- Perfect Dotted Grid Table -->
    <table>
        <thead>
            <tr>
                <th class="sno">#</th>
                <th class="desc">تفصیل</th>
                <th class="qty">مقدار</th>
                <th class="rate">ریٹ</th>
                <th class="amt">کل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($saleitmesAddons as $i => $item)
                @php
                    $p = \App\Models\Product::find($item['product_id']);
                    $name = $p ? ($p->name_ur ?: 'نامعلوم') : 'نامعلوم';
                    if(mb_strlen($name)>24) $name = mb_substr($name,0,21).'..';
                @endphp
                <tr>
                    <td class="sno">{{ $loop->iteration }}</td>
                    <td class="desc">{{ $name }}</td>
                    <td class="qty">{{ $item['quantity'] }} {{ $item['unit'] ?? '' }}</td>
                    <td class="rate">{{ number_format($item['selling_price'],0) }}</td>
                    <td class="amt">{{ number_format($item['amount'],0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Compact Totals -->
    <div class="totals">
        @if(($saleInvoice['discount_amount']??0)>0 || ($saleInvoice['other_charges']??0)>0)
            <div class="trow"><span>ذیلی کل:</span><span>{{ number_format($saleInvoice['sub_total'],0) }}</span></div>
        @endif
        @if(($saleInvoice['discount_amount']??0)>0)
            <div class="trow"><span>رعایت{{ $saleInvoice['discount_type']=='percentage'?" ({$saleInvoice['discount']}%)":'' }}:</span>
                <span>{{ number_format($saleInvoice['discount_amount'],0) }}-</span></div>
        @endif
        @if(($saleInvoice['other_charges']??0)>0)
            <div class="trow"><span>دیگر چارجز:</span><span>+{{ number_format($saleInvoice['other_charges'],0) }}</span></div>
        @endif

        <div class="trow grand">
            <span class="b">کل قابل ادائیگی:</span>
            <span class="b">{{ number_format($saleInvoice['grand_total'],0) }}</span>
        </div>

        @if(($saleInvoice['paid_amount']??0)>0)
            <div class="trow"><span>ادا کردہ:</span><span>{{ number_format($saleInvoice['paid_amount'],0) }}</span></div>
        @endif
        @if(($saleInvoice['change_amount']??0)>0)
            <div class="trow"><span>واپس:</span><span>{{ number_format($saleInvoice['change_amount'],0) }}</span></div>
        @endif
        @if(($saleInvoice['balance_amount']??0)>0)
            <div class="trow"><span>بقایا:</span><span>{{ number_format($saleInvoice['balance_amount'],0) }}</span></div>
        @endif

        @php

            $prev = $customerBalance ?? 0;
            $totalDue = $prev + ($saleInvoice['balance_amount'] ?? 0);
        @endphp
        @if($prev > 0)
            <div class="trow"><span>پچھلا بیلنس:</span><span>{{ number_format($prev,0) }}</span></div>
        @endif
        @if($totalDue > 0)
            <div class="trow grand">
                <span class="b">ٹوٹل واجب الادا:</span>
                <span class="b">{{ number_format($totalDue,0) }}</span>
            </div>
        @endif
    </div>

    @if(!empty($saleInvoice['description']))
        <div class="note"><b>نوٹ:</b> {{ $saleInvoice['description'] }}</div>
    @endif

    <div class="footer">
        <b>{{ $settings['footer_text'] ?? 'شکریہ!' }}</b><br>
        <small>Devpeller Solutions • 0336-6667686</small>
    </div>

</div>
</body>
</html>
