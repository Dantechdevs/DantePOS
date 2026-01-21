@extends('layouts.layout')
@section('title', '| Create Role')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Create Role</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Create Role</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <form id="groupForm" action="{{ route('create.role') }}" method="post">
                    @csrf
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Add Role</h3>
                            <a href="{{ route('roles') }}" class="btn btn-sm btn-success ml-auto">
                                <i class="fa fa-list"></i> Roles List
                            </a>
                        </div>

                        <div class="card-body">
                            <!-- Flash Messages -->
                            @include('flash_messages')

                            <!-- Role Name -->
                            <div class="form-group">
                                <label for="name">Group Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-sm" name="name" id="name"
                                    placeholder="Enter Group Name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Hidden Fields for Group Module -->
                            @foreach ($groupModule as $key => $data)
                                <input type="hidden" name="txtModID[{{ $key }}]" value="{{ $data['id'] }}">
                                <input type="hidden" name="txtModname[{{ $key }}]"
                                    value="{{ $data['module_name'] }}">
                                <input type="hidden" name="txtModpage[{{ $key }}]"
                                    value="{{ $data['module_page'] }}">
                            @endforeach

                            <!-- Module Access -->
                            <div class="form-group">
                                <label for="e1">Roles</label>
                                <select class="form-control select2 shadow-sm" name="txtaccess[]" id="e1"
                                    multiple="multiple">
                                    @foreach ($groupModule as $key => $data)
                                        <option value="{{ $key }}">{{ $data->module_name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2">
                                    <input type="checkbox" id="checkbox"> Select All
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('roles') }}" class="btn btn-warning">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success ml-auto">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>


@endsection

@push('custom-script')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $("#e1").select2();

            // Select All / Deselect All
            $("#checkbox").click(function() {
                if ($("#checkbox").is(':checked')) {
                    $("#e1 > option").prop("selected", "selected");
                } else {
                    $("#e1 > option").prop("selected", false);
                }
                $("#e1").trigger("change");
            });

            // Form Validation
            $('#groupForm').validate({
                rules: {
                    name: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please enter a Group Name"
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endpush
