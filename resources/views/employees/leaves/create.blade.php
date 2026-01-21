@extends('layouts.layout')
@section('title', '| Employees Leaves')
@section('content')


<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>
						@if(isset($editData))
						Update Employee Leaves
						@else
						Add Employee Leaves
						@endif
					</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/home')}}">Dashboard</a></li>
						<li class="breadcrumb-item active">Employee Leaves</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">

			<!-- SELECT2 EXAMPLE -->
			<form name="employeeLeaveForm" id="employeeLeaveForm" action="{{(@$editData)?url('edit-employee-leave/'.$editData['id']):url('/add-employee-leave') }}"  method="post">
				@csrf

				<div class="card card-default">

					<div class="card-header">
						<a href="{{ url('/employees-leaves') }}" class="btn btn-block btn-success" style="width: 150px; display: inline-block;"><i  class="fa fa-list"></i>&nbsp;&nbsp;Leave List</a>
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
									<label for="class">Employee Name <font style="color: red;">*</font></label>
									<select name="employee_id" id="employee_id" class="form-control select2" style="width: 100%;">
										<option selected="selected" disabled="true">Select Employee</option>
										@foreach($employees as $employee)
										<option value="{{ $employee->id }}" {{(@$editData->employee_id==$employee->id)?'selected':''}}> {{ $employee['name'] }}
										</option>
										@endforeach
									</select>
								</div>


								<div class="form-group col-sm-12 col-md-4">
									<label for="start_date">Start Date</label>
									<input type="text" class="form-control" name="start_date" value="{{@$editData->start_date}}" id="startDate" placeholder="DD-MM-YYYY">
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="end_date">End Date</label>
									<input type="text" class="form-control" name="end_date" value="{{@$editData->end_date}}" id="endDate" placeholder="DD-MM-YYYY">
									<font style="color: red;">
										{{($errors->has('end_date'))?($errors->first('end_date')):''}}
									</font>
								</div>
								<div class="form-group col-sm-12 col-md-4">
									<label for="leavePurpose">Leave Purpose <font style="color: red;">*</font></label>
									<select name="employee_leave_id" id="employee_leave_id" class="form-control select2" style="width: 100%;">
										<option selected="selected" disabled="true">Select Purpose</option>
										@foreach($leavePurpose as $leave)
										<option value="{{ $leave->id }}" {{(@$editData['employee_leave_id']==$leave->id)?'selected':''}}> {{ $leave->name }}
										</option>
										@endforeach
										<option value="0">New Purpose</option>
									</select>
								</div>
								<div class="form-group col-sm-12 col-md-4" id="other_purpose" style="display: none;">
									<label for="leave_purpose">Leave Purpose</label>
									<input type="text" class="form-control" name="name" placeholder="Write your purpose">
								</div>

							</div>
							<!--Row Strat----->
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<div>
								<button type="submit" class="btn btn-success" style="float: right;">{{(@$editData)?'Update':'Add Leave'}}</button>
							</div>
							<div style="margin-right: 130px;">
								<a href="{{'/dashboard'}}"  class="btn btn-warning" style="float: right;">Cancel</a>
							</div>
						</div>
					</div>
				</form>
			</div><!-- /.container-fluid -->
		</section>
		<!-- /.content -->
	</div>


	@endsection

    @push('custom-script')
<script type="text/javascript">
		$(document).ready(function () {

			$('#employeeLeaveForm').validate({
				rules: {
					name: {
						required: true,
						name: true,
					},
					terms: {
						required: true
					},
				},
				messages: {
					name: {
						required: "Please enter group name",
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
	<script type="text/javascript">
		$(document).ready(function(){
			$(document).on('change','#employee_leave_id',function(){
				var employee_leave_id = $(this).val();
				if (employee_leave_id==  '0') {
					$("#other_purpose").show();
				}else{
					$("#other_purpose").hide();
				}
			});
		});
	</script>
    @endpush
