@extends('layouts.layout')
@section('title', '| Customer Payments By Staff Report')
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
						<li class="breadcrumb-item"><a href="{{ url('/home')}}">Dashboard</a></li>
						<li class="breadcrumb-item active">Customer Payments By Staff Report</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">

			<!-- SELECT2 EXAMPLE -->
			<form name="customerPaymentByStaffForm" id="customerPaymentByStaffForm" action="{{ url('/report-paymentsByStaff-pdf') }}"  method="post">
				@csrf


				<!-- Start another Card Here--------->
				<div class="card card-dark">
					<div class="card-header">
						<h3 class="card-title">Customer Payments By Staff Report</h3>
					</div>
					<!-- /.card-header -->
					<!-- form start -->
					<div class="card-body box-profile">

						<div class="form-group row">
							<label for="inputFromDate" class="col-sm-2 col-form-label">From Date</label>
							<div class="col-sm-10 col-md-4">
								<input type="text" placeholder="DD-MM-YYYY" class="form-control" name="startDate" id="startDate" readonly>
							</div>
							<label for="inputToDate" class="col-sm-2 col-form-label">To Date</label>
							<div class="col-sm-10 col-md-4">
								<input type="text" placeholder="DD-MM-YYYY" class="form-control" name="endDate" id="endDate" readonly>
							</div>
						</div>


						<div class="form-group row customerWise">
							<label for="inputSupplier" class="col-sm-2 col-form-label">Staff</label>
							<div class="col-sm-10 col-md-4">
								<select name="user_id" id="user_id" class="form-control select2" style="width: 100%;">
									<option selected="selected" disabled="true">-Select-</option>
									<option value="all" id="all_users">All</option>
									@foreach($users as $user)
									<option value="{{$user['id']}}">{{$user['name']}}</option>
									@endforeach
								</select>
							</div>
						</div>



					</div>
					<!-- /.card-body -->
					<div class="card-footer">
						<!-- <button type="button" id="show_report" class="btn btn-info showUserSaleBtn">Show Report</button> -->
						<button type="submit" class="btn btn-primary" id="downloadPdf" style="margin-right: 5px; float:right;">
							<i class="fas fa-download"></i> Generate PDF
						</button>

					</div>
					<!-- /.card-footer -->
				</div>

			</form>

		</div><!-- /.container-fluid -->
	</section>
	<!-- /.content -->
</div>
<script type="text/javascript">


	$(document).on('click','#downloadPdf',function(){

		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();
		// alert(endDate);
		var all_users = $("#all_users").val();
		var user_id = $("#user_id").val();

		if(startDate==''){
			$.notify("Start Date is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
		if(endDate==''){
			$.notify("End Date is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
		if(user_id==null){
			$.notify("Staff is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
	});
</script>


@endsection
