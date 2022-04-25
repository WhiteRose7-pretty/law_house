<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersGamesFinishCheckedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_games', function (Blueprint $table) {
            $table->timestamp('finish_checked_at')->nullable()->after('finished_at');
        });
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_games', function (Blueprint $table) {
          $table->dropColumn([
              'finish_checked_at'
          ]);
        });
    }
}
