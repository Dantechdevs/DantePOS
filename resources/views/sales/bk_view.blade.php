@extends('layouts.layout')
@section('title', '| View Sales')
@section('content')

@section('custom_styles')

@endsection

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>View Sales</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">View Sales</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
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
    @if(Session::has('flash_message_warning'))
     <div class="alert alert-warning">

      <strong> {!! session('flash_message_warning') !!} </strong>
    </div>
    @endif
    <div class="row">
      <div class="col-12">


        <div class="card">
          <div class="card-header">
            <h3 class="card-title">View Sales</h3>
            <a href="{{ url('/add-sale') }}" class="btn btn-block btn-success btn-sm" style="width: 180px; float: right; display: inline-block;">New Order</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body">


            <table id="example1" class="table table-bordered table-striped table-sm">
              <thead>
                <tr>
                  <th>ID #</th>
                  <th>Date</th>
                  <th>Invoice#</th>
                  <th>Customer</th>
                  <th>Total Amount</th>
                  <th>Order Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $counter = 0;
                ?>
                @foreach($sales as $sale)
                <?php

                $counter = $counter+1;
                ?>
                <tr>
                  <td>{{ $counter }}</td>

                  <td>{{ date('d-m-Y',strtotime($sale['date']))}}</td>
                  <td>{{"SN-".($sale['invoice_no'])}}</td>
                  <td>{{ @$sale['customers']['name'] }}</td>
                  <td>{{ $sale['amount'] }}</td>
                  <td>

                    @if($sale['status']=='0')
                    <span class="badge badge-danger">Cancelled</span>

                    @elseif($sale['status']=='1')
                    <span class="badge badge-success">Received</span>

                    @elseif($sale['status']=='2')
                    <span class="badge badge-warning">Pending</span>

                    @endif

                  </td>



                  <td class="center">
                    <ul class="navbar-nav ml-auto">
                      <!-- Notifications Dropdown Menu -->
                      <li class="nav-item dropdown">
                        <a class="btn btn-primary btn-sm btn-o dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
                          Action <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

                          <a class="dropdown-item" href="{{url('/edit-sale',[$sale['id']]) }}">
                            <i class="fas fa-edit text-blue"></i>
                            {{ __('Edit') }}
                          </a>


                          <a class="dropdown-item" href="{{url('/sale-invoice',[$sale['id']]) }}">
                            <i class="fas fa-file-invoice text-gray"></i>
                            {{ __('Sale Invoice') }}
                          </a>
                          @if($sale['status']==0)
                          <a class="dropdown-item deleteRecord" data-toggle="modal" class="deleteRecord" href="javascript:" rel="{{ $sale['id'] }}" rel1="delete-sale" id="delete">
                            <i class="fas fa-trash text-red"></i>
                            {{ __('Delete') }}
                          </a>
                          @endif

                        </div>
                      </li>
                    </ul>
                  </td>
                </tr>
                @endforeach

              </tbody>
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

@push('custom-script')
@endpush
