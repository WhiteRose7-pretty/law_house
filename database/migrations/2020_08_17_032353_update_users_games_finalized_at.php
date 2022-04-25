<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersGamesFinalizedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_games', function (Blueprint $table) {
            $table->timestamp('finalized_at')->nullable()->after('finish_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_games', function (Blueprint $table) {
            $table->dropColumn([
                'finalized_at'
            ]);
        });
    }
}
