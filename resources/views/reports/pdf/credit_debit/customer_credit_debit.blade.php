<?php
use App\Models\Customer;
use App\Models\Sale;
use App\Models\AdvanceCustomerPayment;
use App\Models\CustomerOpeningBalance;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
    <style>
      body {
        font-family: Geneva, Verdana, sans-serif;
      }

      h6 {
        font-size: 16px;
        margin: 0;
        margin-bottom: 10px;
      }

      p {
        font-size: 14px;
      }

      .p-text {
        margin: 0;
        margin-bottom: 5px;
        font-size: 12px;
      }

      .table-suply {
        border: 1px solid black;
        border-top: none;
        margin-top: 20px;
      }

      .table-suply tr th,
      .table-suply tr td {
        border-top: 1px solid #000000;
        text-align: center;
        border-right: 1px solid #000000;
        padding: 3px;
      }

      .table-suply tr th:last-child,
      .table-suply tr td:last-child {
        border-right: none;
      }

      .p-text-1 {
        text-decoration: underline;
        padding-top: 10px;
      }

      .p-text-2 {
        padding-top: 10px;
      }
    </style>
  </head>
  <body>
    <center style=" width: 700px; margin: 0px auto;">
    <table cellspacing="0" cellpadding="0" border="0" style="font-family: Geneva, Verdana,  sans-serif; font-size:12px; color:#000000; line-height: 1rem;">
      <tr>
        @if($sale_type=='areaWise')
        <td style=" width: 100%; padding-top: 30px; text-align: center;"><strong>Customer Balance - Report</strong></td>
        @else
        <td style=" width: 100%; padding-top: 30px; text-align: center;"><strong>Credit/Debit - Report</strong></td>
        @endif
      </tr><br>
      <tr>
        <td>
          <table cellspacing="0" cellpadding="0" border="0">
            <tr>
              <!-- company address -->
              <td style="font-size: 10px; width: 50%;">
                <span style="font-size: 12px; font-weight: bold;"></span>Muhammad Haseeb</span>

                Dilazak Road, bilmuqabil Darya High School Peshawar. Contact:  {{Auth::user()->mobile}}

                <!-- company address -->
                <hr style=" width: 100%; border-color: #000000;"></hr>
                @if($sale_type=='customerWise')
                <p class="p-text" ><b>Report Date: </b>{{date('M d, Y',strtotime($startDate))}} to {{date('M d, Y',strtotime($endDate))}}</p>
                @endif
              </td>

              <td style="font-size: 10px; width: 10%; padding-left: 100px"></td>
              <td style="font-size: 10px; width: 40%;">
                <!-- Profile Details -->

                @if($sale_type=='areaWise')
                <p class="p-text" ><b>Areawise Report: </b>{{(@$sale_type=='areaWise')?$area['name']:'' }}</p>

                @else
                <p class="p-text" ><b>Customer Name:</b> {{$customer['name']}}</p>
                <hr style=" width: 100%; border-color: #000000;"></hr>
                @endif
                @if($sale_type=='customerWise')
                <p class="p-text" ><b>Total Balance:</b> {{(@$customerBalance<0)?(-1)*$customerBalance. ' CR':$customerBalance. ' DB'}}
                @endif

                </p>
                <hr style=" width: 100%; border-color: #000000;"></hr>
                <!-- <hr style=" width: 100%; border-color: #000000;"></hr> -->

                <!-- <p class="p-text"> <span>Bharai Employees Details in single file.</span></p> -->
                <!-- Profile Details -->
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <!-- 2nd Row -->
      <!-- Heading -->

      <?php
      $tbalance=0;
      $total_balance=0;
      ?>

      @if($sale_type=='customerWise')
      <tr>
        <td style="width: 100%;">
          <table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <th colspan="12" align="center"><b>Credit/Debit Report, {{(@$paymentDiscount>0)?$paymentDiscount.' Discount':''}}</b></th>
            </tr>
            <!-- Table Heading -->
            <tr>
              <th colspan="1">VCH#</th>
              <th colspan="2">Date</th>
              <th colspan="5">Description</th>
              <th colspan="1">Debit</th>
              <th colspan="1">Credit</th>
              <th colspan="2">Balance</th>
            </tr>
            <!-- Table Heading -->
            <!-- Table content row 1 -->
            @foreach($debitCredit as $value)
            <?php $chkbala = (int)$value['debit'] - (int)$value['credit'];  $tbalance += $chkbala;
            $total_balance = $tbalance;
            ?>
            <tr>
              <td colspan="1">{{$value['voucher_no']}}</td>
              <td colspan="2">{{date('d-m-Y',strtotime($value['date']))}}</td>
              <td colspan="5">{{$value['description']}}</td>
              <td colspan="1">{{$value['debit']}}</td>
              <td colspan="1">{{$value['credit']}}</td>
              <?php
              if($total_balance<0){
                ?>
                <td colspan="2" align="right">{{-1*($total_balance)}} CR</td>
                <?php
              }else{
                ?>
                <td colspan="2" align="right">{{$total_balance}} DB</td>
                <?php
              }

              ?>
            </tr>
            @endforeach
            <!-- Table content row 1 -->
            <!-- Table total  row -->
            <tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
              <td colspan="10"><span class="text-bold">Closing Balance</span></td>
              <?php
              $closing_balance = $total_balance - $paymentDiscount;
              if($closing_balance<0){
                ?>
                <td colspan="2">{{-1*($closing_balance)}} CR</td>
                <?php
              }else{
                ?>
                <td colspan="2">{{$closing_balance}} DB</td>
                <?php
              }

              ?>
            </tr>


          </table>
        </td>
      </tr>
      @else
      <tr>
        <td style="width: 100%;">
          <table class="table-suply" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <th colspan="12" align="center"><b>Customer Balance Report</b></th>
            </tr>

            <!-- Table Heading -->
            <tr>
              <th colspan="1">#</th>
              <th colspan="4">Customer</th>
              <th colspan="3">Balance</th>
              <th colspan="4">Receiving</th>
            </tr>
            <!-- Table Heading -->
            <!-- Table content row 1 -->
            <?php

            /* Check Customer Total Amount Function Start*/
            function totalAmount($customer_id) {
              $returnTotalAmount = 0;

              $totalAmount = Sale::where('customer_id',$customer_id)->where('status',1)->get();

              $totalAmountOutPut = 0;
              if ($totalAmount === NULL) {
                $returnTotalAmount = 0;
              } else {
                $total_amount = $totalAmount;
                foreach ($total_amount as $value) {
                  $totalAmountOutPut = $value->amount + $totalAmountOutPut;
                }

                $returnTotalAmount = $totalAmountOutPut;
              }

              return $returnTotalAmount;
            }
            /* Check Customer Total Amount Function Ends*/

            /* Check Customer Pre Receivable Amount Function Start*/
            function customerDebitAmount($customer_id) {
              $returnReceivable = 0;

              $totalReceivable = CustomerOpeningBalance::where('customer_id',$customer_id)->where('type','debit')->get();

              $receivableOutPut = 0;
              if ($totalReceivable === NULL) {
                $returnReceivable = 0;
              } else {
                $total_receivable = $totalReceivable;
                foreach ($total_receivable as $value) {
                  $receivableOutPut = $value->amount + $receivableOutPut;
                }

                $returnReceivable = $receivableOutPut;
              }

              return $returnReceivable;
            }
            /* Check Customer Pre Receivable Amount Function Ends*/

            /* Check Customer Advance Amount Function Start*/
            function advanceAmount($customer_id) {
              $returnAdvance = 0;

              $totalAdvance = AdvanceCustomerPayment::where('customer_id',$customer_id)->get();

              $advanceOutPut = 0;
              if ($totalAdvance === NULL) {
                $returnAdvance = 0;
              } else {
                $total_advance = $totalAdvance;
                foreach ($total_advance as $value) {
                  $advanceOutPut = $value->amount + $advanceOutPut;
                }

                $returnAdvance = $advanceOutPut;
              }
              return $returnAdvance;
            }
            /* Check Customer Advance Amount Function Ends*/

            /* Check Customer Payment Discount Amount Function Start*/
            function paymentDicsount($customer_id) {
              $returnDiscount = 0;

              $paymentDiscount = AdvanceCustomerPayment::where('customer_id',$customer_id)->get();

              $discountOutPut = 0;
              if ($paymentDiscount === NULL) {
                $returnDiscount = 0;
              } else {
                $total_discount = $paymentDiscount;
                foreach ($total_discount as $value) {
                  $discountOutPut = $value->payment_discount + $discountOutPut;
                }

                $returnDiscount = $discountOutPut;
              }
        // dd($returnDiscount);
              return $returnDiscount;
            }
            /* Check Customer Payment Discount Amount Function Ends*/

            /* Check Customer Pre Payable Amount Function Start*/
            function customerCredtiAmount($customer_id) {
              $returnPayable = 0;

              $totalPayable = CustomerOpeningBalance::where('customer_id',$customer_id)->where('type','credit')->get();

              $payableOutPut = 0;
              if ($totalPayable === NULL) {
                $returnPayable = 0;
              } else {
                $total_payable = $totalPayable;
                foreach ($total_payable as $value) {
                  $payableOutPut = $value->amount + $payableOutPut;
                }

                $returnPayable = $payableOutPut;
              }
              return $returnPayable;
            }
            /* Check Customer Pre Payable Amount Function Ends*/

            /*SUM all Customers Amount Areawise Start*/
            function allCustomersAmount($area_id)
            {
              $allCustomersSalesAmount = Sale::where('area_id',$area_id)->where('status',1)->sum('amount');
              return $allCustomersSalesAmount;
            }
            function allCustomersDebitAmount($area_id){
              $allCustomersReceivable = CustomerOpeningBalance::where('area_id',$area_id)->where('type','debit')->sum('amount');
              return $allCustomersReceivable;
            }
            function allCustomersAdvanceAmount($area_id){
              $allCustoemrsAdvance = AdvanceCustomerPayment::where('area_id',$area_id)->sum('amount');
              return $allCustoemrsAdvance;
            }
            function allCustomersCreditAmount($area_id){
              $allCustomersPayable = CustomerOpeningBalance::where('area_id',$area_id)->where('type','credit')->sum('amount');
              return $allCustomersPayable;
            }
            function allCustomersDiscount($area_id){
              $allCustomersDiscount = AdvanceCustomerPayment::where('area_id',$area_id)->sum('payment_discount');
              return $allCustomersDiscount;
            }
            /*SUM all Customers Amount Areawise Ends*/
            $customerSaleAmount = 0;
            $allCustomersAmount = 0;

            $customerReceivableAmount = 0;
            $allCustomersDebitAmount = 0;

            $customerAdvanceAmount = 0;
            $allCustomersAdvanceAmount = 0;

            $customerPayableAmount = 0;
            $allCustomersCreditAmount = 0;
            $counter = 0;
            $customers = Customer::where('area_id',$area_id)->get()->toArray();
            foreach ($customers as $key => $customer) {
              $customer_id = $customer['id'];
              $area_id = $customer['area_id'];
              $customerName = $customer['name'];

              /*---- Customer Total Amount Start ------*/
              // foreach ($customerPayment as $key => $balance) {

              if ($area_id == $customer['area_id']) {


                $customerSaleAmount = totalAmount($customer_id);
                $allCustomersAmount = allCustomersAmount($area_id);
                  // dd($allCustomersAmount);
                $customerReceivableAmount = customerDebitAmount($customer_id);
                $allCustomersDebitAmount = allCustomersDebitAmount($area_id);

                $customerAdvanceAmount = advanceAmount($customer_id);
                $singleCustomerDiscount = paymentDicsount($customer_id);
                $allCustomersDiscount = allCustomersDiscount($area_id);
                $allCustomersAdvanceAmount = allCustomersAdvanceAmount($area_id);

                $customerPayableAmount = customerCredtiAmount($customer_id);
                $allCustomersCreditAmount = allCustomersCreditAmount($area_id);
                  // dd($allCustomersCreditAmount);
                $customerBalance = ($customerSaleAmount + $customerReceivableAmount) - ($singleCustomerDiscount + $customerAdvanceAmount + $customerPayableAmount);

                $counter = $counter+1;
                ?>
                <tr>
                  <td colspan="1">{{$counter}}</td>
                  <td colspan="4">{{$customerName}}</td>
                  <?php
                  if($customerBalance<0){
                    ?>
                    <td colspan="3">{{-1*($customerBalance)}} CR</td>
                    <?php
                  }else{
                    ?>
                    <td colspan="3">{{($customerBalance)}} DB</td>
                    <?php
                  }

                  ?>
                  <td colspan="4"></td>
                </tr>

                <?php

              }


              /*---- Customer Total Amount Ends ------*/




            }
            $allCustomersBalance = ($allCustomersAmount + $allCustomersDebitAmount) - ($allCustomersDiscount + $allCustomersAdvanceAmount + $allCustomersCreditAmount);
            ?>
            <tr style="background-color: #FBCEB1; font-family: #POUND, sans-serif; font-weight: bold;">
              <td colspan="5">Total Balance</td>
              <?php
              if($allCustomersBalance<0){
                ?>
                <td colspan="3">{{-1*($allCustomersBalance)}} CR</td>
                <?php
              }else{
                ?>
                <td colspan="3">{{($allCustomersBalance)}} DB</td>
                <?php
              }

              ?>
              <td colspan="4"></td>
            </tr>



          </table>
        </td>
      </tr>
      @endif

      <!-- 5nd Row -->
      <tr>
        <td colspan="12" style="padding-top: 10px;"></td>
      </tr>
      <!-- 6nd Row -->



      <!-- 14nd Row -->
      <!-- 15nd Row -->
      <!-- <tr>
        <td colspan="12"><b>Abid Traders, </b>Pakistan (Pvt) Ltd.</td>
      </tr> -->
      <!-- 15nd Row -->
      <!-- 16nd Row -->
      <tr>
        <td colspan="12" style="padding-top: 20px;"> <b>Muhammad Haseeb</b></td>
      </tr>
      <!-- 16nd Row -->
      <!-- 17nd Row -->
      <tr>
        <td colspan="12" style="font-size: small;">(Owner)</td>
      </tr>
      <!-- 17nd Row -->
    </table>
  </center>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script> -->

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-W8fXfP3gkOKtndU4JGtKDvXbO53Wy8SZCQHczT5FMiiqmQfUpWbYdTil/SxwZgAN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.min.js" integrity="sha384-skAcpIdS7UcVUC05LJ9Dxay8AXcDYfBJqt1CJ85S/CFujBsIzCIv+l9liuYLaMQ/" crossorigin="anonymous"></script>
    -->
  </body>
</html>
