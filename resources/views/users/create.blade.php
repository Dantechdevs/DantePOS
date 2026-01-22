@extends('layouts.layout')
@section('title', '| Add/Edit User')
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ isset($editAdmin) ? 'Update User' : 'Add New User' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manage Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form id="userForm"
                  action="{{ isset($editAdmin) ? route('user.update', $editAdmin['id']) : route('user.create') }}"
                  method="POST">
                @csrf

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">{{ isset($editAdmin) ? 'Update User' : 'Add New User' }}</h3>
                    </div>

                    <div class="card-body">

                        {{-- BASIC INFO --}}
                        <h5>Basic Info</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Name *</label>
                                <input type="text" name="name" class="form-control form-control-sm"
                                       value="{{ $editAdmin['name'] ?? old('name') }}">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control form-control-sm"
                                       value="{{ $editAdmin['email'] ?? old('email') }}"
                                       {{ isset($editAdmin) ? 'disabled' : '' }}>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Mobile</label>
                                <input type="text" name="mobile" class="form-control form-control-sm"
                                       value="{{ $editAdmin['mobile'] ?? old('mobile') }}">
                            </div>
                        </div>

                        @if (!isset($editAdmin))
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Password *</label>
                                    <input type="password" name="password" id="password"
                                           class="form-control form-control-sm">
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Confirm Password *</label>
                                    <input type="password" name="confirm_password"
                                           class="form-control form-control-sm">
                                </div>
                            </div>
                        @endif

                        {{-- ROLE --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Role *</label>
                                <select name="group_id" id="group_id" class="form-control form-control-sm" required>
                                    <option value="">Select Role</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group['id'] }}"
                                                {{ isset($editAdmin) && $editAdmin['group_id']==$group['id'] ? 'selected' : '' }}>
                                            {{ $group['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Address</label>
                                <textarea name="address" class="form-control form-control-sm">{{ $editAdmin['address'] ?? old('address') }}</textarea>
                            </div>
                        </div>

                        {{-- ACCESS --}}
                        <hr>
                        <h5>User Access</h5>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>
                                    <input type="checkbox" name="status" value="1" checked> Active
                                </label>
                            </div>

                            <div class="form-group col-md-3">
                                <label>
                                    <input type="checkbox" name="allow_login" value="1" checked> Allow Login
                                </label>
                            </div>

                            <div class="form-group col-md-6" id="staff_pin_box" style="display:none;">
                                <label>Staff PIN</label>
                                <input type="text" name="staff_pin" class="form-control form-control-sm">
                            </div>
                        </div>

                        {{-- PERSONAL --}}
                        <hr>
                        <h5>Personal Info</h5>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>DOB</label>
                                <input type="date" name="dob" class="form-control form-control-sm">
                            </div>

                            <div class="form-group col-md-3">
                                <label>Gender</label>
                                <select name="gender" class="form-control form-control-sm">
                                    <option value="">Select</option>
                                    <option>Male</option>
                                    <option>Female</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label>Marital Status</label>
                                <select name="marital_status" class="form-control form-control-sm">
                                    <option value="">Select</option>
                                    <option>Single</option>
                                    <option>Married</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label>Blood Group</label>
                                <input type="text" name="blood_group" class="form-control form-control-sm">
                            </div>
                        </div>

                        {{-- HRM --}}
                        <hr>
                        <h5>HRM</h5>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Department</label>
                                <input type="text" name="department" class="form-control form-control-sm">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Designation</label>
                                <input type="text" name="designation" class="form-control form-control-sm">
                            </div>
                        </div>

                        {{-- BANK --}}
                        <hr>
                        <h5>Bank Details</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Account Name</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Account Number</label>
                                <input type="text" name="account_number" class="form-control form-control-sm">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Tax Payer ID</label>
                                <input type="text" name="tax_id" class="form-control form-control-sm">
                            </div>
                        </div>

                        {{-- PAYROLL --}}
                        <hr>
                        <h5>Payroll & Sales</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Basic Salary</label>
                                <input type="number" name="salary" class="form-control form-control-sm">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Commission (%)</label>
                                <input type="number" name="commission" class="form-control form-control-sm">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Max Discount (%)</label>
                                <input type="number" name="max_discount" class="form-control form-control-sm">
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button class="btn btn-success btn-sm float-right">Save User</button>
                        <a href="{{ url('/home') }}" class="btn btn-secondary btn-sm float-right mr-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
    $(function () {

        $('#group_id').on('change', function () {
            let role = $("#group_id option:selected").text().toLowerCase();
            if (role.includes('cashier') || role.includes('staff')) {
                $('#staff_pin_box').slideDown();
            } else {
                $('#staff_pin_box').slideUp();
            }
        });

        $('#userForm').validate({
            rules: {
                name: { required: true },
                email: { required: true, email: true },
                password: { required: true },
                confirm_password: { equalTo: "#password" },
                group_id: { required: true }
            }
        });
    });
</script>
@endsection
