<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateCustomerPaymentsTable extends Migration
{
    use SoftDeletes;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relationship with sale
            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('cascade');

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->dateTime('payment_date')->index(); // Added index for better query performance


            $table->text('notes')->nullable();

            // Tracking
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            // Foreign keys
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->softDeletes();

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
        Schema::dropIfExists('customer_payments');
    }
}
