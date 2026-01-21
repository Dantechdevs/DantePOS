<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('branch')->nullable();
            $table->string('invoice_no');
            $table->string('sale_type')->default('single');
            $table->dateTime('date');
            $table->integer('customer_id');
            $table->integer('godown_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->tinyInteger('status');
            $table->LongText('description')->nullable();
            $table->LongText('items_addon');
            $table->integer('total_qty')->default(0);
            $table->double('sub_total', 10, 2);
            $table->double('other_charges', 10, 2)->default(0);
            $table->string('discount_type')->nullable();
            $table->integer('discount')->default(0);
            $table->double('discount_amount',10,2)->default(0);
            $table->string('courier')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_status')->nullable()->comment('paid, partial, unpaid');
            $table->double('grand_total', 10, 2)->comment('grand total');
            $table->double('paid_amount', 10, 2)->comment('paid amount');
            $table->double('balance_amount', 10, 2)->comment('refund amount');
            $table->double('change_amount', 10, 2)->comment('change amount');
            $table->date('due_date')->nullable();
            $table->tinyInteger('createdBy')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
