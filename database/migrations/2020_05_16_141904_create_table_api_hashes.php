<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableApiHashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_hashes', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->timestamps();
            $table->string('session_id')->nullable()->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('type', ['user', 'editor', 'admin'])->default('user');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_hashes');
    }
}
