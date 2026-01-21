<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrizeTrackingToLuckyDrawEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_entries', function (Blueprint $table) {
            $table->string('prize_won')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lucky_draw_entries', function (Blueprint $table) {
            $table->dropColumn(['prize_won', 'won_at', 'is_winner']);
        });
    }
}
