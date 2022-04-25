<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersGamesParticipantsAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_games_participants_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_game_participant_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('user_question_answer_id')->nullable();
            $table->boolean('correct')->nullable();
            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('user_game_participant_id','participant')->references('id')->on('users_games_participants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_games_participants_answers');
    }
}
