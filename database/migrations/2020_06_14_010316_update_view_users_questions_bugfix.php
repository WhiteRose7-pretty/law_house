<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateViewUsersQuestionsBugfix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW
                users_questions
            AS SELECT
                qs.user_id,
                q.*,
                uqr.last_answer_at,
                uqr.last_incorrect_at,
                uqr.last_correct_at,
                uqr.last_repeat_at,
                uqr.next_repeat_at,
                uqr.correct_in_row,
                uqr.skip
            FROM
                users_packages_questions_sets qs
                JOIN questions q on q.questions_set_id = qs.questions_set_id
                LEFT JOIN users_questions_repeats uqr on uqr.question_id = q.id and uqr.user_id = qs.user_id
            WHERE
                qs.free = 1
                OR
                qs.valid_until >= now()
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
            CREATE OR REPLACE VIEW
                users_questions
            AS SELECT
                qs.user_id,
                q.*,
                uqr.last_answer_at,
                uqr.last_incorrect_at,
                uqr.last_correct_at,
                uqr.last_repeat_at,
                uqr.next_repeat_at,
                uqr.correct_in_row,
                uqr.skip
            FROM
                users_packages_questions_sets qs
                JOIN questions q on q.questions_set_id = qs.questions_set_id
                LEFT JOIN users_questions_repeats uqr on uqr.question_id = q.id
            WHERE
                qs.free = 1
                OR
                qs.valid_until >= now()
            ;
        ");
    }
}
