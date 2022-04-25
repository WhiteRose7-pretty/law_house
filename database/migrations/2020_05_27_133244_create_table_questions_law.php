<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableQuestionsLaw extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions_laws', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('law_document_id');
            $table->unsignedBigInteger('law_document_element_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('law_document_id')->references('id')->on('law_documents')->onDelete('cascade');
            $table->foreign('law_document_element_id')->references('id')->on('law_documents_elements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions_laws');
    }
}
