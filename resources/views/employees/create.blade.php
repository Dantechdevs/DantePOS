@extends('layouts.layout')
@section('title', '| Add Edit Employees')
@section('content')

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>
						@if(isset($editEmployee))
						Update Employees
						@else
						Manage Employees
						@endif
					</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/home')}}">Dashboard</a></li>
						<li class="breadcrumb-item active">Manage Employees</li>
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
			<form name="employeeForm" id="employeeForm" action="{{(@$editEmployee)?url('edit-employee/'.$editEmployee['id']):url('/create-employee') }}"  method="post" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="id" value="{{@$editEmployee['id']}}">
				<div class="card card-default">

					<div class="card-header">
						<a href="{{ url('/employees') }}" class="btn btn-block btn-success" style="width: 150px; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Employees List</a>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
							<button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fa fa-times"></i></button>
						</div>
					</div>
					<!-- /.card-header -->
					<div class="card-body">
						<!--Row Strat----->
						<div class="form-row">

							<!-- <div class="col-md-6"> -->
								<div class="form-group col-sm-12 col-md-3">
									<label for="name">Employee Name <font style="color: red;">*</font></label>
									<input type="text" class="form-control" name="name" placeholder="Enter Employee Name" id="name" value="{{(@$editEmployee)?$editEmployee['name']:old('name') }}">
								</div>

								<div class="form-group col-sm-12 col-md-3">
									<label for="national_id">National ID <font style="color: red;">*</font></label>
									<input type="text" class="form-control" name="national_id" placeholder="Enter National ID" id="national_id" value="{{(@$editEmployee)?$editEmployee['national_id']:old('national_id') }}">
								</div>
								<div class="form-group col-sm-12 col-md-3">
									<label for="mobile">Mobile No <font style="color: red;">*</font></label>
									<input type="text" class="form-control" name="mobile" placeholder="Enter Mobile No" id="mobile" value="{{(@$editEmployee)?$editEmployee['mobile']:old('mobile') }}">
								</div>


								@if(!@$editEmployee)
								<div class="form-group col-sm-12 col-md-3">
									<label for="salary">Salary </label>
									<input type="text" class="form-control" name="salary" id="salary" placeholder="Joining Salary" value="{{(@$editEmployee)?$editEmployee['salary']:old('salary') }}">
								</div>
								@endif
								<div class="form-group col-sm-12 col-md-12">
									<label for="address">Address <font style="color: red;">*</font></label>
									<textarea class="form-control form-control-sm" id="address" name="address" rows="3">{{(@$editEmployee)?$editEmployee['address']:old('address')}}</textarea>
								</div>


							</div>
							<!--Row Strat----->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<div>
								<button type="submit" class="btn btn-success btn-sm" style="float: right;">{{(@$editEmployee)?'Update Employee':'Add Employee'}}</button>
							</div>
							<div style="margin-right: 150px;">
								<a href="{{'/home'}}"  class="btn btn-warning btn-sm" style="float: right;">Cancel</a>
							</div>
						</div>
					</div>
				</form>
			</div><!-- /.container-fluid -->
		</section>
		<!-- /.content -->
	</div>

	<script type="text/javascript">
		$(document).ready(function () {

			$('#employeeForm').validate({
				rules: {
					gender: {
						required: true,
					},
					religion: {
						required: true,
					},
					name: {
						required: true,
					},
					fname: {
						required: true,
					},
					cnic: {
						required: true,
					},
					mobile: {
						required: true,
					},

					address: {
						required: true,
					},

					dob: {
						required: true,
					},
					salary: {
						required: true,
					},
					join_date: {
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

	@endsection
