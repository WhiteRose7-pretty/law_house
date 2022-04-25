<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableQuestionsSetsGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions_sets', function (Blueprint $table) {
            $table->string('name')->unique()->change();
            $table->string('group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions_sets', function (Blueprint $table) {
            $table->dropColumn(['group']);
            $table->string('name')->change();
        });
    }
}
