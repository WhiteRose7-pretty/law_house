<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_games', function (Blueprint $table) {
            $table->id();
            $table->string('hash_key',20)->unique();
            $table->unsignedBigInteger('root_game_id')->nullable();
            $table->timestamps();
            $table->timestamp('starts_at')->nullable();
            $table->boolean('auto_start')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->boolean('has_active_subgame')->default(false);
            $table->timestamp('removed_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('info');
            $table->enum('type',['exam','race']);
            $table->unsignedInteger('questions_count')->default(0);
            $table->unsignedInteger('time_limit');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_games');
    }
}
