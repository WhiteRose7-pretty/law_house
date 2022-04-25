<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
            $table->timestamp('published_at')->nullable();
            $table->boolean('private')->default(false);
            $table->string('category',16);
            $table->string('title');
            $table->string('title_uri')->nullable();
            $table->text('lead', 1024)->nullable();
            $table->text('full')->nullable();
            $table->index('category');
            $table->unique(['category', 'title_uri']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contents');
    }
}
