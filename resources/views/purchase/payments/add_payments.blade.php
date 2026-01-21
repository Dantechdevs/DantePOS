@extends('layouts.layout')
@section('title', '| Purchase Payments')
@section('content')



<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Purchase Payments</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
						<li class="breadcrumb-item active">Purchase Payments</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-12">

				<form action="{{ url('supplier-payment/'.$purchase_payment['purchase_id']) }}" method="post" id="purchaseForm">@csrf
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Purchase Payments</h3>
						<a href="{{ url('/purchase-orders') }}" class="btn btn-block btn-success" style="width: 200px; float: right; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Purchase Orders</a>
					</div>
					<!-- /.card-header -->
					<div class="card-body">
						<table width="100%" style="margin-bottom: 20px;">
							<tbody>
								<tr>
									<td colspan="3"><strong>Supplier Information: </strong></td>
								</tr>
								<tr>
									<td width="10%"><strong>Purchase #: </strong>{{$purchase_payment['purchase']['purchase_no']}}</td>
									<td width="20%"><strong>Supplier: </strong>{{$purchase_payment['suppliers']['name']}}</td>
									<td width="20%"><strong>Mobile: </strong>{{$purchase_payment['suppliers']['mobile']}}</td>
									<td width="50%"><strong>Address: </strong>{{$purchase_payment['suppliers']['address']}}</td>
								</tr>
							</tbody>
						</table>
						<table border="1" width="100%" style="margin-bottom: 10px;">
							<thead>
								<th>SL.</th>
								<th>Product Name</th>
								<th>Quantity</th>
								<th>Unit Price</th>
								<th>Total Price</th>
							</thead>
							<tbody>
								@php
								$total_sum = '0';
								@endphp
								@foreach($purchaseItemAddons as $key => $details)
								<tr class="text-center">
								<td>{{$key+1}}</td>
								<td>{{$details['productName']}}</td>
								<td>{{$details['quantity']}}</td>
								<td>{{$details['price']}}</td>
								<td>{{$details['amount']}}</td>
								</tr>
								@php
								$total_sum += $details['amount'];
								@endphp
								@endforeach
								<tr>
									<td colspan="4" class="text-right"><strong>Sub Total</strong></td>
									<td class="text-center"><strong>{{$total_sum}}</strong></td>
								</tr>
								<tr>
									<td colspan="4" class="text-right"><strong>Paid Amount </strong></td>
									<td class="text-center"><strong>{{$purchase_payment['paid_amount']}}</strong></td>
								</tr>
								<tr>
									<td colspan="4" class="text-right"><strong>Due Amount </strong></td>
									<input type="hidden" name="new_paid_amount" value="{{$purchase_payment['due_amount']}}">
									<td class="text-center"><strong>{{$purchase_payment['due_amount']}}</strong></td>
								</tr>
								<tr>
									<td colspan="4" class="text-right"><strong>Grand Total </strong></td>
									<td class="text-center"><strong>{{$purchase_payment['total_amount']}}</strong></td>
								</tr>
							</tbody>
						</table>
						<div class="form-row">
							<div class="form-group col-sm-12 col-md-3">
									<label for="date">Date <font style="color: red;">*</font></label>
									<input type="text" name="date" id="datepicker" placeholder="DD-MM-YYYY" class="form-control" value="{{ old('date') }}">
									<font style="color: red;">
										{{($errors->has('date'))?($errors->first('date')):''}}
									</font>
								</div>
							<div class="form-group col-sm-12 col-md-3">
								<label>Paid Status</label>
								<select name="paid_status" id="paid_status" class="form-control select2">
									<option value="" selected="selected" disabled="true">Select Status</option>
									<option value="full_paid">Full Paid</option>
									<option value="partial_paid">Partial Paid</option>
								</select>
								
							</div>
							<div class="form-group col-sm-12 col-md-2" id="paid_amount" style="display: none;">
								<label>Paid Amount</label>
								
								<input type="text" name="paid_amount" class="form-control" placeholder="Enter Paid Amount">
							</div>
							
						</div>
						<br>
						<div class="form-group">
							<button type="submit" class="btn btn-success" id="storeButton" style="float: right;">Add Payment</button>
						</div>
					</div>

					<!-- /.card-body -->
				</div>
				<!-- /.card -->
			</form>
			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
<script type="text/javascript">
		$(document).ready(function () {

			$('#purchaseForm').validate({
				rules: {
					date: {
						required: true,
					},
					paid_status: {
						required: true,
					},
					terms: {
						required: true
					},
				},
				messages: {
					
				},
				errorElement: 'span',
				errorPlacement: function (error, element) {
					error.addClass('invalid-feedback');
					element.closest('.form-group').append(error);
				},
				highlight: function (element, errorClass, validClass) {
					$(element).addClass('is-invalid');
				},
				unhighlight: function (element, errorClass, validClass) {
					$(element).removeClass('is-invalid');
				}
			});
		});
	</script>
<script type="text/javascript">
		$(document).on('change','#paid_status',function(){
			var paid_status = $(this).val();
			if(paid_status=='partial_paid'){
				$('#paid_amount').show();
			}else{
				$('#paid_amount').hide();
			}
		});
		
	</script>
@endsection