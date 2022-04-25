<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTestsTimeCompleted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('completion_seconds')->after('time_limit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_tests', function (Blueprint $table) {
          $table->dropColumn([
              'completion_seconds',
          ]);
        });
    }
}
