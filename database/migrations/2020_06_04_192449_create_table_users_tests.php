<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_tests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->text('info');
            $table->unsignedInteger('time_limit')->default(0);
            $table->boolean('show_correct')->default(false);
            $table->unsignedInteger('questions_count')->default(0);
            $table->unsignedInteger('questions_answered_count')->default(0);
            $table->unsignedInteger('questions_answered_correct_count')->default(0);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_tests');
    }
}
