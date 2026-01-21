<?php
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\SupplierOpeningBalance;
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
  <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
  	<!-- <title>Document</title> -->
  	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
  	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.3.0/paper.css">
  	<style>@page { size: A5 }
  	@media print
  	{
  		@page
  		{
  			size: 8.5in 11in;
  		}
  	}
  </style>
  <style>
  body {
  	font-family: Geneva, Verdana, sans-serif;
  	background-color: white;
  }

  h6 {
  	font-size: 16px;
  	margin: 0;
  	margin-bottom: 10px;

  }

  p {
  	font-size: 14px;
  }

  .p-text {
  	margin: 0;
  	margin-bottom: 5px;
  	font-size: 12px;
  }

  .table-suply {
  	border: 1px solid black;
  	border-top: none;
  	margin-top: 20px;
  }

  .table-suply tr th,
  .table-suply tr td {
  	border-top: 1px solid #000000;
  	text-align: center;
  	border-right: 1px solid #000000;
  	padding: 1px;

  }

  .table-suply tr th:last-child,
  .table-suply tr td:last-child {
  	border-right: none;
  }

  .p-text-1 {
  	text-decoration: underline;
  	padding-top: 10px;
  }

  .p-text-2 {
  	padding-top: 10px;
  }

  .cont-challan-single {
  	float: left;
  	width: 100%;
  	border: 1px solid #000000;
  	padding: 5px 10px;
  	margin-bottom: 20px;
  }

  .challan-single-title {
  	float: left;
  	width: 50%;
  }

  .suplier {
  	width: 22%;
  	border-right: 1px solid black;
  	padding: 5px 0px;
  }

  .cont-suplier .challan-single-title:last-child {
  	width: 77%;
  	text-align: center;
  	padding: 5px 0px;
  }

  .cont-challan-single.cont-suplier {
  	padding: 0px 10px;
  }

  .table-suply.no-border tr td {
  	border-top: none;
  	border-right: none;
  	padding: 0px;
  }

  .table-suply.no-border tr th {
  	border-right: none;
  }

  .table-challan tr th {
  	border-top: 1px solid #000000;
  }

  .table-challan tr th,
  .table-challan tr td {

  	text-align: center;
  }

  .table-challan tr th:first-child,
  .table-challan tr td:first-child {
  	border-left: 1px solid #000000;
  	border-bottom: 1px solid #000000;

  }

  .table-challan tr th:last-child,
  .table-challan tr td:last-child {
  	border-right: 1px solid #000000;
  	border-bottom: 1px solid #000000;

  }

  .table-challan tr th:last-child,
  .table-challan tr th:first-child {
  	border-bottom: none;
  }

  .p-tr td {
  	padding: 0px !important;
  	padding: 3px 8px 3px 3px !important;
  	border-right: 1px solid #000000;
  	border-top: 1px solid #000;
  }
  .invoice-total tr td,
  .invoice-total tr th
  {
  	text-align: right;
  }
</style>
</head>


<body class="A5">
	<center style=" width: 500px; margin: 0px auto;">
		<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td style="font-size: 10px; width: 50%;" class="text-left">
					<p class="p-text" ><b>Stock Report</p>


					</td>
					<td style="font-size: 10px; width: 50%;">
						<p class="p-text" ><b>Date: </b>{{date('d-m-Y')}}</p>
					</td>
				</tr>
			</table>
			<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">

				<tr>
					<th colspan="1">#</th>
					<th colspan="3">Product Name</th>
					<th colspan="2">Cost</th>
					<th colspan="2">Current Stock</th>
					<th colspan="4">Stock Value</th>
				</tr>
				<?php
				$totalAmount = 0;
				$returnTotalAmount = 0;
				$counter = 0;
				foreach ($products as $key => $mainProduct) {
					$counter = $counter+1;
					$mainProductID = $mainProduct['id'];
					$mainProductName = $mainProduct['name'];
					$quantity = $mainProduct['quantity'];

					$unit = $mainProduct['units']['name'];

                    if ($mainProduct['qtyPerUnit'] > 0) {
                        $cost = $mainProduct['item_cost'];
                        $selling_price = $mainProduct['item_selling_price'];
                    }else{
                        $cost = $mainProduct['cost'];
                        $selling_price = $mainProduct['selling_price'];
                    }

					$stockValue = (float)$quantity * (float)$cost;
					$totalAmount = $stockValue + $totalAmount;

                    $stockCount = $quantity;
                    $bottlesPerBox = $mainProduct['qtyPerUnit'];
                    $result = \App\Http\Helpers\ProductHelper::calculateBoxesAndRemaining($stockCount, $bottlesPerBox);

					?>
					<tr>
						<td colspan="1">{{$counter}}</td>
						<td colspan="3" class="text-left">{{$mainProductName}}</td>
						<td colspan="2" class="text-right">{{$cost}}</td>
						<td colspan="2" class="text-right">{{$result['fullBoxes']}} {{$unit}}, {{$result['remainingItems']}} pieces</td>
						<td colspan="4" class="text-right">{{$stockValue}}</td>
					</tr>
					<?php
				}
				$returnTotalAmount = $totalAmount;
				?>
				<!-- Table content row 1 -->
				<!-- Table total  row -->
				<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
					<td colspan="8" class="text-bold">Total</td>
					<td colspan="4" class="text-right">{{$returnTotalAmount}}</td>
				</tr>

			</table>
		</center>

		<script type="text/javascript">
			window.addEventListener("load", window.print());
		</script>
	</body>

	</html>
