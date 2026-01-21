<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchemeTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('scheme_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_scheme_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->enum('type', ['accumulation', 'redemption', 'bonus']);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            // Make sure customer_schemes table exists first and has bigIncrements('id')
            $table->foreign('customer_scheme_id')
                  ->references('id')
                  ->on('customer_schemes')
                  ->onDelete('cascade');

            // For sales table, check if it exists and has the correct structure
            $table->foreign('sale_id')
                  ->references('id')
                  ->on('sales')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheme_transactions');
    }
}
