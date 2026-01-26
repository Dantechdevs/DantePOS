<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('unit_id');
            $table->string('supplier_id')->nullable();
            $table->string('product_code');
            $table->string('name');
            $table->double('stock_alert', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->json('unit_info')->nullable();
            $table->integer('quantity')->default(0);
            $table->double('cost', 10, 2)->nullable();
            $table->boolean('is_scheme_product')->default(false);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('products');
    }
}
