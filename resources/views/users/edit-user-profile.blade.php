@extends('layouts.layout')
@section('title', '| Update User Profile')
@section('content')


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Update User Profile</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/home')}}">Dashboard</a></li>
						<li class="breadcrumb-item active">Update Profile</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			
			<!-- SELECT2 EXAMPLE -->
			<form name="userForm" id="userForm" action="{{ url('/edit-user-profile/'.$editUser['id']) }}"  method="post" enctype="multipart/form-data">
				@csrf
				
				<div class="card card-default">

					<div class="card-header">
						<a href="{{ url('/user-profile') }}" class="btn btn-block btn-success btn-sm" style="width: 150px; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Profile</a>
						
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
									<label for="name">User Name</label>
									<input type="text" class="form-control" name="name" placeholder="Enter User Name" id="name" value="{{ $editUser['name'] }}">
									<font style="color: red;">
										{{($errors->has('name'))?($errors->first('name')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="email">User Email</label>
									<input type="email" class="form-control" name="email" placeholder="Enter User Email" id="email" value="{{ $editUser['email'] }}" readonly="">
									<font style="color: red;">
										{{($errors->has('email'))?($errors->first('email')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="mobile">Mobile</label>
									<input type="text" class="form-control" name="mobile" placeholder="Enter User Name" id="mobile" value="{{ $editUser['mobile'] }}">
									<font style="color: red;">
										{{($errors->has('mobile'))?($errors->first('mobile')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="gender">Gender</label>
									<select name="gender" id="gender" class="form-control select2" style="width: 100%;">
										<option selected="selected" disabled="true">Select Gender</option>
										<option value="Male" {{($editUser['gender']=="Male")?"selected":""}}>Male</option>
										<option value="Female" {{($editUser['gender']=="Female")?"selected":""}}>Female</option>
									</select>
									<font style="color: red;">
										{{($errors->has('gender'))?($errors->first('gender')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="address">Address</label>
									<textarea class="form-control" id="address" name="address" rows="3">{{ $editUser['address'] }}</textarea>
									<font style="color: red;">
										{{($errors->has('address'))?($errors->first('address')):''}}
									</font>
								</div>
								

								
								
								<!-- /.col -->
							</div>
							<!--Row Strat----->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<div>
								<button type="submit" class="btn btn-success" style="float: right;">Update Prfile</button>
							</div>
							<div style="margin-right: 150px;">
								<a href="{{ url('/view-users') }}"  class="btn btn-warning" style="float: right;">Cancel</a>
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

			$('#userForm').validate({
				rules: {
					usertype: {
						required: true,
						usertype: true,
					},
					name: {
						required: true,
						name: true,
					},
					email: {
						required: true,
						email: true,
					},
				},
				messages: {
					usertype: {
						required: "Please select User Role",
					},
					name: {
						required: "Please enter Name",
					},
					email: {
						required: "Please enter a email address",
						email: "Please enter a <em>vaild</em> email address"
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