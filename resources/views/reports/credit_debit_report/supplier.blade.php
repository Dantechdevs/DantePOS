@extends('layouts.layout')
@section('title', '| Supplier Credit/Debit Report')
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
						<li class="breadcrumb-item active">Supplier Credit/Debit Report</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			
			<!-- SELECT2 EXAMPLE -->
			<form name="gateForm" id="gateForm" action="{{ url('/report-supplier-credit-debit-pdf') }}"  method="post">
				@csrf
				
				
				<!-- Start another Card Here--------->
				<div class="card card-dark">
					<div class="card-header">
						<h3 class="card-title">Supplier Credit/Debit Report</h3>
					</div>
					<!-- /.card-header -->
					<!-- form start -->
					<div class="card-body box-profile">
						
						
						
						
						<div class="form-group row">
							<label for="supplier" class="col-sm-2 col-form-label">Suppliers</label>
							<div class="col-sm-10 col-md-4">
								<select name="supplier_id" id="supplier_id" class="form-control select2" style="width: 100%;">
										<option selected="selected" disabled="true">-Select-</option>
										<option value="all">All</option>
										@foreach($suppliers as $supplier)
										<option value="{{$supplier['id']}}">{{$supplier['name']}}</option>
										@endforeach
									</select>
							</div>
						</div>
						<div class="form-group row customerWise" style="display: none;">
							<label for="inputFromDate" class="col-sm-2 col-form-label">From Date</label>
							<div class="col-sm-10 col-md-4">
								<input type="text" placeholder="DD-MM-YYYY" class="form-control" name="startDate" id="startDate">
							</div>
							<label for="inputToDate" class="col-sm-2 col-form-label">To Date</label>
							<div class="col-sm-10 col-md-4">
								<input type="text" placeholder="DD-MM-YYYY" class="form-control" name="endDate" id="endDate">
							</div>
						</div>
						
						
					</div>
					<!-- /.card-body -->
					<div class="card-footer">
						
						<button type="submit" class="btn btn-primary" id="downloadPdf" style="margin-right: 5px; float:right;">
							<i class="fas fa-download"></i> Generate PDF
						</button>
						<!-- <a class="btn btn-info" id="show_report" style=" color: white">Show Report</a> -->
						<!-- <a href="{{'/dashboard'}}"  class="btn btn-warning float-right">Cancel</a> -->
					</div>
					<!-- /.card-footer -->
				</div>
				
			</form>

		</div><!-- /.container-fluid -->
	</section>
	<!-- /.content -->
</div>
<script type="text/javascript">
	$(document).on('change','#supplier_id',function(){

				var supplier_id = $(this).val();
			// alert(sale_type);
			if(supplier_id=='all'){
				$('.customerWise').hide();

			}else{
				$('.customerWise').show();
			}
		});
	function checkCustomer() {
		var supplier_id = $("#supplier_id").val();
		if(supplier_id==null){
			$.notify("Supplier is required", {globalPosition: 'top right',className: 'error'});
			return false;
		}
	}
	
</script>

@endsection