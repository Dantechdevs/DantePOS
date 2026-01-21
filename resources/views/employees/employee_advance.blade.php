@extends('layouts.layout')
@section('title', '| Employee Advance')
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
						<li class="breadcrumb-item active">Employee Advance</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger">

				<strong> {!! session('flash_message_error') !!} </strong>
			</div>

			@endif
			@if(Session::has('flash_message_success'))
			<div class="alert alert-success">

				<strong> {!! session('flash_message_success') !!} </strong>
			</div>
			@endif
			<!-- SELECT2 EXAMPLE -->
			<form name="gateForm" id="gateForm" action="{{ url('employee-advance-salary/'.$employee['id']) }}"  method="post">
				@csrf
				
				<!-- Start another Card Here--------->
				<div class="card card-dark">
					<div class="card-header">
						<h3 class="card-title">Add Employee Advance</h3>
						<a href="{{ url('/employees') }}" class="btn btn-block btn-warning btn-sm" style="width: 120px; float: right; display: inline-block; color: black;"><i  class="fa fa-list"></i>&nbsp;&nbsp; Employees</a>
					</div>
					<!-- /.card-header -->
					<!-- form start -->
					<form class="form-horizontal">
						<div class="card-body box-profile">
							
							<div class="form-group row">
									<label for="inputDate" class="col-sm-2 col-form-label">Date</label>
									<div class="col-sm-10 col-md-4">
										<input type="text" class="form-control" name="date" id="datepicker" placeholder="DD-MM-YYYY">
									</div>

									<label for="inputAdvance" class="col-sm-2 col-form-label">Advance</label>
								<div class="col-sm-10 col-md-4">
									<input type="text" class="form-control" name="advance_amount" placeholder="Enter Advance Amount" id="advance_amount" value="{{ old('advance_amount') }}" required="">
								</div>
								<input type="hidden" name="status" value="1">
								
							</div>
							<div class="form-group row">
								<label for="inputDate" class="col-sm-2 col-form-label">Description</label>
								<div class="col-sm-10 col-md-4">
									<textarea class="form-control" name="description" placeholder="Enter Advance Amount" id="description"></textarea>
								</div>
								
							</div>
							


						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button type="submit" class="btn btn-info">Add Advance</button>
							
							<a href="{{'/dashboard'}}"  class="btn btn-warning float-right">Cancel</a>
						</div>
						<!-- /.card-footer -->
					</form>
				</div>
			</form>
		</div><!-- /.container-fluid -->
	</section>
	<!-- /.content -->
</div>

<script type="text/javascript">
	$(document).ready(function () {

		$('#gateForm').validate({
			rules: {
				gate_id: {
					required: true
				},
				date: {
					required: true
				},
				terms: {
					required: true
				},
			},
			messages: {
				gate_id: {
					required: "Please enter Name",
				},
				date : {
					required : 'Please select date',
				}
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
@endsection