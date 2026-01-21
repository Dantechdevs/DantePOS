@extends('layouts.layout')
@section('title', '| Balance Sheet')
@section('content')



<div class="content-wrapper">
	<!-- Content Header (Page header) -->


	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-12">


				<div class="card">

					<!-- /.card-header -->
					<div class="card-body">


						<table class="table-suply" width="100%" cellspacing="0" cellpadding="0" border="1">
							<tr>
								<td colspan="12" align="center" class="text-bold">Balance Sheet  {{ $date }}</td>
							</tr>
							<tr class="p-text text-bold">
								<th colspan="1" width="3%">#</th>
								<th colspan="1" width="15%">Date</th>
								<th colspan="4" width="39%">Description</th>
								<th colspan="2" width="13%">Debit</th>
								<th colspan="2" width="13%">Credit</th>
								<th colspan="2" width="25%">Balance</th>


							</tr>
							<tr>

								<td colspan="10" width="87%" class="text-left p-text">Opening Balance</td>

								<td colspan="2" width="13%" class="text-right p-text">{{(@$openingBalance)?$openingBalance:0}}</td>
							</tr>
							<?php
							$tbalance= (@$openingBalance)?$openingBalance:0;
							// dd($tbalance);
							$total_balance=0;
							$counter = 0;
							?>
							@foreach($creditDebit as $value)
							<?php $chkbala = (int)$value['credit'] - (int)$value['debit'];  $tbalance += $chkbala;
							$total_balance = $tbalance;
							$counter = $counter+1;
							?>
							<tr>
								<td colspan="1" width="3%" class="p-text">{{$counter}}</td>
								<td colspan="1" width="15%" class="p-text">{{date('d-m-Y',strtotime($value['date']))}}</td>
								<td colspan="4" width="39%" class="text-left p-text">{{$value['description']}}</td>
								<td colspan="2" width="13%" class="text-right p-text">{{$value['debit']}}</td>
								<td colspan="2" width="13%" class="text-right p-text">{{$value['credit']}}</td>

								<td colspan="2" width="25%" class="text-right p-text">{{$total_balance}}</td>

							</tr>

							@endforeach
							<tr style="background-color: #FBCEB1;">
								<td colspan="10"><span class="text-bold">Closing Balance</span></td>


								<td colspan="2" class="text-right p-text">{{(@$total_balance)?$total_balance:$tbalance}}</td>

							</tr>


						</table>

					</div>
					<!-- /.card-body -->
				</div>
				<!-- /.card -->
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
@endsection
