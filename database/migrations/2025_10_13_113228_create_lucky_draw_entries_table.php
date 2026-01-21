<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLuckyDrawEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_draw_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lucky_draw_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_scheme_id');
            $table->integer('cycle_number'); // Which cycle completed
            $table->string('entry_source'); // 'cycle_completion', 'manual', etc.
            $table->boolean('is_winner')->default(false);
            $table->string('prize_type')->nullable();
            $table->decimal('prize_amount', 15, 2)->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamps();

            $table->foreign('lucky_draw_id')->references('id')->on('lucky_draws')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('customer_scheme_id')->references('id')->on('customer_schemes')->onDelete('cascade');

            $table->index(['lucky_draw_id', 'customer_id']);
            $table->index(['lucky_draw_id', 'is_winner']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lucky_draw_entries');
    }
}
