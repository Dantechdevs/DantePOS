@extends('layouts.layout')
@section('title', '| Employees Attendance')
@section('content')
<style type="text/css">
	.switch-toggle {
		width: auto;
	}
	.switch-toggle label:not(.disabled) {
		cursor: pointer;
	}
	.switch-candy a {
		border: 1px solid #333;
		border-radius: 3px;
		box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2), inset  0 1px  1px rgba(255, 255, 255, 0.45);
		background-color: white;
		background-image: -webkit-linear-gradient(top, rgba(255, 255, 255, 0.2), transparent);
		background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0.2), transparent);
	}
	.switch-toggle.switch-candy, .switch-light.switch-candy > span {
		background-color: #5a6268;
		border-radius: 3px;
		box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3), 0 1px 0 rgba(255, 255, 255, 0.2);
	}
</style>
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>Employees Attendance</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
						<li class="breadcrumb-item active">Employees Attendance</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-12">


				<div class="card">
					<div class="card-header">
						<h3 class="card-title">
							@if(isset($editData))
							Edit Employee Attendance
							@else
							Add Employee Attendance
							@endif
						</h3>
						<a href="{{ url('/employees-attendance') }}" class="btn btn-block btn-success btn-sm" style="width: 180px; float: right; display: inline-block;"><i class="fa fa-list">&nbsp;Attendance List</i></a>
					</div>
					<!-- /.card-header -->
					<form method="post" action="{{url('/add-employee-attendance')}}" id="attendanceForm">@csrf
						@if(isset($editData))
						<div class="card-body">
							<div class="form-group col-md-4">
								<label class="control-label">Attendance Date</label>
								<input type="text" value="{{date('d-m-Y',strtotime($editData['0']['date']))}}" name="date" id="datepicker" class="form-control form-control-sm">
							</div>
							<table class="table-sm table-bordered table->striped dt-responsive" style="width: 100%">
								<thead>
									<tr>
										<th rowspan="2" class="text-center" style="vertical-align: middle;">SL.</th>
										<th rowspan="2" class="text-center" style="vertical-align: middle;">Employee Name</th>
										<th rowspan="2" class="text-center" style="vertical-align: middle;">Wage Type</th>
										<th colspan="3" class="text-center" style="vertical-align: middle; width: 25%">Attendance Status</th>
									</tr>
									<tr>
										<th class="text-center btn present_all" style="display: table-cell; background-color: #114190">Present</th>
										<th class="text-center btn leave_all" style="display: table-cell; background-color: #114190">Leave</th>
										<th class="text-center btn absent_all" style="display: table-cell; background-color: #114190">Absent </th>
									</tr>
								</thead>
								<tbody>
									@foreach($editData as $key => $data)
									<tr id="div{{$data->id}}" class="text-center">
										<input type="hidden" name="employee_id[]" value="{{$data->employee_id}}" class="employee_id">
										<td>{{$key+1}}</td>
										<td>{{$data['employee']['name']}}</td>
										<td>{{$data['employee']['wage_type']}}</td>
										<td colspan="3">
											<div class="switch-toggle switch-3 switch-candy">
												<input type="radio" class="present" id="present{{$key}}" name="attend_status{{$key}}" value="Present" {{($data->attend_status=='Present')?'checked':''}} />
												<label for="present{{$key}}">Present</label>

												<input type="radio" id="leave{{$key}}" name="attend_status{{$key}}" value="Leave" {{($data->attend_status=='Leave')?'checked':''}} />
												<label for="leave{{$key}}">Leave</label>

												<input type="radio" id="absent{{$key}}" name="attend_status{{$key}}" value="Absent" {{($data->attend_status=='Absent')?'checked':''}} />
												<label for="absent{{$key}}">Absent</label>
												<a></a>
											</div>
										</td>
									</tr>
									@endforeach
								</tbody>
							</table><br>
							<button type="submit" class="btn btn-success btn-sm">{{(@$editData)?'Update':'Submit'}}</button>
						</div>
						@else
						<div class="card-body">
							<div class="form-group col-md-4">
								<label class="control-label">Attendance Date</label>
								<input type="text" name="date" id="datepicker" placeholder="DD-MM-YYYY" class="form-control form-control-sm">
							</div>
							<table class="table-sm table-bordered table->striped dt-responsive" style="width: 100%">
								<thead>
									<tr>
										<th rowspan="2" class="text-center" style="vertical-align: middle;">SL.</th>
										<th rowspan="2" class="text-center" style="vertical-align: middle;">Employee Name</th>
										<th rowspan="2" class="text-center" style="vertical-align: middle;">Wage Type</th>
										<th colspan="3" class="text-center" style="vertical-align: middle; width: 25%">Attendance Status</th>
									</tr>
									<tr>
										<th class="text-center btn present_all" style="display: table-cell; background-color: #114190">Present</th>
										<th class="text-center btn leave_all" style="display: table-cell; background-color: #114190">Leave</th>
										<th class="text-center btn absent_all" style="display: table-cell; background-color: #114190">Absent </th>
									</tr>
								</thead>
								<tbody>
									@foreach($employees as $key => $employee)
									<tr id="div{{$employee->id}}" class="text-center">
										<input type="hidden" name="employee_id[]" value="{{$employee->id}}" class="employee_id">
										<input type="hidden" name="wage_type[]" value="{{$employee->wage_type}}" class="wage_type">
										<td>{{$key+1}}</td>
										<td>{{$employee->name}}</td>
										<td>{{$employee->wage_type}}</td>
										<td colspan="3">
											<div class="switch-toggle switch-3 switch-candy">
												<input type="radio" class="present" id="present{{$key}}" name="attend_status{{$key}}" value="Present" checked="checked" />
												<label for="present{{$key}}">Present</label>

												<input type="radio" id="leave{{$key}}" name="attend_status{{$key}}" value="Leave" />
												<label for="leave{{$key}}">Leave</label>

												<input type="radio" id="absent{{$key}}" name="attend_status{{$key}}" value="Absent"/>
												<label for="absent{{$key}}">Absent</label>
												<a></a>
											</div>
										</td>
									</tr>
									@endforeach
								</tbody>
							</table><br>
							<button type="submit" class="btn btn-success btn-sm">{{(@$editData)?'Update':'Submit'}}</button>
						</div>
						@endif
					</form>

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
<script type="text/javascript">
	$(document).on('click','.present',function(){
		$(this).parents('tr').find('.datetime').css('pointer-events','none').css('background-color','white').css('color','#495057');
	});
	$(document).on('click','.leave',function(){
		$(this).parents('tr').find('.datetime').css('pointer-events','').css('background-color','white').css('color','#495057');
	});
	$(document).on('click','.absent',function(){
		$(this).parents('tr').find('.datetime').css('pointer-events','none').css('background-color','white').css('color','#495057');
	});
</script>
<script type="text/javascript">
	$(document).on('click','.present_all',function(){
	  $("input[value=Present]").prop('checked',true);
	  $('.datetime').css('pointer-events','none').css('background-color','#dee2e6').css('color','#495057');
	});
	$(document).on('click','.leave_all',function(){
	  $("input[value=Leave]").prop('checked',true);
	  $('.datetime').css('pointer-events','').css('background-color','white').css('color','#495057');
	});
	$(document).on('click','.absent_all',function(){
	  $("input[value=Absent]").prop('checked',true);
	  $('.datetime').css('pointer-events','none').css('background-color','#dee2e6').css('color','#495057');
	});
</script>
<script type="text/javascript">
		$(document).ready(function () {

			$('#attendanceForm').validate({
				rules: {
					date: {
						required: true
					}
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
