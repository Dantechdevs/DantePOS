<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    	<!-- <title>Document</title> -->
    	<style>
    	body {
    		font-family: Geneva, Verdana, sans-serif;
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
    		padding: 3px;
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
    </style>
</head>

<body>

	<center style=" width: 700px; margin: 0px auto;">
		<table cellspacing="0" cellpadding="0" border="0" style="font-family: Geneva, Verdana,  sans-serif; font-size:12px; color:#000000; line-height: 1rem;">
			<tr>
				<td style=" width: 100%; padding-top: 30px; text-align: center;"><strong>Sold Items - Report</strong></td>
			</tr><br>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" border="0">
						<tr>
							<!-- company address -->
							<td style="font-size: 10px; width: 50%;">
								<span style="font-size: 12px; font-weight: bold;">{{ Auth::user()->name }}</span>
                                {{ Auth::user()->address }}. Tel: {{ Auth::user()->mobile }}

								<!-- company address -->
								<hr style=" width: 100%; border-color: #000000;"></hr>

							</td>

							<td style="font-size: 10px; width: 10%; padding-left: 100px"></td>
							<td style="font-size: 10px; width: 40%;">
								<!-- Profile Details -->

								<p class="p-text" ><b>Report Date:</b></p>
								<hr style=" width: 100%; border-color: #000000;"></hr>
								<p class="p-text" >{{date('d M Y',strtotime($startDate))}} to {{date('d M Y',strtotime($endDate))}}</p>

								<hr style=" width: 100%; border-color: #000000;"></hr>

								<!-- <p class="p-text"> <span>Bharai Employees Details in single file.</span></p> -->
								<!-- Profile Details -->
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- 2nd Row -->
			<!-- Heading -->


			<?php
			if($product_id=='all'){
				?>
				<tr>
					<td style="width: 100%;">
						<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<th colspan="12" align="center"><b>Item wise Sale</b></th>
							</tr>
							<!-- Table Heading -->
							<tr>
								<th colspan="1">#</th>
								<th colspan="2">Invoice#</th>
								<th colspan="2">Date</th>

								<th colspan="2">Customer</th>
								<th colspan="2">Product Name</th>
								<th colspan="1">Selling Price</th>
								<th colspan="1">Items Count</th>
								<th colspan="1">Sales Amount</th>
							</tr>
							<!-- Table Heading -->
							<!-- Table content row 1 -->
							<?php
							$totalAmount = 0;
							$returnTotalAmount = 0;
							$counter = 0;
							foreach ($products as $mainProduct) {
                    // echo "<pre>"; print_r($mainProduct); exit();
								$mainProductID = $mainProduct['id'];
								$invoice_no = $mainProduct['invoice_no'];
								$customerName = $mainProduct['customers']['name'];
								$date = date('d M Y',strtotime($mainProduct['date']));
								$variations = unserialize($mainProduct['items_addon']);


								foreach ($variations as $keys => $variation){


									$productName = $variation['productName'];
									$unit = $variation['unit'];
									$selling_price = $variation['selling_price'];
									$quantity = $variation['quantity'];
									$amount = $variation['amount'];

									$totalAmount = $variation['amount'] + $totalAmount;

									$soldValue = $quantity * $selling_price;
									$counter = $counter+1;
									?>
									<tr>
										<td colspan="1">{{$counter}}</td>
										<td colspan="2">{{$invoice_no}}</td>
										<td colspan="2">{{$date}}</td>

										<td colspan="2">{{$customerName}}</td>
										<td colspan="2">{{$productName}}</td>
										<td colspan="1">{{number_format($selling_price,2)}}</td>
										<td colspan="1">{{$quantity}}</td>
										<td colspan="1">{{number_format($soldValue,2)}}</td>
									</tr>
									<?php

								}
								$returnTotalAmount = $totalAmount;
							}
							?>

							<!-- Table content row 1 -->
							<!-- Table total  row -->
							<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
								<td colspan="11" class="text-bold">Total</td>
								<td colspan="1">{{number_format($returnTotalAmount,2)}}</td>

							</tr>


						</table>
					</td>
				</tr>
				<?php
			}else{
				?>
				<tr>
					<td style="width: 100%;">
						<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<th colspan="12" align="center"><b>Item wise Sale</b></th>
							</tr>
							<!-- Table Heading -->
							<tr>
								<th colspan="1">#</th>
								<th colspan="2">Invoice#</th>
								<th colspan="2">Date</th>

								<th colspan="2">Customer</th>
								<th colspan="2">Product Name</th>
								<th colspan="1">Selling Price</th>
								<th colspan="1">Items Count</th>
								<th colspan="1">Sales Amount</th>
							</tr>
							<!-- Table Heading -->
							<!-- Table content row 1 -->
							<?php
							$itemSoldCount = 0;
							$returnItemSoldCount = 0;
							$totalAmount = 0;
							$returnTotalAmount = 0;
							$counter = 0;

                            $singleProduct = \App\Models\Product::with('units')->select('id', 'qtyPerUnit', 'unit_id')->find($product_id);
                // echo "<pre>"; print_r($singleProduct->toArray()); exit;
                $piecesPerBox = $singleProduct['qtyPerUnit']; // Number of pieces per box
                $unit = $singleProduct->units->name;

							foreach ($products as $mainProduct) {
                    // echo "<pre>"; print_r($mainProduct); exit();
								$mainProductID = $mainProduct['id'];
								$invoice_no = $mainProduct['invoice_no'];
								$customerName = $mainProduct['customers']['name'];
								$date = date('d M Y',strtotime($mainProduct['date']));
								$variations = unserialize($mainProduct['items_addon']);


								foreach ($variations as $keys => $variation){
									if ($variation['product_id']==$product_id) {

										$productName = $variation['productName'];
										$unit = $variation['unit'];
										$selling_price = $variation['selling_price'];
										$quantity = $variation['quantity'];
										$amount = $variation['amount'];


                                        $totalPiecesSold = $variation['quantity']; // Number of pieces sold

                                        // Call the helper function to calculate sold boxes and remaining pieces
                                        $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining($totalPiecesSold, $piecesPerBox);

										$itemSoldCount = $variation['quantity'] + $itemSoldCount;
										$totalAmount = $variation['amount'] + $totalAmount;



										$soldValue = $quantity * $selling_price;
										$counter = $counter+1;
										?>
										<tr>
											<td colspan="1">{{$counter}}</td>
											<td colspan="2">{{$invoice_no}}</td>
											<td colspan="2">{{$date}}</td>

											<td colspan="2">{{$customerName}}</td>
											<td colspan="2">{{$productName}}</td>
											<td colspan="1">{{number_format($selling_price,2)}}</td>
											<td colspan="1">{{$result['boxes_sold']}} {{$unit}}, {{$result['items_sold']}} piece</td>
											<td colspan="1">{{number_format($soldValue,2)}}</td>
										</tr>
										<?php
									}
								}
								$returnItemSoldCount = $itemSoldCount;
								$returnTotalAmount = $totalAmount;

                                $result = \App\Http\Helpers\ProductHelper::calculateSoldAndRemaining($returnItemSoldCount, $piecesPerBox);
							}
							?>

							<!-- Table content row 1 -->
							<!-- Table total  row -->
							<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
								<td colspan="10" class="text-bold">Total</td>
								<td colspan="1">{{$result['boxes_sold']}} {{$unit}}, {{$result['items_sold']}} piece</td>
								<td colspan="1">{{number_format($returnTotalAmount,2)}}</td>

							</tr>


						</table>
					</td>
				</tr>
				<?php
			}
			?>


			<!-- 5nd Row -->
			<tr>
				<td colspan="12" style="padding-top: 10px;"></td>
			</tr>
			<!-- 6nd Row -->



			<!-- 14nd Row -->
			<!-- 15nd Row -->

			<!-- 15nd Row -->
			<!-- 16nd Row -->
			<!-- <tr>
				<td colspan="12" style="padding-top: 20px;"> <b>Abid</b></td>
			</tr> -->
			<!-- 16nd Row -->
			<!-- 17nd Row -->
			<!-- <tr>
				<td colspan="12" style="font-size: small;">(Owner)</td>
			</tr> -->
			<!-- 17nd Row -->
		</table>
	</center>
</body>

</html>
