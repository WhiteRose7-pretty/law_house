<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersStatsDailyAggregate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_stats_daily_aggregate', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('questions_set_id');
            $table->date('date');
            $table->boolean('change')->default(false);
            $table->boolean('new')->default(false);
            $table->unsignedSmallInteger('repeat');
            $table->unsignedSmallInteger('answers');
            $table->unsignedSmallInteger('correct');
            $table->unsignedSmallInteger('incorrect');
            $table->unsignedSmallInteger('correct_in_row');
            $table->unsignedSmallInteger('all_answers');
            $table->unsignedSmallInteger('all_correct');
            $table->unsignedSmallInteger('all_incorrect');
            $table->unsignedSmallInteger('all_repeat');
            $table->unique(['user_id', 'question_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_stats_daily_aggregate');
    }
}
