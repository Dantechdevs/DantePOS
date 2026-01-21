@extends('layouts.layout')
@section('title', '| Sale Report')
@section('content')

<style type="text/css">
	.customerLoader {
		display: none;
		margin: 0;
		position: absolute;
		top: 50%;
		left: 50%;
		margin-right: -50%;
		transform: translate(-50%, -50%);
	}
</style>

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
						<li class="breadcrumb-item active">Sale Report</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			
			<!-- SELECT2 EXAMPLE -->
			<form name="gateForm" id="gateForm" action="{{ url('/report-sales-pdf') }}"  method="post">
				@csrf
				
				
				<!-- Start another Card Here--------->
				<div class="card card-dark">
					<div class="card-header">
						<h3 class="card-title">Sale Report</h3>
					</div>
					<!-- /.card-header -->
					<!-- form start -->
					<div class="card-body box-profile">
						
						<div class="form-group row">
							<label for="inputFromDate" class="col-sm-2 col-form-label">From Date</label>
							<div class="col-sm-10 col-md-4">
								<input type="text" placeholder="DD-MM-YYYY" class="form-control" name="startDate" id="startDate">
							</div>
							<label for="inputToDate" class="col-sm-2 col-form-label">To Date</label>
							<div class="col-sm-10 col-md-4">
								<input type="text" placeholder="DD-MM-YYYY" class="form-control" name="endDate" id="endDate">
							</div>
						</div>
						
						
						<div class="form-group row customerWise">
							<label for="inputSupplier" class="col-sm-2 col-form-label">Customers</label>
							<div class="col-sm-10 col-md-4">
								<select name="customer_id" id="customer_id" class="form-control select2" style="width: 100%;">
									<option selected="selected" disabled="true">-Select-</option>
									<option value="all" id="all_customers">All</option>
									@foreach($customers as $customer)
									<option value="{{$customer['id']}}">{{$customer['name']}}</option>
									@endforeach
								</select>
							</div>
						</div>
						
						
						
					</div>
					<!-- /.card-body -->
					<div class="card-footer">
						<button type="button" id="show_report" class="btn btn-info">Show Report</button>
						<button type="submit" class="btn btn-primary" id="downloadPdf" style="margin-right: 5px; float:right;">
							<i class="fas fa-download"></i> Generate PDF
						</button>
						<!-- <a class="btn btn-info" id="show_report" style=" color: white">Show Report</a> -->
						<!-- <a href="{{'/dashboard'}}"  class="btn btn-warning float-right">Cancel</a> -->
					</div>
					<!-- /.card-footer -->
				</div>
				<div class="card card-dark">
					<div class="card-body">
						<div class="card-body">
							<div class="customerLoader"><img src="{{asset('images/spiner.gif')}}" style="width: 50px; height: 50px;"></div>
							<div id="DocumentResults"></div>
							<script id="document-template" type="text/x-handlebars-template">
								<table class="table-sm table-bordered table-hover" style="width: 100%">
									<thead>
										<tr style="background-color: gray; color: white;">
											@{{{thsource}}}
										</tr>
									</thead>
									<tbody>
										@{{#each this}}
										<tr>
											@{{{tdsource}}}
										</tr>
										@{{/each}}
									</tbody>
									<tfoot>
										@{{{tfootsource}}}
									</tfoot>
								</table>
							</script>
						</div>
					</div>
				</div>
			</form>

		</div><!-- /.container-fluid -->
	</section>
	<!-- /.content -->
</div>
<script type="text/javascript">
	// $(document).on('change','#sale_type',function(){

	// 		var sale_type = $(this).val();
	// 		// alert(sale_type);
	// 		if(sale_type=='customerWise'){
	// 			$('.customerWise').show();
	// 			$('.areaWise').hide();

	// 		}else if(sale_type=='areaWise'){
	// 			$('.customerWise').hide();
	// 			$('.areaWise').show();
	// 		}
	// 	});

	$(document).on('click','#downloadPdf',function(){

		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();
		// alert(endDate);
		var all_customers = $("#all_customers").val();
		var customer_id = $("#customer_id").val();
		var area_id = $("#area_id").val();

		if(startDate==''){
			$.notify("Start Date is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
		if(endDate==''){
			$.notify("End Date is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
		// if(customer_id==null){
		// 	$.notify("Customer is required", {globalPosition: 'top right',className: 'error'});
		// 	return false;
		// }
	});
</script>
<script type="text/javascript">
	$(document).on('click','#show_report',function(){
		
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();
		// alert(endDate);
		var customer_wise = $('#customer_wise').val();
		
		var sale_type = $('#sale_type').val();
		// alert(sale_type);
		var all_customers = $("#all_customers").val();
		var customer_id = $("#customer_id").val();
		var area_id = $("#area_id").val();

		if(startDate==''){
			$.notify("Start Date is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
		if(endDate==''){
			$.notify("End Date is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
		// if (sale_type == customerWise) {
		// 	alert('ok');
		// 	if(customer_id==null){
		// 	$.notify("Customer is required", {globalPosition: 'top right',className: 'error'});
		// 	return false;
		// }
		// }
		// if (sale_type == areaWise) {
		// 	if(area_id==null){
		// 	$.notify("Area is required", {globalPosition: 'top right',className: 'error'});
		// 	return false;
		// }
		// }

		
		$.ajax({ 
			url: '/report-get-sales',
			type: 'GET',
			data:{
				startDate:startDate,
				endDate:endDate,
				customer_id:customer_id,
				customer_wise:customer_wise,
				sale_type:sale_type,
				area_id:area_id,
			},
			beforSend: function(){
				$('.customerLoader').show();
			},
			success: function(data){
				
				if (data=='false') {
					$.notify("there is no data found!", {globalPosition: 'top right',className: 'error'});
					return false;
				}
				var source = $("#document-template").html();
				var template = Handlebars.compile(source);
				var html = template(data);
				$('#DocumentResults').html(html);
				$('[data-toggle="tooltip"]').tooltip();
				$('.customerLoader').hide();
			}
		});
	});
</script>

@endsection