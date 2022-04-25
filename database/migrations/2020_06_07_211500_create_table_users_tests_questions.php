<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersTestsQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_tests_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_test_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('user_question_answer_id')->nullable();
            $table->boolean('correct')->nullable();
            $table->foreign('user_test_id')->references('id')->on('users_tests')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('user_question_answer_id')->references('id')->on('users_questions_answers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_tests_questions');
    }
}
