@extends('layouts.layout')
@section('title', '| Purchase Invoice')
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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Purchase Invoice</li>
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
                                    <h4>
                                        <i class="fas fa-globe"></i> {{ Auth::user()->name }}, Pvt.
                                        <small class="float-right">Purchase Date:
                                            {{ date('d/m/Y | h:i A', strtotime($purchaseInvoice['date'])) }}</small>
                                    </h4>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- info row -->
                            <div class="row invoice-info">
                                <div class="col-sm-4 invoice-col">
                                    From
                                    <address>
                                        <strong>{{ Auth::user()->name }}, Inc.</strong><br>
                                        <b>Address</b>: {{ Auth::user()->address }}<br>

                                        <b>Phone</b>: {{ Auth::user()->mobile }}<br>

                                    </address>
                                </div>
                                <!-- /.col -->
                                <div class="col-sm-4 invoice-col">
                                    Supplier,
                                    <address>
                                        <strong>{{ $purchaseInvoice['supplier']['name'] }}</strong><br>
                                        <b>Address</b>:
                                        {{ optional($purchaseInvoice['supplier'])['address'] }}<br>
                                        <b>Phone</b>:
                                        {{ optional($purchaseInvoice['supplier'])['mobile'] }}<br>

                                    </address>
                                </div>
                                <!-- /.col -->
                                <div class="col-sm-4 invoice-col">
                                    <b>Invoice # {{ $purchaseInvoice['purchase_no'] }}</b><br>
                                    <br>
                                    <b>Order Status: </b>
                                    {{ @$purchaseInvoice['status'] == 'received' ? 'Received' : (@$purchaseInvoice['status'] == 'pending' ? 'Pending' : 'Canceled') }}<br>
                                    <?php $previous_balance = $supplierBalance - $purchaseInvoice['grand_total']; ?>

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
                                            @foreach ($purchaseitmesAddons as $addons)
                                                <?php

                                                $counter = $counter + 1;
                                                ?>
                                                <tr>
                                                    <td width="6%">{{ $counter }}</td>
                                                    <td width="8%">{{ $addons['productName'] }}</td>
                                                    <td width="7%" class="text-center">{{ $addons['price'] }}</td>
                                                    <td width="7%" class="text-center">
                                                        {{ $addons['quantity'] }} {{ $addons['unit'] }}</td>
                                                    <td width="7%" class="text-right">
                                                        {{ number_format($addons['amount'], 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if ($purchaseInvoice['sub_total'] > 0)
                                                <tr>
                                                    <td colspan="4"
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        Sub Total:
                                                    </td>
                                                    <td
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        {{ number_format($purchaseInvoice['sub_total'], 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($purchaseInvoice['other_charges'] > 0)
                                                <tr>
                                                    <td colspan="4"
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        Other Charges:
                                                    </td>
                                                    <td
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        {{ number_format($purchaseInvoice['other_charges'], 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($purchaseInvoice['discount'] > 0)
                                                <tr>
                                                    <td colspan="4"
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        Discount @if (isset($purchaseInvoice['discount_type']))
                                                            ({{ ucfirst($purchaseInvoice['discount_type']) }}) :
                                                        @endif
                                                    </td>
                                                    <td
                                                        style="text-align: right; background: white; font-weight: bold; color:black;">
                                                        @if (isset($purchaseInvoice['discount_type']) && $purchaseInvoice['discount_type'] === 'percentage')
                                                            ({{ number_format($purchaseInvoice['discount']) }}%)
                                                            {{ number_format($purchaseInvoice['discount_amount']) }}
                                                        @else
                                                            {{ number_format($purchaseInvoice['discount']) }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr style="background-color: #F2F2F2; color:black;">
                                                <td colspan="4" style="text-align: right; font-weight: bold;">Invoice
                                                    Total:
                                                </td>
                                                <td style="text-align: right; font-weight: bold;">
                                                    {{ number_format($purchaseInvoice['grand_total'], 2) }}
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
                                                    {{ @$supplierBalance < 0 ? -1 * $supplierBalance . ' CR' : $supplierBalance . ' DB' }}
                                                </td>
                                            </tr>
                                            @if ($purchaseInvoice['description'])
                                                <tr style="background-color: white; color:black;">
                                                    <td>
                                                        <span><b>Note:</b></span>
                                                        <p>
                                                            {{ $purchaseInvoice['description'] }}
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

                            <div class="row">
                                <!-- accepted payments column -->
                                <div class="col-6">
                                    <table>
                                        <tr>
                                            <td colspan="12"><b>{{ Auth::user()->name }}, </b>Pakistan (Pvt) Ltd.</td>
                                        </tr>
                                        <!-- 15nd Row -->
                                        <!-- 16nd Row -->
                                        <tr>
                                            <td colspan="12" style="padding-top: 20px;"> <b>{{ Auth::user()->name }}</b>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <!-- /.col -->

                                <!-- /.col -->
                            </div>
                            <!-- /.row -->

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-12">
                                    <a href="{{ route('print.purchase.invoice', [$purchaseInvoice['id']]) }}"
                                        target="_blank" class="btn btn-default"><i class="fas fa-print"></i> Print</a>

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
