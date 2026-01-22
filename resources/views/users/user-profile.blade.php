@extends('layouts.layout')
@section('title', '| User Profile')
@section('content')

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <!-- Left col - Profile Info -->
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile text-center">
                            <img class="profile-user-img img-fluid img-circle"
                                src="{{ asset($user['image'] ?? 'images/avatar5.png') }}"
                                alt="User profile picture">

                            <h3 class="profile-username text-center">{{ $user['name'] }}</h3>
                            <p class="text-muted text-center">{{ ucfirst($user['group']['name'] ?? 'User') }}</p>

                            <ul class="list-group list-group-unbordered mb-3 text-left">
                                <li class="list-group-item"><b>Email</b> <span class="float-right">{{ $user['email'] }}</span></li>
                                <li class="list-group-item"><b>Mobile</b> <span class="float-right">{{ $user['mobile'] ?? 'N/A' }}</span></li>
                                <li class="list-group-item"><b>Status</b> <span class="float-right">{{ $user['status'] ? 'Active' : 'Inactive' }}</span></li>
                                <li class="list-group-item"><b>Member Since</b> <span class="float-right">{{ \Carbon\Carbon::parse($user['created_at'])->format('M d, Y') }}</span></li>
                            </ul>

                            <a href="{{ route('edit.user.profile',$user['id']) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-edit mr-2"></i><b>Edit Profile</b>
                            </a>
                        </div>
                    </div>

                    <!-- About Me Box -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Other Info</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                            <p class="text-muted">{{ $user['address'] ?? 'N/A' }}</p>
                            <hr>
                            <strong><i class="far fa-calendar-alt mr-1"></i> Last Updated</strong>
                            <p class="text-muted">{{ \Carbon\Carbon::parse($user['updated_at'])->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right col - Tabs -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#personal" data-toggle="tab">Personal</a></li>
                                <li class="nav-item"><a class="nav-link" href="#hrm" data-toggle="tab">HRM</a></li>
                                <li class="nav-item"><a class="nav-link" href="#bank" data-toggle="tab">Bank</a></li>
                                <li class="nav-item"><a class="nav-link" href="#payroll" data-toggle="tab">Payroll</a></li>
                                <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity Log</a></li>
                                <li class="nav-item"><a class="nav-link" href="#login" data-toggle="tab">Login History</a></li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">

                                <!-- PERSONAL TAB -->
                                <div class="active tab-pane" id="personal">
                                    <table class="table table-bordered table-sm">
                                        <tr><th>Name</th><td>{{ $user['name'] }}</td></tr>
                                        <tr><th>Email</th><td>{{ $user['email'] }}</td></tr>
                                        <tr><th>Mobile</th><td>{{ $user['mobile'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Gender</th><td>{{ $user['gender'] ?? 'N/A' }}</td></tr>
                                        <tr><th>DOB</th><td>{{ $user['dob'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Marital Status</th><td>{{ $user['marital_status'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Address</th><td>{{ $user['address'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Blood Group</th><td>{{ $user['blood_group'] ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>

                                <!-- HRM TAB -->
                                <div class="tab-pane" id="hrm">
                                    <table class="table table-bordered table-sm">
                                        <tr><th>Department</th><td>{{ $user['department'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Designation</th><td>{{ $user['designation'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Staff PIN</th><td>{{ $user['staff_pin'] ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>

                                <!-- BANK TAB -->
                                <div class="tab-pane" id="bank">
                                    <table class="table table-bordered table-sm">
                                        <tr><th>Bank Name</th><td>{{ $user['bank_name'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Account Name</th><td>{{ $user['account_name'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Account Number</th><td>{{ $user['account_number'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Tax Payer ID</th><td>{{ $user['tax_id'] ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>

                                <!-- PAYROLL TAB -->
                                <div class="tab-pane" id="payroll">
                                    <table class="table table-bordered table-sm">
                                        <tr><th>Basic Salary</th><td>{{ $user['salary'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Commission (%)</th><td>{{ $user['commission'] ?? 'N/A' }}</td></tr>
                                        <tr><th>Max Discount (%)</th><td>{{ $user['max_discount'] ?? 'N/A' }}</td></tr>
                                    </table>
                                </div>

                                <!-- ACTIVITY LOG -->
                                <div class="tab-pane" id="activity">
                                    <div id="activityResults">
                                        @include('users.partials.activity-list', ['activityLogs' => $activityLogs])
                                    </div>
                                </div>

                                <!-- LOGIN HISTORY -->
                                <div class="tab-pane" id="login">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>IP Address</th>
                                                <th>Browser</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($loginActivities as $login)
                                                <tr>
                                                    <td>{{ $login->created_at }}</td>
                                                    <td>{{ $login->ip_address }}</td>
                                                    <td>{{ Str::limit($login->user_agent, 80) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3">No login records</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<style>
    .profile-user-img { width: 100px; height: 100px; object-fit: cover; }
    .table th { width: 35%; }
</style>

@endsection
