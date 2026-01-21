@extends('layouts.layout')
@section('title', '| Sale Invoice')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                    </div>

                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Sale Invoice</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <!-- Main content -->
                        <div class="invoice p-3 mb-3">
                            <!-- title row -->
                            <div class="row">
                                <div class="col-12">
                                    {{-- <h4>
                                        <i class="fas fa-globe"></i> {{ optional($settings)['site_name'] }}
                                        <small class="float-right">Invoice Date:
                                            {{ date('d/m/Y | h:i A', strtotime($saleInvoice['date'])) }}</small>
                                    </h4> --}}
                                    <h4 style="display: flex; align-items: center; gap: 15px;">
                                        <!-- Circular Logo Wrapper -->
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid #ddd; background-color: #f9f9f9;">
                                            <!-- Logo Image -->
                                            <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                                                ? asset(optional($settings)['invoice_logo'])
                                                : (optional($settings)['default_image'] && file_exists(public_path(optional($settings)['default_image']))
                                                    ? asset(optional($settings)['default_image'])
                                                    : asset('images/default-image.png')) }}"
                                                alt="Site Logo"
                                                style="width: 100%; height: 100%; object-fit: cover; object-position: center;">

                                        </div>
                                        <!-- Site Name -->
                                        <span style="font-size: 1.5rem; font-weight: bold; color: #333;">
                                            {{ optional($settings)['site_name'] }}
                                        </span>
                                        <!-- Invoice Date -->
                                        <small class="float-right"
                                            style="margin-left: auto; font-size: 0.9rem; color: #555;">
                                            Invoice Date: {{ date('d/m/Y | h:i A', strtotime($saleInvoice['date'])) }}
                                        </small>
                                    </h4>


                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- info row -->
                            <div class="row invoice-info">
                                <div class="col-sm-4 invoice-col">
                                    From,
                                    <address>
                                        {{ optional($settings)['site_name'] }}<br>
                                        <b>Address</b>: {{ optional($settings)['site_address'] }}<br>
                                        @php
                                            // Split the comma-separated mobile numbers into an array
                                            $mobileNumbers = explode(',', $settings['mobile_numbers']);
                                            $mobileNumbers = array_map('trim', $mobileNumbers); // Trim any extra spaces
                                        @endphp
                                        @foreach ($mobileNumbers as $mobileNumber)
                                            <b>Phone</b>: {{ $mobileNumber }}<br>
                                        @endforeach
                                    </address>
                                </div>
                                <!-- /.col -->
                                <div class="col-sm-4 invoice-col">
                                    <strong>Customer,</strong>
                                    <address>
                                        {{ $saleInvoice['customers']['name'] }}<br>
                                        <b>Address</b>:
                                        {{ @$saleInvoice['customers']['address'] ? $saleInvoice['customers']['address'] : '' }}<br>
                                        <b>Phone</b>:
                                        {{ @$saleInvoice['customers']['mobile'] ? $saleInvoice['customers']['mobile'] : '' }}<br>

                                    </address>
                                </div>
                                <!-- /.col -->
                                <div class="col-sm-4 invoice-col">
                                    <b>Invoice # {{ $saleInvoice['invoice_no'] }}</b><br>
                                    <br>
                                    <b>Order Status: </b> {{ @$saleInvoice['status'] == 1 ? 'Confirmed' : 'Canceled' }}<br>
                                    <?php $previous_balance = $customerBalance - $saleInvoice['grand_total']; ?>

                                    <!-- <b>Payment Due:</b> 2/22/2014<br>
                            <b>Account:</b> 968-34567 -->
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /.row -->

                            <!-- Table row -->
                            <div class="row">
                                <div class="col-12 table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>

                                                <th>Product</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-right">Item Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $counter = 0;
                                            ?>
                                            @foreach ($saleitmesAddons as $addons)
                                                <?php
                                                $product = \App\Models\Product::select('id', 'qtyPerUnit')->find($addons['product_id']);
                                                $totalPiecesSold = $addons['quantity']; // Number of pieces sold
                                                $piecesPerBox = $product['qtyPerUnit']; // Number of pieces per box

                                                // Call the helper function to calculate sold boxes and remaining pieces
                                                $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining($totalPiecesSold, $piecesPerBox);
                                                // echo "<pre>"; print_r($result); exit;
                                                $counter = $counter + 1;
                                                ?>
                                                <tr>
                                                    <td width="6%">{{ $counter }}</td>
                                                    <td width="8%">{{ $addons['productName'] }}</td>
                                                    <td width="7%" class="text-center">{{ $addons['selling_price'] }}
                                                    </td>
                                                    <td width="7%" class="text-center">{{ $result['boxes_sold'] }}
                                                        {{ $addons['unit'] }}, {{ $result['items_sold'] }} pieces</td>
                                                    <td width="7%" class="text-right">
                                                        {{ number_format($addons['amount'], 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if ($saleInvoice['sub_total'] > 0)
                                                <tr>
                                                    <td colspan="4"
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        Sub Total:
                                                    </td>
                                                    <td
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        {{ number_format($saleInvoice['sub_total'], 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($saleInvoice['other_charges'] > 0)
                                                <tr>
                                                    <td colspan="4"
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        Other Charges:
                                                    </td>
                                                    <td
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        {{ number_format($saleInvoice['other_charges'], 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($saleInvoice['discount'] > 0)
                                                <tr>
                                                    <td colspan="4"
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        Discount @if (isset($saleInvoice['discount_type']))
                                                            ({{ ucfirst($saleInvoice['discount_type']) }}) :
                                                        @endif
                                                    </td>
                                                    <td
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        @if (isset($saleInvoice['discount_type']) && $saleInvoice['discount_type'] === 'percentage')
                                                            ({{ number_format($saleInvoice['discount']) }}%)
                                                            {{ number_format($saleInvoice['discount_amount']) }}
                                                        @else
                                                            {{ number_format($saleInvoice['discount']) }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr style="background-color: #F2F2F2; color:black;">
                                                <td colspan="4" style="text-align: right; font-weight: bold;">Invoice
                                                    Total:
                                                </td>
                                                <td style="text-align: right; font-weight: bold;">
                                                    {{ number_format($saleInvoice['grand_total'], 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"
                                                    style="text-align: right; background: white; font-weight: bold; color:black;">
                                                    Previous Balance:
                                                </td>
                                                <td
                                                    style="text-align: right; background: white; font-weight: bold; color:black;">
                                                    {{ @$previous_balance < 0 ? -1 * $previous_balance . ' CR' : $previous_balance . ' DB' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"
                                                    style="text-align: right; background: gray; font-weight: bold; color:white;">
                                                    Total Balance:
                                                </td>
                                                <td
                                                    style="text-align: right; background: gray; font-weight: bold; color:white;">
                                                    {{ @$customerBalance < 0 ? -1 * $customerBalance . ' CR' : $customerBalance . ' DB' }}
                                                </td>
                                            </tr>
                                            @if ($saleInvoice['description'])
                                                <tr>
                                                    <td>
                                                        <span><b>Note:</b></span>
                                                        <p>
                                                            {{ $saleInvoice['description'] }}
                                                        </p>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /.row -->
                            <hr>

                            <!-- /.row -->

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-12">
                                    <a href="{{ route('print.invoice', [$saleInvoice['id']]) }}" target="_blank"
                                        class="btn btn-default"><i class="fas fa-print"></i> Print</a>

                                </div>
                            </div>
                        </div>
                        <!-- /.invoice -->

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
    </div>
@endsection
