<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewsQuestionNewKnowCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW
                users_questions_new_count
            AS SELECT
                q.user_id,
                q.questions_set_id,
                count(*) as count
            FROM
                users_questions q
            WHERE
                q.id not in (select question_id from users_questions_repeats)
            GROUP BY
                q.user_id, q.questions_set_id
            ;
        ");
        DB::statement("
            CREATE VIEW
                users_questions_known_count
            AS SELECT
                q.user_id,
                q.questions_set_id,
                count(*) as count
            FROM
                users_questions q
            WHERE
                q.id in (select question_id from users_questions_repeats)
            GROUP BY
                q.user_id, q.questions_set_id
            ;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            DROP VIEW users_questions_new_count;
        ");
        DB::statement("
            DROP VIEW users_questions_known_count;
        ");
    }
}
