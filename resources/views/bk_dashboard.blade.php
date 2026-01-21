@extends('layouts.layout')
@section('title', '| Dashboard')
@section('content')

<?php

use App\Models\GroupPermission;
use App\Models\EmployeeReturnAdvance;
use App\Models\AdvanceHistory;

/* Calculate Daily Cash Start */

$dailyCustomerPayment = App\Models\AdvanceCustomerPayment::where('date', date('Y-m-d'))->sum('amount');

$dailyExpensesPayment = App\Models\Expense::where('date', date('Y-m-d'))->sum('amount');
// echo "<pre>"; print_r($dailyExpensesPayment); exit();
/* Calculate Daily Cash Start */


$sale_total = App\Models\Sale::where('status', 1)->sum('amount');
$customerAmount = App\Models\AdvanceCustomerPayment::sum('amount'); //CR
$customerCreditAmount = App\Models\CustomerOpeningBalance::where('type', 'credit')->sum('amount'); //CR
$customerDebitAmount = App\Models\CustomerOpeningBalance::where('type', 'debit')->sum('amount'); //DB
// dd($customerDebitAmount);
$total_expenses = App\Models\Expense::sum('amount'); //DB
$supplierCreditAmount = App\Models\SupplierOpeningBalance::where('type', 'credit')->sum('amount'); //CR

$supplierDebitAmount = App\Models\SupplierOpeningBalance::where('type', 'debit')->sum('amount'); // DB

$supplierPaymets = App\Models\SupplierPayment::sum('amount'); //DB
$purchaseAmount = App\Models\Purchase::where('status', 'received')->sum('amount');

$countSales = App\Models\Sale::count('id');
$countPurchase = App\Models\Purchase::count('id');
$countSupplier = App\Models\Supplier::count('id');
$countCustomers = App\Models\Customer::count('id');
$products = App\Models\Product::get()->toArray();
$totalAmount = 0;
$returnTotalAmount = 0;
foreach ($products as $mainProduct) {
  $quantity = $mainProduct['quantity'];
  if ($mainProduct['qtyPerUnit'] > 0) {
    $cost = $mainProduct['item_cost'];
  } else {
    $cost = $mainProduct['cost'];
  }

  $stockValue = (float)$quantity * (float)$cost;
  $totalAmount = $stockValue + $totalAmount;
}
$returnTotalAmount = $totalAmount;
// dd($customerDebitAmount);
$customersCash = ($sale_total + $customerDebitAmount) - ($customerCreditAmount  + $customerAmount);
$suppliersCash = ($supplierDebitAmount   + $supplierPaymets) - ($purchaseAmount + $supplierCreditAmount);
$customersCash = intval($customersCash);
$suppliersCash = intval($suppliersCash);
?>
<!-- Content Wrapper. Contains page content -->

