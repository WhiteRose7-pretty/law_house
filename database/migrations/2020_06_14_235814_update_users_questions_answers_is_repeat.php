<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersQuestionsAnswersIsRepeat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_questions_answers', function (Blueprint $table) {
            $table->boolean('is_repeat')->default(0);
        });

        DB::statement("
            update
                users_questions_answers
            set
                is_repeat = 1
            where
                id in (
                  select
                      user_question_answer_id
                  from
                      users_tests_questions
                  where
                      user_test_id in (
                          select id from users_tests where is_repeat=1
                      )
                );
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_questions_answers', function (Blueprint $table) {
            $table->dropColumn([
                'is_repeat'
            ]);
        });
    }
}
