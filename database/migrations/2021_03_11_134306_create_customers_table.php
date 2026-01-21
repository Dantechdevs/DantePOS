<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('area_id')->nullable();
            $table->string('name');
            $table->string('name_ur');
            $table->string('email')->unique()->nullable();
            $table->string('national_id')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->float('opening_balance')->default(0);
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
        Schema::dropIfExists('customers');
    }
}