<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
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
      <div class="row mb-2">
        <div class="col-sm-4">
          <h1 class="m-0 text-dark">Dashboard</h1><small>Overall Information on Single Screen</small>
        </div><!-- /.col -->

        <div class="col-sm-4">

          <!-- <div class="color-palette-set">
          <div class="bg-success color-palette text-center"><span>Daily Cash</span></div>
          <div class="bg-success disabled color-palette text-center"><span><b>{{number_format($dailyCustomerPayment,2)}}</b>/PKR</span></div>
        </div> -->
        </div>
        <div class="col-sm-4">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->

    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  @if($permissionCheck->access == 1)
  <section class="content">
    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-dollar-sign"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Customers Cash</span>
              <span class="info-box-number">
                {{(@$customersCash<0)?(-1)*$customersCash. ' CR':$customersCash. ' DB'}}
                <small>/<sub>PKR</sub></small>
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <?php
        $total_cash = ($returnTotalAmount + $suppliersCash + $customersCash) - ($total_expenses);
        ?>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-dollar-sign"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Suppliers Cash</span>
              <span class="info-box-number">
                {{(@$suppliersCash<0)?(-1)*$suppliersCash. ' CR':$suppliersCash. ' DB'}}
                <small>/<sub>PKR</sub></small>
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Total Sales Amount</span>
              <span class="info-box-number">
                {{number_format($sale_total)}}
                <small>/<sub>PKR</sub></small>
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-dollar-sign"></i></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Stock Value</span>
              <span class="info-box-number">
                {{number_format($returnTotalAmount)}}
                <small>/<sub>PKR</sub></small>
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-dollar-sign"></i></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Total Cash</span>
              <span class="info-box-number">
                {{ @$total_cash < 0 ? abs($total_cash) . ' CR' : $total_cash }}
                <small> / <sub>PKR</sub></small>
              </span>
            </div>

            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-minus-square"></i></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Total Expenses</span>
              <span class="info-box-number">
                {{number_format($total_expenses)}}
                <small>/<sub>PKR</sub></small>
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>

      </div>
      <div class="row">
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$countPurchase}}</h3>

              <p>Purchase Invoice</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{$countSales}}<!-- <sup style="font-size: 20px">%</sup> --></h3>

              <p>Sales Invoice</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{$countSupplier}}</h3>

              <p>Suppliers</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-danger">
            <div class="inner">
              <h3>{{$countCustomers}}</h3>

              <p>Customers</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <div class="row">
        <!-- Left col -->
        <div class="col-sm-12 col-md-6">
          <table id="example1" class="table table-sm table-bordered table-hover table-responsive" style="width: 100%;">
            <thead>
              <th>SL# </th>
              <th>Employee </th>
              <th>Advance Amount </th>
              <th>Return Amount </th>
              <th>Remaining Amount </th>
            </thead>
            <tbody>
              <?php

              /* Calculate Monthly Advance */
              function testinggetMonthlySalary($employee_id)
              {
                // dd($employee_id);
                $empMonthly = AdvanceHistory::where('employee_id', $employee_id)->get()->toArray();
                // echo"<pre>"; print_r($empMonthly); exit();
                $empAdvance = [];
                foreach ($empMonthly as $key => $value) {
                  $empAdvance[] = $value['current_paidAmount'];
                }
                return $empAdvance;
              }
              /* Calculate Monthly Advance */

              /* Calculate Monthly Return Advacne */
              function returnMonthlySalary($employee_id)
              {
                $empReturnMonthly = EmployeeReturnAdvance::where('employee_id', $employee_id)->get()->toArray();
                // echo"<pre>"; print_r($empReturnMonthly); exit();
                $empReturnAdvance = [];
                foreach ($empReturnMonthly as $key => $value) {
                  $empReturnAdvance[] = $value['return_amount'];
                }
                return $empReturnAdvance;
              }

              foreach ($employees as $key => $employee) {
                $employee_id = $employee['id'];
                $advanceSalary = testinggetMonthlySalary($employee_id);
                $advanceSalary = array_sum($advanceSalary);

                $returnMonthlyAdvance = returnMonthlySalary($employee_id);
                $returnMonthlyAdvance = array_sum($returnMonthlyAdvance);

                $remainAdvance = $advanceSalary - $returnMonthlyAdvance;
              ?>
                <tr>
                  <td>{{$key+1}}</td>
                  <td>{{$employee['name']}}</td>
                  <td class="text-right">{{number_format($advanceSalary)}}</td>
                  <td class="text-right">{{number_format($returnMonthlyAdvance)}}</td>
                  <td class="text-right">{{number_format($remainAdvance)}}</td>
                </tr>
              <?php
              }
              ?>
            </tbody>
          </table>

        </div>
        <div class="col-sm-12 col-md-6" style="width: 400px;height: 400px;margin: auto;">
          <div>
            <canvas id="salePurChart"></canvas>
          </div>
        </div>
      </div>


      <!-- Main row -->

      <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
  </section>
  @endif

  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

<script src="{{asset('plugins/chart.js/Chart.bundle.min.js')}}"></script>
<script>
  var ctx = document.getElementById('salePurChart').getContext('2d');
  var salePurChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: '# of Sales',
        data: <?php echo json_encode($datas) ?>,
        backgroundColor: [
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 99, 132, 0.2)'
        ],
        borderColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 99, 132, 1)'
        ],
        borderWidth: 1
      }, {
        label: '# of Purchase',
        data: <?php echo json_encode($purchaseData) ?>,
        backgroundColor: [
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(54, 162, 235, 0.2)'
        ],
        borderColor: [
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 162, 235, 1)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: [{
          beginAtZero: true
        }, {
          beginAtZero: true
        }]
      }
    }



  });
</script>


@endsection
