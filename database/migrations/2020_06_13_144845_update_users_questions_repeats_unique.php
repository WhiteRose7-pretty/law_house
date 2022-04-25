<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersQuestionsRepeatsUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_questions_repeats', function (Blueprint $table) {
            $table->unique(['user_id','question_id'],'only_one_uq_pair');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_questions_repeats', function (Blueprint $table) {
              $table->dropUnique('only_one_uq_pair');
        });
    }
}
