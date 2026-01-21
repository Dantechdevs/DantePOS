<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <style>
        @page {
            margin: 20mm 10mm; /* Page margin */
            header: html_header; /* Repeats the header on each page */
            footer: html_footer; /* Repeats the footer on each page */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background-color: #FBCEB1;
        }

        .description-row {
            font-style: italic;
            text-align: left;
        }

        .header, .footer {
            text-align: center;
        }

        .header {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer {
            font-size: 10px;
            color: gray;
        }
    </style>
</head>
<body>

<!-- Header Content -->
<htmlpageheader name="header">
    <div style="text-align: center; font-size: 14px; font-weight: bold;">
        Iqbal Traders<br>
        Al Barkat Town Mamukanjan 37000, Punjab, Pakistan<br>
        Tel: M Irfan: +92336-6667686 / Irfan: +92336-6667686<br>
        <u>Customer Payments By Staff - Report</u>
    </div>
</htmlpageheader>

<htmlpagefooter name="footer">
    <div style="text-align: center; font-size: 10px; color: gray;">
        Generated on: {{ date('d-m-Y') }}
    </div>
</htmlpagefooter>

<!-- Main Content -->
<main>
    <table class="table">
        <thead>
            <tr>
                <th>VCH#</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Staff Member</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tbalanceCredit = 0;
            $tbalanceDebit = 0;
            ?>
            @foreach($debitCredit as $value)
            <?php
            $credit = (int)$value['credit'];
            $debit = (int)$value['debit'];
            $tbalanceCredit += $credit;
            $tbalanceDebit += $debit;
            ?>
            <tr>
                <td>{{$value['invoice_no']}}</td>
                <td>{{date('d-m-Y', strtotime($value['date']))}}</td>
                <td class="text-left">{{$value['customer']}}</td>
                <td>{{$value['debit']}}</td>
                <td>{{$value['credit']}}</td>
                <td>{{$value['staff']}}</td>
            </tr>
            @if(!empty($value['description']) && strtolower($value['description']) !== 'exchange')
            <tr class="description-row">
                <td colspan="6">{{$value['description']}}</td>
            </tr>
            @endif
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td>{{$tbalanceDebit}}</td>
                <td>{{$tbalanceCredit}}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</main>

</body>
</html>
