<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PackagesQuestionsSetFixColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages_sets', function (Blueprint $table) {
            $table->renameColumn('question_set_id','questions_set_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages_sets', function (Blueprint $table) {
            $table->renameColumn('questions_set_id','question_set_id');
        });
    }
}
