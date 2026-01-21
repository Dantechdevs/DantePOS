<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('purchase_no');
            $table->dateTime('date');
            $table->integer('supplier_id');
            $table->string('status');
            $table->text('description')->nullable();
            $table->LongText('items_addon');
            $table->integer('total_qty')->default(0);
            $table->double('sub_total', 10, 2);
            $table->double('other_charges', 10, 2)->default(0);
            $table->string('discount_type')->nullable();
            $table->integer('discount')->default(0);
            $table->double('discount_amount',10,2)->default(0);
            $table->double('grand_total', 10, 2)->comment('grand total');
            $table->string('attachment')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
