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
    	body {
    		font-family: Geneva, Verdana, sans-serif;
    		padding-left: 2px;
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

<body class="A5">

	<center style=" width: 500px; margin: 0px auto;">
		@if($allSuppliersData=='all')
		<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<th colspan="12" align="center"><b>Supplier Balance Report</b></th>
			</tr>
			<p class="p-text" ><b>Report Date: </b>{{date('d-m-Y')}}</p>
			<tr class="p-text text-bold">
				<th colspan="1">#</th>
				<th colspan="6">Supplier</th>
				<th colspan="5">Balance</th>
			</tr>
			<!-- Table Heading -->
			<!-- Table content row 1 -->
			<?php

			/* Check Supplier Total Amount Function Start*/
			function supplierTotalAmount($supplier_id) {
				$returnTotalAmount = 0;

				$totalAmount = Purchase::where('supplier_id',$supplier_id)->where('status','received')->get();

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
			/* Check Supplier Total Amount Function Ends*/

			/* Check Supplier Payable Amount Function Start*/
			function supplierCreditAmount($supplier_id) {
				$returnPayable = 0;

				$totalPayable = SupplierOpeningBalance::where('supplier_id',$supplier_id)->where('type','credit')->get();

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
			/* Check Supplier Payable Amount Function Ends*/

			/* Check Supplier Pre Receivable Amount Function Start*/
			function supplierDebitAmount($supplier_id) {
				$returnPaidAdvance = 0;

				$totalPaidAdvance = SupplierOpeningBalance::where('supplier_id',$supplier_id)->where('type','debit')->get();

				$paidAdvanceOutPut = 0;
				if ($totalPaidAdvance === NULL) {
					$returnPaidAdvance = 0;
				} else {
					$total_receivable = $totalPaidAdvance;
					foreach ($total_receivable as $value) {
						$paidAdvanceOutPut = $value->amount + $paidAdvanceOutPut;
					}

					$returnPaidAdvance = $paidAdvanceOutPut;
				}

				return $returnPaidAdvance;
			}
			/* Check Customer Pre Receivable Amount Function Ends*/

			/* Check Customer Advance Amount Function Start*/
			function paymentToSupplier($supplier_id) {
				$returnSupplierPayment = 0;

				$paymentToSupplier = SupplierPayment::where('supplier_id',$supplier_id)->get();

				$supplierPaymentOutPut = 0;
				if ($paymentToSupplier === NULL) {
					$returnSupplierPayment = 0;
				} else {
					$total_advance = $paymentToSupplier;
					foreach ($total_advance as $value) {
						$supplierPaymentOutPut = $value->amount + $supplierPaymentOutPut;
					}

					$returnSupplierPayment = $supplierPaymentOutPut;
				}
				return $returnSupplierPayment;
			}
			/* Check Customer Advance Amount Function Ends*/

			/* Check Customer Payment Discount Amount Function Start*/
			function supplierPaymentDicsount($supplier_id) {
				$returnDiscount = 0;

				$paymentDiscount = SupplierPayment::where('supplier_id',$supplier_id)->get();

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



			/*SUM all Customers Amount Areawise Start*/
			function allSupplierAmount()
			{
				$allSupplierAmount = Purchase::where('status','received')->sum('amount');
				return $allSupplierAmount;
			}
			function allSupplierCreditAmount(){
				$allSupplierCreditAmount = SupplierOpeningBalance::where('type','credit')->sum('amount');
				return $allSupplierCreditAmount;
			}
			function allSuppliersDebitAmount(){
				$allSuppliersDebitAmount = SupplierOpeningBalance::where('type','debit')->sum('amount');
				return $allSuppliersDebitAmount;
			}
			function allPaymentsToSuppliers(){
				$allPaymentsToSuppliers = SupplierPayment::sum('amount');
				return $allPaymentsToSuppliers;
			}
			function allSuppliersDiscount(){
				$allSuppliersDiscount = SupplierPayment::sum('payment_discount');
				return $allSuppliersDiscount;
			}
			/*SUM all Customers Amount Areawise Ends*/
			$supplierTotalAmount = 0;
			$allSupplierAmount = 0;

			$supplierCreditAmount = 0;
			$allSupplierCreditAmount = 0;

			$supplierDebitAmount = 0;
			$allSuppliersDebitAmount = 0;

			$paymentToSupplier = 0;
			$allPaymentsToSuppliers = 0;
			$counter = 0;

			foreach ($suppliers as $key => $supplier) {
				$supplier_id = $supplier['id'];
				$supplierName = $supplier['name'];

				/*---- Customer Total Amount Start ------*/
							// foreach ($customerPayment as $key => $balance) {

							// if ($area_id == $supplier['area_id']) {


				$supplierTotalAmount = supplierTotalAmount($supplier_id);
				$allSupplierAmount = allSupplierAmount();

				$supplierCreditAmount = supplierCreditAmount($supplier_id);
				$allSupplierCreditAmount = allSupplierCreditAmount();

				$supplierDebitAmount = supplierDebitAmount($supplier_id);
				$allSuppliersDebitAmount = allSuppliersDebitAmount();

				$singleSupplierPaymentDicsount = supplierPaymentDicsount($supplier_id);

				$allSuppliersDiscount = allSuppliersDiscount();


				$paymentToSupplier = paymentToSupplier($supplier_id);
				$allPaymentsToSuppliers = allPaymentsToSuppliers();
									// dd($allPaymentsToSuppliers);
				$supplierBalance = ($supplierTotalAmount + $supplierCreditAmount) - ($singleSupplierPaymentDicsount + $supplierDebitAmount + $paymentToSupplier);

				$counter = $counter+1;
				?>
				<tr>
					<td colspan="1" class="p-text">{{$counter}}</td>
					<td colspan="6" class="text-left p-text">{{$supplierName}}</td>
					<?php
					if($supplierBalance<0){
						?>
						<td colspan="5" class="text-right p-text">{{-1*($supplierBalance)}} DB</td>
						<?php
					}else{
						?>
						<td colspan="5" class="text-right p-text">{{$supplierBalance}} CR</td>
						<?php
					}

					?>

				</tr>

				<?php

							// }


				/*---- Customer Total Amount Ends ------*/




			}
			$allSuppliersBalance = ($allSupplierAmount + $allSupplierCreditAmount) - ($allSuppliersDiscount + $allSuppliersDebitAmount + $allPaymentsToSuppliers);
						// dd($allSuppliersBalance);
			?>

			<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
				<td colspan="7">Total Balance</td>
				<?php
				if($allSuppliersBalance<0){
					?>
					<td colspan="5" class="text-right p-text">{{-1*($allSuppliersBalance)}} DB</td>
					<?php
				}else{
					?>
					<td colspan="5" class="text-right p-text">{{$allSuppliersBalance}} CR</td>
					<?php
				}

				?>
			</tr>

		</table>
		@else
		<span style="font-size: 12px; font-weight: bold;">Iqbal Traders<</span>
		<span class="p-text">Al Barkat Town Mamukanjan 37000, Punjan Pakistan. </span><br>
        <span class="p-text">Tel: M Irfan: +92336-6667686 / Irfan: &nbsp;&nbsp; +92336-6667686</span>
		<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td style="font-size: 10px; width: 50%;" class="text-left">
						<p class="p-text" ><b>Supplier: </b>{{$suppliers['name']}}</p>
							<p class="p-text" ><b>Total Balance:</b> {{(@$supplierBalance<0)?(-1)*$supplierBalance. ' DB':$supplierBalance. ' CR'}}</p>

					</td>
					<td style="font-size: 10px; width: 50%;">
						<p class="p-text" ><b>Date: </b>{{date('M d, Y',strtotime($startDate))}} to {{date('M d, Y',strtotime($endDate))}}</p>
					</td>
				</tr>
			</table>
		<table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
			<tr class="p-text text-bold">
				<th colspan="1">VCH#</th>
				<th colspan="2">Date</th>
				<th colspan="3">Debit</th>
				<th colspan="3">Credit</th>
				<th colspan="3">Balance</th>
			</tr>
			<!-- Table Heading -->
			<!-- Table content row 1 -->
			<?php
			$tbalance=0;
			$total_balance=0;
			?>
			@foreach($debitCredit as $value)
			<?php $chkbala = (int)$value['credit'] - (int)$value['debit'];  $tbalance += $chkbala;
			$total_balance = $tbalance;
			?>
			<tr>
				<td colspan="1" class="p-text">{{$value['voucher_no']}}</td>
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
			<!-- Table content row 1 -->
			<!-- Table total  row -->
			<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
				<td colspan="9"><span class="text-bold">Closing Balance</span></td>
				<?php
				$closing_balance = $total_balance;
				if($closing_balance<0){
					?>
					<td colspan="3" class="text-right p-text">{{-1*($closing_balance)}} DB</td>
					<?php
				}else{
					?>
					<td colspan="3" class="text-right p-text">{{$closing_balance}} CR</td>
					<?php
				}

				?>
			</tr>
		</table>
		@endif
	</center>
</body>

</html>
