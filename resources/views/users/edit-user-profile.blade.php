@extends('layouts.layout')
@section('title', '| Update User Profile')
@section('content')

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Update User Profile</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Update Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <form name="userForm" id="userForm" action="{{ url('/edit-user-profile/'.$editUser['id']) }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="card card-default">
                    <div class="card-header">
                        <a href="{{ url('/user-profile') }}" class="btn btn-success btn-sm" style="width: 150px;"><i class="fa fa-list"></i>&nbsp;&nbsp;Profile</a>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(Session::has('flash_message_error'))
                            <div class="alert alert-danger"><strong>{!! session('flash_message_error') !!}</strong></div>
                        @endif
                        @if(Session::has('flash_message_success'))
                            <div class="alert alert-success"><strong>{!! session('flash_message_success') !!}</strong></div>
                        @endif

                        <div class="row">

                            <!-- Name -->
                            <div class="form-group col-md-4">
                                <label for="name">User Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ $editUser['name'] }}">
                                <font style="color: red;">{{ $errors->first('name') }}</font>
                            </div>

                            <!-- Email -->
                            <div class="form-group col-md-4">
                                <label for="email">User Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="{{ $editUser['email'] }}" readonly>
                                <font style="color: red;">{{ $errors->first('email') }}</font>
                            </div>

                            <!-- Mobile -->
                            <div class="form-group col-md-4">
                                <label for="mobile">Mobile</label>
                                <input type="text" class="form-control" name="mobile" id="mobile" value="{{ $editUser['mobile'] }}">
                                <font style="color: red;">{{ $errors->first('mobile') }}</font>
                            </div>

                            <!-- Gender -->
                            <div class="form-group col-md-4">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" class="form-control select2">
                                    <option disabled>Select Gender</option>
                                    <option value="Male" {{ $editUser['gender']=='Male'?'selected':'' }}>Male</option>
                                    <option value="Female" {{ $editUser['gender']=='Female'?'selected':'' }}>Female</option>
                                </select>
                                <font style="color: red;">{{ $errors->first('gender') }}</font>
                            </div>

                            <!-- DOB -->
                            <div class="form-group col-md-4">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" name="dob" id="dob" value="{{ $editUser['dob'] }}">
                            </div>

                            <!-- Marital Status -->
                            <div class="form-group col-md-4">
                                <label for="marital_status">Marital Status</label>
                                <select name="marital_status" id="marital_status" class="form-control">
                                    <option disabled>Select Status</option>
                                    <option value="Single" {{ $editUser['marital_status']=='Single'?'selected':'' }}>Single</option>
                                    <option value="Married" {{ $editUser['marital_status']=='Married'?'selected':'' }}>Married</option>
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="form-group col-md-4">
                                <label for="address">Address</label>
                                <textarea class="form-control" name="address" id="address" rows="2">{{ $editUser['address'] }}</textarea>
                            </div>

                            <!-- Department -->
                            <div class="form-group col-md-4">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" name="department" id="department" value="{{ $editUser['department'] }}">
                            </div>

                            <!-- Designation -->
                            <div class="form-group col-md-4">
                                <label for="designation">Designation</label>
                                <input type="text" class="form-control" name="designation" id="designation" value="{{ $editUser['designation'] }}">
                            </div>

                            <!-- Blood Group -->
                            <div class="form-group col-md-4">
                                <label for="blood_group">Blood Group</label>
                                <input type="text" class="form-control" name="blood_group" id="blood_group" value="{{ $editUser['blood_group'] }}">
                            </div>

                            <!-- Bank Name -->
                            <div class="form-group col-md-4">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ $editUser['bank_name'] }}">
                            </div>

                            <!-- Account Number -->
                            <div class="form-group col-md-4">
                                <label for="account_number">Account Number</label>
                                <input type="text" class="form-control" name="account_number" id="account_number" value="{{ $editUser['account_number'] }}">
                            </div>

                            <!-- Tax Payer ID -->
                            <div class="form-group col-md-4">
                                <label for="tax_id">Tax Payer ID</label>
                                <input type="text" class="form-control" name="tax_id" id="tax_id" value="{{ $editUser['tax_id'] }}">
                            </div>

                            <!-- Basic Salary -->
                            <div class="form-group col-md-4">
                                <label for="salary">Basic Salary</label>
                                <input type="number" class="form-control" name="salary" id="salary" value="{{ $editUser['salary'] }}">
                            </div>

                            <!-- Commission -->
                            <div class="form-group col-md-4">
                                <label for="commission">Commission (%)</label>
                                <input type="number" class="form-control" name="commission" id="commission" value="{{ $editUser['commission'] }}">
                            </div>

                            <!-- Max Discount -->
                            <div class="form-group col-md-4">
                                <label for="max_discount">Max Discount (%)</label>
                                <input type="number" class="form-control" name="max_discount" id="max_discount" value="{{ $editUser['max_discount'] }}">
                            </div>

                        </div>

                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ url('/view-users') }}" class="btn btn-warning mr-2">Cancel</a>
                        <button type="submit" class="btn btn-success">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $('#userForm').validate({
        rules: {
            name: { required: true },
            email: { required: true, email: true },
        },
        messages: {
            name: { required: "Please enter Name" },
            email: { required: "Please enter a valid email", email: "Please enter a valid email" }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element) { $(element).addClass('is-invalid'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid'); }
    });
});
</script>

@endsection
