@extends('layouts.layout')
@section('title', '| Edit-Supplier')
@section('content')


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Edit Supplier</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Dashboard</a></li>
						<!-- <li class="breadcrumb-item active">Edit Supplier</li> -->
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			
			<!-- SELECT2 EXAMPLE -->
			<form name="supplierForm" id="supplierForm" action="{{ url('edit-supplier/'.$editSupplier['id']) }}"  method="post">
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
					
					<div class="card-header">
						<a href="{{ url('/suppliers') }}" class="btn btn-block btn-success btn-sm" style="width: 150px; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Suppliers List</a>
						
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
								<div class="form-group col-sm-12 col-md-4">
									<label for="name">Name</label>
									<input type="text" class="form-control" name="name"  id="name" value="{{ $editSupplier['name'] }}" required="">
									<font style="color: red;">
										{{($errors->has('name'))?($errors->first('name')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="mobile">Mobile</label>
									<input type="text" class="form-control" value="{{ $editSupplier['mobile'] }}" name="mobile">
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="cnic">CNIC</label>
									<input type="text" class="form-control" name="cnic" value="{{ $editSupplier['cnic'] }}">
								</div>
								<!-- /.col -->
							</div>
							<div class="row">
								<div class="form-group col-sm-12 col-md-6">
									<label for="description">Description</label>
									<textarea class="form-control" id="description" name="description" rows="3">{{ $editSupplier['description'] }}</textarea>
								</div>
								<div class="form-group col-sm-12 col-md-6">
									<label for="address">Address</label>
									<textarea class="form-control" id="address" name="address" rows="3">{{ $editSupplier['address'] }}</textarea>
								</div>
							</div>
							<!--Row Strat----->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<div>
								<button type="submit" class="btn btn-success" style="float: right;">Update Supplier</button>
							</div>
							<div style="margin-right: 150px;">
								<a href="{{'/suppliers'}}"  class="btn btn-warning" style="float: right;">Cancel</a>
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

			$('#supplierForm').validate({
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