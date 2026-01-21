<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSchemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_schemes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('redeemed_amount', 15, 2)->default(0);
            $table->decimal('available_amount', 15, 2)->default(0);
            $table->decimal('current_cycle_amount', 15, 2)->default(0);
            $table->integer('cycles_completed')->default(0);
            $table->decimal('bonus_amount', 15, 2)->default(5000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraint (Laravel 6 syntax)
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_schemes');
    }
}
