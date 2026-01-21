@extends('layouts.layout')
@section('title', '| Create-Customer')
@section('content')


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Add Customer</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Dashboard</a></li>
						<!-- <li class="breadcrumb-item active">Add Customer</li> -->
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			
			<!-- SELECT2 EXAMPLE -->
			<form name="customerForm" id="customerForm" action="{{ url('create-customer') }}"  method="post">
				@csrf
				
				<div class="card card-default">
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
					<!-- Laravel Validations Errors------>
					@if ($errors->any())
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
					@endif
					<div class="card-header">
						<a href="{{ url('/customers') }}" class="btn btn-block btn-success btn-sm" style="width: 150px; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Customers List</a>
						
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
							<button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fa fa-times"></i></button>
						</div>
					</div>
					<!-- /.card-header -->
					<div class="card-body">
						<!--Row Strat----->
						<div class="row"> 
							<!-- <div class="col-md-6"> -->
								<div class="form-group col-sm-12 col-md-3">
									<label for="name">Name</label>
									<input type="text" class="form-control" name="name" placeholder="Enter Customer Name" id="name" value="{{ old('name') }}">
									<font style="color: red;">
										{{($errors->has('name'))?($errors->first('name')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-3">
									<label for="mobile">Mobile</label>
									<input type="text" placeholder="Enter Mobile Number" class="form-control" name="mobile" id="mobile">
								</div>
								<div class="form-group col-sm-12 col-md-3">
									<label for="cnic">CNIC</label>
									<input type="text" placeholder="Enter CNIC" class="form-control" name="cnic">
								</div>
								<div class="form-group col-sm-12 col-md-3">
									<label for="area">Area <font style="color: red;">*</font></label>
									<select name="area_id" id="area_id" class="form-control select2" style="width: 100%;">
										<option selected="selected" disabled="true">-Select-</option>
										@foreach($areas as $area)
										<option value="{{$area['id']}}">{{$area['name']}}</option>
										@endforeach
									</select>
									<font style="color: red;">
										{{($errors->has('name'))?($errors->first('name')):''}}
									</font>
								</div>
								<!-- /.col -->
							</div>
							<div class="row">
								<div class="form-group col-sm-12 col-md-12">
									<label for="address">Address</label>
									<textarea class="form-control" id="description" name="address" rows="3" placeholder="Enter Customer Address"></textarea>
								</div>
							</div>
							<!--Row Strat----->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<div>
								<button type="submit" class="btn btn-success" style="float: right;">Create Customer</button>
							</div>
							<div style="margin-right: 140px;">
								<a href="{{'/customers'}}"  class="btn btn-warning" style="float: right;">Cancel</a>
							</div>
						</div>
					</div>
				</form>
			</div><!-- /.container-fluid -->
		</section>
		<!-- /.content -->
	</div>
	<script type="text/javascript">
		// validate signup form on keyup and submit
		$(document).ready(function () {
			$("#customerForm").validate({
				rules: {
					name: {
						required: true,
						remote: "check-customername"
					},
					mobile: {
						required: true,
						remote: "customer-number",
						digits: true
					},
					area_id: {
						required: true
					}
					
				},
				messages: {
					name: {
						required: "Please enter a Name",
						remote: "The Name is Already Exist"
					},
					mobile: {
						required: "Please enter you Mobile",
						remote: "The Number is Already Exist"
					},
					area_id: {
						required: "Please select area"
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
	<!-- <script type="text/javascript">
		$(document).ready(function () {

			$('#customerForm').validate({
				rules: {
					name: {
						required: true
					},
					
				},
				messages: {
					name: {
						required: "Please enter Name",
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
	</script> -->
	@endsection