<?php
use App\Models\Customer;
use App\Models\Sale;
use App\Models\AdvanceCustomerPayment;
use App\Models\ReceivablePayable;
?>
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
				<td style=" width: 100%; padding-top: 30px; text-align: center;"><strong>Staff Sale -Report</strong></td>
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


			<tr>
				<td style="width: 100%;">
					<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
						<tr>
							<th colspan="12" align="center"><b>Staff Sale Report Details</b></th>
						</tr>

						<!-- Table Heading -->
						<tr>
							<th colspan="1">#</th>
							<th colspan="2">Date</th>
							<th colspan="1">Invoice#</th>
							<th colspan="3">Customer</th>
							<th colspan="3">Added By</th>
							<th colspan="2">Total Amount</th>
						</tr>
						<!-- Table Heading -->
						<!-- Table content row 1 -->
						<?php
						$totalAmount = 0;
						$returnTotalAmount = 0;

						$paidAmount = 0;
						$returnPaidAmount= 0;

						$dueAmount = 0;
						$returnDueAmount = 0;

						foreach ($customerPayment as $key => $value) {
                    // echo"<pre>"; print_r($value['total_amount']); exit();
							$totalAmount = $value['amount'] + $totalAmount;
							?>
							<tr>
								<td colspan="1">{{$key+1}}</td>
								<td colspan="2">{{date('M d, Y',strtotime($value['date']))}}</td>
								<td colspan="1">{{$value['invoice_no']}}</td>
								<td colspan="3">{{$value['customers']['name']}}</td>
								<td colspan="3">{{$value['users']['name']}}</td>
								<td colspan="2">{{number_format($value['amount'],2)}}</td>
							</tr>
							<?php
						}
						$returnTotalAmount = $totalAmount;
						?>

						<!-- Table content row 1 -->
						<!-- Table total  row -->
						<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
							<td colspan="10" class="text-bold">Total</td>
							<td colspan="2">{{number_format($returnTotalAmount,2)}}</td>
						</tr>


					</table>
				</td>
			</tr>

			<!-- 5nd Row -->
			<tr>
				<td colspan="12" style="padding-top: 10px;"></td>
			</tr>
			<!-- 6nd Row -->



			<!-- 14nd Row -->
			<!-- 15nd Row -->
			<tr>
				<td colspan="12"><b>Muhammad Haseeb, </b></td>
			</tr>
			<!-- 15nd Row -->
			<!-- 16nd Row -->

			<!-- 16nd Row -->
			<!-- 17nd Row -->
			<tr>
				<td colspan="12" style="font-size: small;">(Owner)</td>
			</tr>
			<!-- 17nd Row -->
		</table>
	</center>
</body>

</html>
