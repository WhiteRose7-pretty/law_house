<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuestionOptionIdInTableUsersQuestionsAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_questions_answers', function (Blueprint $table) {
            $table->dropForeign('question_option_id');
            $table->foreign('question_option_id')->references('id')->on('questions_options')->onDelete('cascade')->change();
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

            $table->foreign('question_option_id')->references('id')->on('questions_options')->change();
        });
    }
}
