<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLawDocumentsElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('law_documents_elements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('law_document_id');
            $table->unsignedBigInteger('parent_element_id')->nullable();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('name')->nullable();
            $table->string('enumeration')->nullable();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->foreign('law_document_id')->references('id')->on('law_documents')->onDelete('cascade');
            $table->foreign('parent_element_id')->references('id')->on('law_documents_elements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('law_documents_elements');
    }
}
