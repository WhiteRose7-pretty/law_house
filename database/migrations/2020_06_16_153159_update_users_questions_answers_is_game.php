<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersQuestionsAnswersIsGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_questions_answers', function (Blueprint $table) {
            $table->boolean('is_game')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_questions_answers', function (Blueprint $table) {
            $table->dropColumn([
                'is_game'
            ]);
        });
    }
}
