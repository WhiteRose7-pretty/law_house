<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserRemovalFixesUsersTestsQuestionsConstraintsFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_tests_questions', function (Blueprint $table) {
            $table->dropForeign('users_tests_questions_user_question_answer_id_foreign');
            $table->foreign('user_question_answer_id')->references('id')->on('users_questions_answers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_tests_questions', function (Blueprint $table) {
            $table->dropForeign('users_tests_questions_user_question_answer_id_foreign');
            $table->foreign('user_question_answer_id')->references('id')->on('users_questions_answers');
        });
    }
}
