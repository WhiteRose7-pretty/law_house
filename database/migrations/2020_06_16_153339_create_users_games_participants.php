<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersGamesParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_games_participants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_game_id');
            $table->timestamp('game_removed_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('owner')->default(false);
            $table->boolean('owner_host_only')->default(false);
            $table->boolean('ready')->nullable();
            $table->string('name');
            $table->unsignedInteger('questions_answered_count')->default(0);
            $table->unsignedInteger('questions_answered_correct_count')->default(0);
            $table->unsignedBigInteger('completion_seconds')->default(0);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_game_id')->references('id')->on('users_games')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_games_participants');
    }
}
