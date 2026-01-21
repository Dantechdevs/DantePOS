@extends('layouts.layout')
@section('title', '| Add Product')
@section('content')


<div class="content-wrapper">

	<!-- Content Header (Page header) -->
	<section class="content-header">
    @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
				</div>

				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ url('/home')}}">Dashboard</a></li>
						<li class="breadcrumb-item active">Add Product</li>
					</ol>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>

	<!-- Main content -->
	<section class="content">
		<div class="container-fluid col-md-10">
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
			<form name="gateForm" id="gateForm" action="{{(@$editProduct)?url('update-product/'.$editProduct['id']):url('/store-product') }}"  method="post">
				@csrf


				<!-- Start another Card Here--------->
				<div class="card card-dark">
					<div class="card-header">
						<h3 class="card-title">Add Product</h3>
						<a href="{{ url('/products') }}" class="btn btn-block btn-warning btn-sm" style="width: 120px; float: right; display: inline-block; color: black;"><i  class="fa fa-list"></i>&nbsp;&nbsp; Products List</a>
					</div>
					<!-- /.card-header -->
					<!-- form start -->
					<form class="form-horizontal">
						<div class="card-body box-profile">
							<div class="form-group row">
								<label for="inputProductName" class="col-sm-2 col-form-label">Product Name <font style="color: red;">*</font></label>
								<div class="col-sm-10">
									<input type="text" class="form-control" name="name" placeholder="Enter Product Name" id="name" value="{{(@$editProduct)?$editProduct['name']:old('name') }}" required="">
								</div>
							</div>
							<div class="form-group row">
								<label for="unit_id" class="col-sm-2 col-form-label">Unit <font style="color: red;">*</font></label>
								<div class="col-sm-10">
									<select name="unit_id" id="unit_id" class="form-control select2" required="" style="width: 100%;">
										<option selected="selected" readonly="" value="0">Select Unit</option>
										@foreach($units as $unit)
										<option value="{{$unit['id']}}" {{(@$editProduct['unit_id']==$unit['id'])?'selected':''}}>{{$unit['name']}}</option>
										@endforeach

									</select>

								</div>
							</div>





                            <div class="form-group row">
								<label for="qtyPerUnit" class="col-sm-2 col-form-label">Qty Per Unit <font style="color: red;">*</font></label>
								<div class="col-sm-10">
									<input type="text" class="form-control forMainProduct" name="qtyPerUnit" placeholder="Qty Per Unit" id="qtyPerUnit" value="{{(@$editProduct)?$editProduct['qtyPerUnit']:old('qtyPerUnit') }}">
                                    <span id="qtyPerUnitValidation" style="color: red;"></span>
                                </div>
							</div>

							<div class="form-group row">
								<label for="inputProductCost" class="col-sm-2 col-form-label">Purchase Price <font style="color: red;">*</font></label>
								<div class="col-sm-10 col-md-5">
									<input type="text" class="form-control forMainProduct" name="cost" placeholder="Unit Purchase Price" id="cost" value="{{(@$editProduct)?$editProduct['cost']:old('cost') }}">
								</div>
                                <div class="col-sm-10 col-md-5">
									<input type="text" class="form-control forMainProduct" name="item_cost" placeholder="Item Purchase Price" id="item_cost" value="{{(@$editProduct)?$editProduct['item_cost']:0 }}">
								</div>
							</div>
							<div class="form-group row">
								<label for="inputSellingPrice" class="col-sm-2 col-form-label">Selling Price <font style="color: red;">*</font></label>
								<div class="col-sm-10 col-md-5">
									<input type="text" class="form-control forMainProduct" name="selling_price" placeholder="Unit Selling Price" id="selling_price" value="{{(@$editProduct)?$editProduct['selling_price']:old('selling_price') }}">
								</div>
                                <div class="col-sm-10 col-md-5">
									<input type="text" class="form-control forMainProduct" name="item_selling_price" placeholder="Item Selling Price" id="item_selling_price" value="{{(@$editProduct)?$editProduct['item_selling_price']:old('item_selling_price') }}">
								</div>
							</div>
							<div class="form-group row">
								<label for="quantity" class="col-sm-2 col-form-label">Quantity</label>
								<div class="col-sm-10">
									<input type="text" class="form-control forMainProduct" name="quantity" placeholder="Quantity" id="quantity" value="{{(@$editProduct)?$editProduct['quantity']:old('quantity') }}">
								</div>
							</div>
							<div class="form-group row">
								<label for="product_code" class="col-sm-2 col-form-label">Product Code <font style="color: red;">*</font></label>
								<div class="col-sm-10">
									<input type="text" class="form-control" name="product_code" placeholder="Product Code" id="product_code" required="" value="{{(@$editProduct)?$editProduct['product_code']:old('product_code') }}">
								</div>
							</div>
						</div>
						<!-- /.card-body -->
						<div class="card-footer">
							<button type="submit" class="btn btn-info saveProductBtn">{{ $title }}</button>

							<a href="{{'/dashboard'}}"  class="btn btn-warning float-right">Cancel</a>
						</div>
						<!-- /.card-footer -->
					</form>
				</div>
			</form>
		</div><!-- /.container-fluid -->
	</section>
	<!-- /.content -->
</div>
@endsection

@push('custom-script')


<script>
    $(function() {
        var cost = parseFloat($('#cost').val()) || 0;
            var qtyPerUnit = parseFloat($('#qtyPerUnit').val()) || 1;
        displayPurchasePrice(cost,qtyPerUnit);
        $(document).on('keyup', '#cost, #qtyPerUnit', function() {
            var cost = parseFloat($('#cost').val()) || 0;
            var qtyPerUnit = parseFloat($('#qtyPerUnit').val()) || 1; // default to 1 if value is invalid or empty

            displayPurchasePrice(cost,qtyPerUnit);
        });
        function displayPurchasePrice(cost,qtyPerUnit){
            if (qtyPerUnit === 0) {
                // Prevent division by zero
                return;
            }

            var total = cost / qtyPerUnit;
            $('#item_cost').val(total.toFixed(2));
        }

        $(document).on('keyup', '#selling_price, #qtyPerUnit', function() {
            var selling_price = parseFloat($('#selling_price').val()) || 0;
            var qtyPerUnit = parseFloat($('#qtyPerUnit').val()) || 1; // default to 1 if value is invalid or empty

            if (qtyPerUnit === 0) {
                // Prevent division by zero
                return;
            }

            var total = selling_price / qtyPerUnit;
            $('#item_selling_price').val(total.toFixed(2));
        });

        $('#qtyPerUnit').on('input', function() {
        var value = $(this).val(); // Get the current value of the input

        // Check if the value is a valid number and greater than 0
        if ($.isNumeric(value) && parseFloat(value) > 0) {
            $("#qtyPerUnitValidation").text('');
            $(".saveProductBtn").prop('disabled',false);
        } else {
            $("#qtyPerUnitValidation").text('Qty Per Unit should be greater than 0');
            $(".saveProductBtn").prop('disabled',true);
        }
        });
    });
</script>

<!-- <script type="text/javascript">
	$(document).ready(function () {

		$('#gateForm').validate({
			rules: {
				name: {
					required: true
				},
				unit_id: {
					required: true
				}
			},
			messages: {
				name: {
					required: "Please enter Name",
				},
				unit_id: {
					required: "Please select unit",
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

@endpush
