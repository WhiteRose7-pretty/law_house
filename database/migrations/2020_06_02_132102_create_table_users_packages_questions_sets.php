<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersPackagesQuestionsSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_packages_questions_sets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('free')->default(false);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_package_id');
            $table->unsignedBigInteger('questions_set_id');
            $table->foreign('user_package_id')->references('id')->on('users_packages')->onDelete('cascade');
            $table->foreign('questions_set_id')->references('id')->on('questions_sets')->onDelete('cascade');
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
        Schema::dropIfExists('users_packages_questions_sets');
    }
}
