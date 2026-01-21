@extends('layouts.layout')
@section('title', '| Profit Loss')
@section('content')

    <style type="text/css">
        .customerLoader {
            display: none;
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, -50%);
        }
    </style>

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
                            <li class="breadcrumb-item active">Profit & Loss Report</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Start Card Here--------->
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title">Profit & Loss Report</h3>
                    </div>
                    <div class="card-body box-profile">
                        <form action="{{ route('report.profit.loss') }}" method="GET">


                            <div class="form-group row">
                                <label for="inputFromDate" class="col-sm-1 col-form-label">From Date</label>
                                <div class="col-sm-10 col-md-3">
                                    <input type="text" placeholder="DD-MM-YYYY"
                                        value="{{ date('d-m-Y', strtotime($startDate)) }}" class="form-control"
                                        name="startDate" id="startDate">
                                </div>
                                <label for="inputToDate" class="col-sm-1 col-form-label">To Date</label>
                                <div class="col-sm-10 col-md-3">
                                    <input type="text" placeholder="DD-MM-YYYY" class="form-control"
                                        value="{{ date('d-m-Y', strtotime($endDate)) }}" name="endDate" id="endDate">
                                </div>
                                <div class="col-sm-10 col-md-3">
                                    <button type="submit" class="btn btn-info btn-sm">Search</button>
                                </div>
                            </div>
                        </form>

                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered table-hover" id="report-data">
                                    <!-- Total Opening Stock -->
                                    <tbody>

                                        <tr>
                                            <td colspan="2" class="text-bold font-italic text-primary">Purchase</td>
                                        </tr>
                                        <!-- Total Purchase -->
                                        <tr>
                                            <td>Total Purchase</td>
                                            <td class="text-right text-bold">{{ $getTotalPurchaseAmount }}</td>
                                        </tr>
                                        <!-- Total Purchase Tax -->

                                        <!-- Total Purchase Paid Amount -->

                                        <!-- Total Purchase Due -->
                                        <tr>
                                            <td class="text-bold font-italic text-primary">Employees Salary</td>
                                            <td class="text-right text-bold">{{ $employees_salarie }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-bold font-italic text-primary">Total Expense</td>
                                            <td class="text-right text-bold">{{ $expenses }}</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-hover " id="report-data-4">

                                    <!-- Total Expenses -->
                                    <tbody>

                                        <tr>
                                            <td colspan="2" class="text-bold font-italic text-primary">Sales</td>
                                        </tr>
                                        <!-- Total Sales -->
                                        <tr>
                                            <td>Total Sales</td>
                                            <td class="text-right text-bold">{{ number_format($getTotalSalesAmount, 2) }}
                                            </td>
                                        </tr>
                                        <!-- Total Sales Tax -->
                                        <tr>
                                            <td>Total Discount on Sales</td>
                                            <td class="text-right text-bold">{{ number_format($salesDiscount, 2) }}</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="box">

                                    <!-- /.box-header -->
                                    <div class="box-body table-responsive no-padding">
                                        <table class="table table-bordered table-hover " id="report-data-2">

                                            <!-- Total Gross Profit -->
                                            <tbody>
                                                <tr>
                                                    <td class="text-bold font-italic text-danger">Gross Profit</td>
                                                    <td class="text-right text-bold">{{ $returnGrossProfit }}</td>
                                                </tr>
                                                <!-- Total Net Profit -->
                                                <tr>
                                                    <td class="text-bold font-italic text-success">Net Profit</td>
                                                    <td class="text-right text-bold">{{ $netProfit }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.box -->
                            </div>
                        </div>

                        <!---------------------------------------------------------------------->
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-header" style="background-color: black;">
                                    <h3 class="card-title"><strong>Item Wise Profit</strong></h3>
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body table-responsive no-padding">
                                    <div class="col-md-12">
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tab_1">
                                                <div class="row">
                                                    <!-- right column -->
                                                    <div class="col-md-12">
                                                        <!-- form start -->

                                                        <br>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-hover table-sm"
                                                                id="profit_by_item_table">
                                                                <thead>
                                                                    <tr class="bg-blue">
                                                                        <th style="">#</th>
                                                                        <th style="">Invoice #</th>
                                                                        <th style="">Item Name</th>
                                                                        <th style="">Sales Quantity</th>
                                                                        <th style="">Sales Price</th>
                                                                        <th style="">Purchase Price</th>
                                                                        <th style="">Item Gross Profit</th>

                                                                    </tr>
                                                                </thead>

                                                                <tbody id="tbodyid">
                                                                    <?php
												$counter = 0;
												$totalGrossProfitAmount = 0;
												foreach($products as $mainProduct){
													// echo "<pre>"; print_r($mainProduct['items_addon']); exit();
													$mainProductID = $mainProduct['id'];
													$invoice_no = $mainProduct['invoice_no'];
													$date = date('d-m-Y',strtotime($mainProduct['date']));
													$variations = unserialize($mainProduct['items_addon']);

													foreach ($variations as $key => $variation){
														$var_productName = $variation['productName'];
														$var_unitPrice = $variation['cost'];
														$var_salePrice = $variation['selling_price'];
														$var_qty = $variation['quantity'];
														$calculatedCost = $variation['calculatedCost'];
														$calculatedTotalAmount = $variation['amount'];

														$counter = $counter+1;
														$itemGrossProfit = (float)$calculatedTotalAmount - (float)$calculatedCost;
														$calculatedGrossProfit = $itemGrossProfit;
														$totalGrossProfitAmount = $itemGrossProfit + $totalGrossProfitAmount;
													?>
                                                                    <tr>
                                                                        <td>{{ $counter }}</td>
                                                                        <td><a href="{{ route('edit.sale', $mainProductID) }}"
                                                                                target="_blank">{{ $invoice_no }}</a>
                                                                        </td>
                                                                        <td>{{ $var_productName }}</td>
                                                                        <td class="text-center">{{ $var_qty }}</td>
                                                                        <td class="text-right">{{ $calculatedTotalAmount }}
                                                                        </td>
                                                                        <td class="text-right">{{ $calculatedCost }}</td>
                                                                        <td class="text-right">{{ $calculatedGrossProfit }}
                                                                        </td>
                                                                    </tr>
                                                                    <?php
												}
											}
												?>

                                                                    <tr>
                                                                        <td colspan="6"
                                                                            style="text-align: right; background: gray; font-weight: bold; color:white;">
                                                                            Total Gross Profit
                                                                        </td>
                                                                        <td
                                                                            style="text-align: right; background: gray; font-weight: bold; color:white;">
                                                                            {{ $totalGrossProfitAmount }}
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <!-- /.box-body -->
                                                    </div>
                                                    <!--/.col (right) -->
                                                </div>
                                                <!-- /.row -->
                                            </div>
                                            <!-- /.tab-pane -->

                                        </div>
                                        <!-- /.tab-content -->

                                    </div>
                                    <!-- /.col -->

                                </div>
                                <!-- /.box-body -->
                            </div>
                            <!-- /.box -->
                        </div>
                        <!---------------------------------------------------------------------->
                    </div>
                    <!-- /.card-body -->
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>

@endsection
