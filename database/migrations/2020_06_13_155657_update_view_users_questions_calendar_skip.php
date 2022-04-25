<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateViewUsersQuestionsCalendarSkip extends Migration
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
                users_questions_calendar
            AS SELECT
                qr.user_id,
                q.questions_set_id,
                qr.question_id,
                if(
                  DATE_FORMAT(qr.next_repeat_at, '%Y-%m-%d') < DATE_FORMAT(now(),'%Y-%m-%d'),
                  DATE_FORMAT(now(),'%Y-%m-%d'),
                  DATE_FORMAT(qr.next_repeat_at, '%Y-%m-%d')
                ) as `planned_at`
            FROM
                users_questions_repeats qr
                join users_questions q on qr.question_id = q.id and qr.user_id = q.user_id
            WHERE
                qr.next_repeat_at IS NOT NULL
                and qr.skip = 0
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
                users_questions_calendar
            AS SELECT
                qr.user_id,
                q.questions_set_id,
                qr.question_id,
                if(
                  DATE_FORMAT(qr.next_repeat_at, '%Y-%m-%d') < DATE_FORMAT(now(),'%Y-%m-%d'),
                  DATE_FORMAT(now(),'%Y-%m-%d'),
                  DATE_FORMAT(qr.next_repeat_at, '%Y-%m-%d')
                ) as `planned_at`
            FROM
                users_questions_repeats qr
                join users_questions q on qr.question_id = q.id and qr.user_id = q.user_id
            WHERE
                qr.next_repeat_at IS NOT NULL
        ");
    }
}
