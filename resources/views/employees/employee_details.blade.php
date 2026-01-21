@extends('layouts.layout')
@section('title', '| Employee Details')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Employee Details</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Employee Details</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
     @if(Session::has('flash_message_error'))
          <div class="alert alert-danger">
            <strong>{!! session('flash_message_error') !!}</strong>
          </div>
          @endif
          @if(Session::has('flash_message_success'))
          <div class="alert alert-success">
            <strong>{!! session('flash_message_success') !!}</strong>
          </div>
          @endif
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Employee Salary History</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-6">
                <strong>Employee Name:</strong> {{$employee['name']}}
              </div>
              <div class="col-md-6">
                <strong>CNIC:</strong> {{$employee['cnic']}}
              </div>
            </div>

            @if(isset($employee['joining_salary']))
            <div class="alert alert-info">
              <strong>Joining Salary:</strong> {{ number_format($employee['joining_salary'], 2) }}
            </div>
            @endif

            <table id="example1" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>ID #</th>
                  <th>Previous Salary</th>
                  <th>Increment Amount</th>
                  <th>Current Salary</th>
                  <th>Effected Date</th>
                  <th>Change Type</th>
                </tr>
              </thead>
              <tbody>
                @foreach($employeeDetails as $key => $details)
                <tr>
                  <td>{{ $key + 1 }}</td>
                  <td>{{ number_format($details['previous_salary'], 2) }}</td>
                  <td class="text-success">+{{ number_format($details['increment_salary'], 2) }}</td>
                  <td class="text-primary"><strong>{{ number_format($details['current_salary'], 2) }}</strong></td>
                  <td>{{ date('Y-m-d', strtotime($details['effected_date'])) }}</td>
                  <td>
                    @if($key == 0 && !isset($employee['joining_salary']))
                    <span class="badge bg-success">Initial Salary</span>
                    @else
                    <span class="badge bg-info">Salary Revision</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
              @if(count($employeeDetails) > 0)
              <tfoot>
                <tr class="bg-light">
                  <td colspan="2"><strong>Total Increment:</strong></td>
                  <td colspan="4">
                    {{ number_format($employeeDetails->sum('increment_salary'), 2) }}
                  </td>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
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

@endsection
