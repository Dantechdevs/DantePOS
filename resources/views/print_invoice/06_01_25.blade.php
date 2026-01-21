<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Sale Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 20px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 210mm;
            /* A5 width */
            margin: 20mm auto;
            /* Center on page */
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header h6 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .company-details p {
            margin: 5px 0;
        }

        .customer-details p {
            margin: 5px 0;
        }

        .invoice-details {
            margin-bottom: 20px;
        }

        .table-supply table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-supply th,
        .table-supply td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        /* .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .footer-details {
            text-align: left;
            margin-top: 10px;
        } */

        footer {
            position: fixed;
            bottom: -20px;
            left: 0px;
            right: 0px;
            height: 90px;
            font-size: 12px !important;

            /** Extra personal styles **/
            background-color: white;
            color: black;
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }

            .container {
                max-width: 210mm;
                margin: 0;
                padding: 20px;
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h6>Sale Invoice</h6>
        </div>

        <!-- Company and Customer Details -->
        <div class="row">
            <!-- Company Details (Left Side) -->
            <div class="col-md-6 company-details">
                <p><strong>Company:</strong> Iqbal Traders</p>
                <p><strong>Address:</strong> Al Barkat Town, Mamukanjan</p>
                <p><strong>Phone:</strong> Mr. Irfan +92336-6667686</p>
                {{-- <p><strong>Phone:</strong> Mr. Irfan 0333-8964775, 0343-1074775</p> --}}
            </div>
            <!-- Customer Details (Right Side) -->
            <div class="col-md-6 text-right customer-details">
                <p><strong>Customer:</strong> <span style="">
                        {{ isset($saleInvoice['customers']['name']) ? $saleInvoice['customers']['name'] : '' }}</span>
                </p>
                <p><strong>Phone:</strong>
                    {{ isset($saleInvoice['customers']['mobile']) ? $saleInvoice['customers']['mobile'] : '' }}</p>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <table class="table-supply">
                <tr>
                    <td colspan="12" class="text-center"><strong>Invoice #:</strong>
                        SN-{{ $saleInvoice['invoice_no'] }} | <strong>Date:</strong>
                        {{ date('M d, Y', strtotime($saleInvoice['date'])) }} | <strong>Order Status:</strong>
                        {{ $saleInvoice['status'] == 1 ? 'Confirmed' : 'Canceled' }}</td>
                </tr>
            </table>
        </div>

        <!-- Table Supply -->
        <!-- <div class="table-supply"> -->
        <table class="table table-bordered table-sm"
            style="font-family: Geneva, Verdana,  sans-serif; font-size:14px; color:#000000; line-height: 1rem;">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 0;
                    $previous_balance = $customerBalance - $saleInvoice['amount'];
                @endphp
                @foreach ($saleitmesAddons as $addons)
                    @php
                        $counter++;
                        $product = \App\Models\Product::select('id', 'qtyPerUnit')->find($addons['product_id']);
                        $totalPiecesSold = $addons['quantity']; // Number of pieces sold
                        $piecesPerBox = $product['qtyPerUnit']; // Number of pieces per box

                        // Call the helper function to calculate sold boxes and remaining pieces
                        $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining(
                            $totalPiecesSold,
                            $piecesPerBox,
                        );
                    @endphp
                    <tr>
                        <td class="text-center">{{ $counter }}</td>
                        <td class="text-left">{{ $addons['productName'] }}</td>
                        <td class="text-left">{{ $result['boxes_sold'] }}
                            {{ $addons['unit'] }}, {{ $result['items_sold'] }} pieces</td>
                        <td class="text-right">{{ $addons['selling_price'] }}</td>
                        <td class="text-right">{{ number_format($addons['amount']) }}</td>
                    </tr>
                @endforeach
                @if ($saleInvoice['discount'] > 0)
                    <tr>
                        <td colspan="4" class="text-left"><strong>Discount</strong></td>
                        <td class="text-right"><b>{{ number_format($saleInvoice['discount']) }}</b></td>
                    </tr>
                @endif
                <tr>
                    <td colspan="4" class="text-left"><strong>Total Amount</strong></td>
                    <td class="text-right"><b>{{ number_format($saleInvoice['amount']) }}</b></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-left"><strong>Previous Balance</strong></td>
                    <td class="text-right">
                        <b>{{ $previous_balance < 0 ? -1 * $previous_balance . ' CR' : $previous_balance . ' DB' }}
                        </b></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-left"><strong>Total Balance</strong></td>
                    <td class="text-right">
                        <b>{{ $customerBalance < 0 ? -1 * $customerBalance . ' CR' : $customerBalance . ' DB' }}</b>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- </div> -->

        <!-- Footer -->
        <!-- <div class="footer">
            <p style="line-height: 1.4;"><strong>Iqbal Traders</strong></br>
            Al Barkat Town, Mamukanjan</p>
            <div class="footer-details">
                <p style="line-height: 1.4; font-size: 10px;"><strong>Developed By:</strong> Irfan Mirza</br>
                <strong>Contact:</strong> +92336-6667686</p>
            </div>
        </div> -->

        <footer>
            <table class="" width="100%" cellspacing="0" cellpadding="0"
                style="border:none; font-size: 11px; line-height: 0.5 rem;">
                <tr>
                    <td width="20%" class="text-left" style="border-right-style: 1px solid; border-color: #ED7D31">
                        <span>
                            <p style="line-heigh:1rem;">
                                Software Developed By: Irfan Mirza </br>
                                Contact: +92336-6667686
                            </p>

                        </span>
                    </td>
                    <td width="10%"></td>
                    <td width="70%" class="text-left" style="margin-left: 10px;">
                        <table class="" width="100%" cellspacing="0" cellpadding="0"
                            style="border:none; font-size: 11px; line-height: 0.5 rem;">
                            <tr>
                                <td>
                                    <i class="fas fa-phone" aria-hidden="true"></i>
                                </td>
                                <td style="padding-left: 10px;">
                                    <span>Mr. Irfan +92336-6667686</span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <i class="fas fa-envelope" aria-hidden="true"></i>
                                </td>
                                <td style="padding-left: 10px;">
                                    <span>Mr. Irfan +92336-6667686</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fas fa-map-marker" aria-hidden="true"></i>
                                </td>
                                <td style="padding-left: 10px;">
                                    <span>
                                        Iqbal Traders, Al Barkat Town, Mamukanjan.

                                    </span>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

            </table>

        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Script to trigger printing after page load -->
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
