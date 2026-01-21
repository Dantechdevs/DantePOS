<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no');
            $table->dateTime('date');
            $table->integer('customer_id');
            $table->integer('area_id')->nullable();
            $table->string('type');
            $table->string('description')->nullable();
            $table->double('amount',10,2);
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
        Schema::dropIfExists('customer_opening_balances');
    }
}
