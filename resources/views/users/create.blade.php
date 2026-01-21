@extends('layouts.layout')
@section('title', '| Add/Edit User')
@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        @if (isset($editAdmin))
                            Update User
                        @else
                            Add New User
                        @endif
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manage Admin</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Flash Messages -->
            @if (Session::has('flash_message_error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ session('flash_message_error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (Session::has('flash_message_success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ session('flash_message_success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Admin Form -->
            <form id="userForm" action="{{ isset($editAdmin) ? route('user.update', $editAdmin['id']) : route('user.create') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            @if (isset($editAdmin))
                                Update User
                            @else
                                Add New User
                            @endif
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('users') }}" class="btn btn-sm btn-light">
                                <i class="fa fa-list"></i> User List
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Name -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm shadow-sm" name="name" id="name" placeholder="Enter Name" value="{{ isset($editAdmin) ? $editAdmin['name'] : old('name') }}">
                            </div>

                            <!-- Email -->
                            <div class="form-group col-md-4">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm shadow-sm" name="email" id="email" placeholder="Enter Email" value="{{ isset($editAdmin) ? $editAdmin['email'] : old('email') }}" {{ isset($editAdmin) ? 'disabled' : '' }}>
                            </div>

                            <!-- Mobile -->
                            <div class="form-group col-md-4">
                                <label for="mobile">Mobile</label>
                                <input type="text" class="form-control form-control-sm shadow-sm" name="mobile" id="mobile" placeholder="Enter Mobile" value="{{ isset($editAdmin) ? $editAdmin['mobile'] : old('mobile') }}">
                            </div>
                        </div>

                        <!-- Password -->
                        @if (!isset($editAdmin))
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-sm shadow-sm" name="password" id="password" placeholder="Enter Password">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-sm shadow-sm" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
                                </div>
                            </div>
                        @endif

                        <!-- Roles -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="group_id">Roles <span class="text-danger">*</span></label>
                                <select name="group_id" id="group_id" class="form-control select2 shadow-sm" required>
                                    <option value="" disabled selected>Select Role</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group['id'] }}" {{ isset($editAdmin) && $editAdmin['group_id'] == $group['id'] ? 'selected' : '' }}>
                                            {{ $group['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="form-group col-md-8">
                                <label for="address">Address</label>
                                <textarea class="form-control form-control-sm shadow-sm" name="address" id="address" rows="3">{{ isset($editAdmin) ? $editAdmin['address'] : old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-sm shadow-sm float-right">
                            {{ isset($editAdmin) ? 'Update Admin' : 'Create Admin' }}
                        </button>
                        <a href="{{ url('/home') }}" class="btn btn-warning btn-sm shadow-sm float-right mr-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
    $(document).ready(function () {
        // Form Validation
        $('#userForm').validate({
            rules: {
                name: {
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true
                },
                confirm_password: {
                    required: true,
                    equalTo: "#password"
                },
                group_id: {
                    required: true
                },
            },
            messages: {},
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>

@endsection
