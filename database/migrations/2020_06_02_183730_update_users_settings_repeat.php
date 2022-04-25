<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersSettingsRepeat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('repeat_incorrect')->default(1);
            $table->unsignedTinyInteger('repeat_1_correct')->default(3);
            $table->unsignedTinyInteger('repeat_2_correct')->default(7);
            $table->unsignedTinyInteger('repeat_3_correct')->default(14);
            $table->unsignedTinyInteger('repeat_4_correct')->default(21);
            $table->unsignedTinyInteger('repeat_5_correct')->default(32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['repeat_incorrect','repeat_1_correct','repeat_2_correct','repeat_3_correct','repeat_4_correct','repeat_5_correct']);
        });
    }
}
