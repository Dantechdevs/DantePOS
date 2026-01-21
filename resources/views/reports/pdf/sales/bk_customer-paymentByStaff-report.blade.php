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
					<td colspan="12" align="center" class="p-text"><b>Customer Payments By Staff - Report</b></td>
				</tr>
				<tr>
					<td colspan="6" align="center" class="p-text">
						<b>From Date: {{date('d-m-Y',strtotime($startDate))}}</b>
					</td>
					<td colspan="6" align="center" class="p-text">
						<b>To Date: {{date('d-m-Y',strtotime($endDate))}}</b>
					</td>
				</tr>
				<tr class="p-text text-bold">
					<th colspan="1">VCH#</th>
					<th colspan="2">Date</th>
					<th colspan="2">Customer</th>

					<th colspan="2">Debit</th>
					<th colspan="2">Credit</th>
					<th colspan="3">Staff Member</th>


				</tr>

				<?php
				$tbalanceCredit=0;
				$tbalanceDebit=0;
				?>
				@foreach($debitCredit as $value)
				<?php
				$credit = (int)$value['credit'];
			    $debit = (int)$value['debit'];
				$tbalanceCredit += $credit;
				$tbalanceDebit += $debit;
				?>
				<tr>
					<td colspan="1" class="text-left p-text">{{$value['invoice_no']}}</td>
					<td colspan="2" class="p-text">{{date('d-m-Y',strtotime($value['date']))}}</td>

					<td colspan="2" class="text-center p-text">{{$value['customer']}}</td>
					<td colspan="2" class="text-right p-text">{{$value['debit']}}</td>
					<td colspan="2" class="text-right p-text">{{$value['credit']}}</td>
					<td colspan="3" class="text-center p-text">{{$value['staff']}}</td>

				</tr>
				<?php if(isset($value['description'])){ ?>
				<tr>
					<td colspan="12" class="text-left p-text"> {{$value['description']}}</td>
				</tr>
			<?php } ?>
				@endforeach
				<tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
					<td colspan="5"><span class="text-bold p-text">Total</span></td>

						<td colspan="2" class="text-right p-text">{{$tbalanceDebit}}</td>

						<td colspan="2" class="text-right p-text">{{$tbalanceCredit}}</td>
						<td colspan="3"></td>
				</tr>


			</table>

		</center>
	</body>
	</html>
