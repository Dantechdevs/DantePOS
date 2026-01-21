@extends('layouts.layout')
@section('title', '| Add Expense Category')
@section('content')


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Add Expense Category</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Dashboard</a></li>
						<!-- <li class="breadcrumb-item active">Add Expense Category</li> -->
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			
			<!-- SELECT2 EXAMPLE -->
			<form name="customerForm" id="customerForm" action="{{ url('add-expense-category') }}"  method="post">
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
						<a href="{{ url('/expense-categories') }}" class="btn btn-block btn-success btn-sm" style="width: 180px; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Expense Categories</a>
						
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
							<button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fa fa-times"></i></button>
						</div>
					</div>
					<!-- /.card-header -->
					<div class="card-body">
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
						
						<!--Row Strat----->
						<div class="row"> 
							<!-- <div class="col-md-6"> -->
								<div class="form-group col-sm-12 col-md-4">
									<label for="name">Name</label>
									<input type="text" class="form-control" name="name" placeholder="Enter Expense Category" id="name" value="{{ old('name') }}" required="">
									<font style="color: red;">
										{{($errors->has('name'))?($errors->first('name')):''}}
									</font>
								</div>
							</div>
							<!--Row Strat----->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<div>
								<button type="submit" class="btn btn-success" style="float: right;">Add Expense Category</button>
							</div>
							<div style="margin-right: 180px;">
								<a href="{{'/expense-categories'}}"  class="btn btn-warning" style="float: right;">Cancel</a>
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
	</script>
	@endsection