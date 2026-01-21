@extends('layouts.layout')
@section('title', '| Update Password')
@section('content')


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				
				<div class="col-sm-12">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
						<li class="breadcrumb-item active">Change Password</li>
					</ol>
				</div>
				<div class="col-sm-12">
					<h1 style="text-align: center;">Update Password</h1>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-12 offset-md-4">


				<!-- Profile Image -->
				<div class="card card-primary card-outline col-md-4">
					<div class="card-body box-profile">
						<div class="text-center">
							<img class="profile-user-img img-fluid img-circle"
							src="{{asset('images/avatar5.png')}}"
							alt="User profile picture" style="height: 75px; width: 80px;">
						</div>

						<h3 class="profile-username text-center">{{$adminDetails['name']}}</h3>

						<p class="text-muted text-center">Admin</p>

						<form role="form" action="{{ url('/update-password') }}" method="post" name="updatePasswordForm" id="updatePasswordForm">
							@csrf
							<div class="card-body">
								
								<div class="form-group">
									<label for="exampleInputPassword1">Current Password</label>
									<input type="password" class="form-control" placeholder="Current Password" id="current_pwd" name="current_pwd">
									<span id="chkCurrentPwd"></span>
								</div>
								<div class="form-group">
									<label for="exampleInputPassword1">New Password</label>
									<input type="password" class="form-control" id="new_pwd" name="new_pwd" placeholder="New Password">
								</div>
								<div class="form-group">
									<label for="exampleInputPassword1">Confirm Password</label>
									<input type="password" class="form-control" id="confirm_pwd" name="confirm_pwd" placeholder="Confirm Password">
								</div>


							</div>
							<!-- /.card-body -->

							<div class="card-footer">
								<button type="submit" class="btn btn-primary" style="width: 100%;">Update Password</button>
							</div>
						</form>


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