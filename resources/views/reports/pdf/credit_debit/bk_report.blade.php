<?php
use App\Models\Customer;
use App\Models\Sale;
use App\Models\AdvanceCustomerPayment;
use App\Models\CustomerOpeningBalance;
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
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
	body {
		font-family: Geneva, Verdana, sans-serif;
		padding-left: 2px;
		background-color: white;
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
		@if($sale_type=='customerWise')
		<span style="font-size: 12px; font-weight: bold;">Iqbal Traders</span>
		<span class="p-text">Al Barkat Town Mamukanjan 37000, Punjan Pakistan</br> </span> <br>
        <table class="table-suply" width="100%" cellspacing="0" cellpadding="0" style="border:none;">
            <tr>
                <td>
                <span class="p-text">Tel: M Irfan: +92336-6667686 / Irfan: &nbsp;&nbsp; +92336-6667686</span>
                </td>
            </tr>
        </table>


			<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td style="font-size: 10px; width: 50%;" class="text-left">
						<p class="p-text" ><b>Customer: </b>{{$customer['name']}}</p>
							<p class="p-text" ><b>Total Balance:</b> {{(@$customerTotalBalance<0)?(-1)*$customerTotalBalance. ' DB':$customerTotalBalance. ' CR'}}</p>

					</td>
					<td style="font-size: 10px; width: 50%;">
						<p class="p-text" ><b>Date: </b>{{date('M d, Y',strtotime($startDate))}} to {{date('M d, Y',strtotime($endDate))}}</p>
					</td>
				</tr>
			</table>



			<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td colspan="12" align="center" class="p-text"><b>Credit/Debit Report</b></td>
				</tr>
				<tr class="p-text text-bold">
					<th colspan="1">VCH#</th>
					<th colspan="2">Date</th>

					<th colspan="3">Debit</th>
					<th colspan="3">Credit</th>
					<th colspan="3">Balance</th>


				</tr>
				<tr>

				<td colspan="9" width="87%" class="text-left p-text">Opening Balance</td>

				<td colspan="3" width="13%" class="text-right p-text">{{(@$customerOPBlncInReport<0)?(-1)*$customerOPBlncInReport. ' DB':$customerOPBlncInReport. ' CR'}}</td>
			</tr>
				<?php
				$tbalance=(@$customerOPBlncInReport)?$customerOPBlncInReport:0;
				$total_balance=0;
				?>
				@foreach($debitCredit as $value)
				<?php $chkbala = (int)$value['credit'] - (int)$value['debit'];  $tbalance += $chkbala;
				$total_balance = $tbalance;
				?>
				<tr>
					<td colspan="1" class="text-left p-text">{{$value['invoice_no']}}</td>
					<td colspan="2" class="p-text">{{date('d-m-Y',strtotime($value['date']))}}</td>

					<td colspan="3" class="text-right p-text">{{$value['debit']}}</td>
					<td colspan="3" class="text-right p-text">{{$value['credit']}}</td>
					<?php
					if($total_balance<0){
						?>
						<td colspan="3" class="text-right p-text">{{-1*($total_balance)}} DB</td>
						<?php
					}else{
						?>
						<td colspan="3" class="text-right p-text">{{$total_balance}} CR</td>
						<?php
					}

					?>
				</tr>
				<?php if(isset($value['description'])){ ?>
				<tr>
					<td colspan="12" class="text-left p-text"> {{$value['description']}}</td>
				</tr>
			<?php } ?>
				@endforeach
				<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
					<td colspan="9"><span class="text-bold p-text">Closing Balance</span></td>
					<?php

					if($total_balance<0){
						?>
						<td colspan="3" class="text-right p-text">{{-1*($total_balance)}} DB</td>
						<?php
					}else{
						?>
						<td colspan="3" class="text-right p-text">{{(@$total_balance)?$total_balance. ' CR':$tbalance. ' DB'}}</td>
						<?php
					}

					?>
				</tr>


			</table>
			@else
			<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td style="font-size: 10px; width: 50%;" class="text-left">
						<p class="p-text" ><b>Area Report: </b>{{(@$sale_type=='areaWise')?$area['name']:'' }}</p>

					</td>
					<td style="font-size: 10px; width: 50%;">
						<p class="p-text" ><b>Report Date: </b>{{date('d-m-Y')}}</p>
					</td>
				</tr>
			</table>



			<table class="table-suply" width="100%" cellspacing="0" cellpadding="70">
				<tr>
					<td colspan="12" align="center"><b>Customers Balance Report</b></td>
				</tr>
				<tr class="p-text text-bold text-center">
					<th colspan="1">#</th>
					<th colspan="4">Customer</th>
					<th colspan="3">Balance</th>
					<th colspan="4">Receiving</th>


				</tr>
				<?php

				/* Check Customer Total Amount Function Start*/
				function totalAmount($customer_id) {
					$returnTotalAmount = 0;

					$totalAmount = Sale::where('customer_id',$customer_id)->where('status',1)->get();

					$totalAmountOutPut = 0;
					if ($totalAmount === NULL) {
						$returnTotalAmount = 0;
					} else {
						$total_amount = $totalAmount;
						foreach ($total_amount as $value) {
							$totalAmountOutPut = $value->amount + $totalAmountOutPut;
						}

						$returnTotalAmount = $totalAmountOutPut;
					}

					return $returnTotalAmount;
				}
				/* Check Customer Total Amount Function Ends*/

				/* Check Customer Pre Receivable Amount Function Start*/
				function customerDebitAmount($customer_id) {
					$returnReceivable = 0;

					$totalReceivable = CustomerOpeningBalance::where('customer_id',$customer_id)->where('type','debit')->get();

					$receivableOutPut = 0;
					if ($totalReceivable === NULL) {
						$returnReceivable = 0;
					} else {
						$total_receivable = $totalReceivable;
						foreach ($total_receivable as $value) {
							$receivableOutPut = $value->amount + $receivableOutPut;
						}

						$returnReceivable = $receivableOutPut;
					}

					return $returnReceivable;
				}
				/* Check Customer Pre Receivable Amount Function Ends*/

				/* Check Customer Advance Amount Function Start*/
				function advanceAmount($customer_id) {
					$returnAdvance = 0;

					$totalAdvance = AdvanceCustomerPayment::where('customer_id',$customer_id)->get();

					$advanceOutPut = 0;
					if ($totalAdvance === NULL) {
						$returnAdvance = 0;
					} else {
						$total_advance = $totalAdvance;
						foreach ($total_advance as $value) {
							$advanceOutPut = $value->amount + $advanceOutPut;
						}

						$returnAdvance = $advanceOutPut;
					}
					return $returnAdvance;
				}
				/* Check Customer Advance Amount Function Ends*/

				/* Check Customer Payment Discount Amount Function Start*/
				function paymentDicsount($customer_id) {
					$returnDiscount = 0;

					$paymentDiscount = AdvanceCustomerPayment::where('customer_id',$customer_id)->get();

					$discountOutPut = 0;
					if ($paymentDiscount === NULL) {
						$returnDiscount = 0;
					} else {
						$total_discount = $paymentDiscount;
						foreach ($total_discount as $value) {
							$discountOutPut = $value->payment_discount + $discountOutPut;
						}

						$returnDiscount = $discountOutPut;
					}
        // dd($returnDiscount);
					return $returnDiscount;
				}
				/* Check Customer Payment Discount Amount Function Ends*/

				/* Check Customer Pre Payable Amount Function Start*/
				function customerCredtiAmount($customer_id) {
					$returnPayable = 0;

					$totalPayable = CustomerOpeningBalance::where('customer_id',$customer_id)->where('type','credit')->get();

					$payableOutPut = 0;
					if ($totalPayable === NULL) {
						$returnPayable = 0;
					} else {
						$total_payable = $totalPayable;
						foreach ($total_payable as $value) {
							$payableOutPut = $value->amount + $payableOutPut;
						}

						$returnPayable = $payableOutPut;
					}
					return $returnPayable;
				}
				/* Check Customer Pre Payable Amount Function Ends*/

				$customerSaleAmount = 0;

				$customerReceivableAmount = 0;

				$customerAdvanceAmount = 0;

				$customerPayableAmount = 0;
				$counter = 0;
				$tbalance= 0;
				$total_balance = 0;
				$customers = Customer::where('area_id',$areaID)->get()->toArray();
				foreach ($customers as $key => $customer) {
					$customer_id = $customer['id'];
					$area_id = $customer['area_id'];
					$customerName = $customer['name'];

					/*---- Customer Total Amount Start ------*/
							// foreach ($customerPayment as $key => $balance) {

					if ($areaID == $customer['area_id']) {


						$customerSaleAmount = totalAmount($customer_id);
									// dd($allCustomersAmount);
						$customerReceivableAmount = customerDebitAmount($customer_id);

						$customerAdvanceAmount = advanceAmount($customer_id);
						$singleCustomerDiscount = paymentDicsount($customer_id);

						$customerPayableAmount = customerCredtiAmount($customer_id);
									// dd($allCustomersCreditAmount);
						$customerBalance = ($customerSaleAmount + $customerReceivableAmount) - ($singleCustomerDiscount + $customerAdvanceAmount + $customerPayableAmount);
                        $tbalance += $customerBalance;
                        $total_balance = $tbalance;
						$counter = $counter+1;
						?>
						<tr>
							<td colspan="1" class="p-text text-center">{{$counter}}</td>
							<td colspan="4" class="p-text text-left"> {{$customerName}}</td>
							<?php
							if($customerBalance<0){
								?>
								<td colspan="3" class="p-text text-right">{{-1*($customerBalance)}} CR</td>
								<?php
							}else{
								?>
								<td colspan="3" class="p-text text-right">{{($customerBalance)}} DB</td>
								<?php
							}

							?>
							<td colspan="4"></td>
						</tr>

						<?php

					}


					/*---- Customer Total Amount Ends ------*/




				}

				?>
				<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
					<td colspan="5" class="p-text text-bold">Total Balance</td>
					<?php
					if($total_balance<0){
						?>
						<td colspan="3" class="p-text text-right">{{-1*($total_balance)}} CR</td>
						<?php
					}else{
						?>
						<td colspan="3" class="p-text text-right">{{($total_balance)}} DB</td>
						<?php
					}

					?>
					<td colspan="4"></td>
				</tr>


			</table>
			@endif
		</center>
	</body>
	</html>
