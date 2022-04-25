<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersQuestionRepeatSkip extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_questions_repeats', function (Blueprint $table) {
            $table->boolean('skip')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_questions_repeats', function (Blueprint $table) {
            $table->dropColumn([
                'skip',
            ]);
        });
    }
}
