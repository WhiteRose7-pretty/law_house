<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewUsersQuestionsRepeatsStats extends Migration
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
              users_questions_repeats_stats
          AS SELECT
              qr.user_id,
              q.questions_set_id,
              sum(if(qr.correct_in_row = 0,1,0)) as correct_0,
              sum(if(qr.correct_in_row = 1,1,0)) as correct_1,
              sum(if(qr.correct_in_row = 2,1,0)) as correct_2,
              sum(if(qr.correct_in_row = 3,1,0)) as correct_3,
              sum(if(qr.correct_in_row = 4,1,0)) as correct_4,
              sum(if(qr.correct_in_row = 5,1,0)) as correct_5,
              sum(if(qr.correct_in_row > 5,1,0)) as correct_m,
              if(qc.c,qc.c,0) as today_repeat
          FROM
              users_questions_repeats qr
              join users_questions q on qr.question_id = q.id and qr.user_id = q.user_id
              left join (
                  select
                      user_id, questions_set_id, count(*) as c
                  FROM
                      users_questions_calendar
                  where
                      planned_at = date(now())
                  group by
                      user_id, questions_set_id
              ) qc on qc.questions_set_id = q.questions_set_id and qc.user_id = q.user_id
          GROUP BY
              q.user_id,
              q.questions_set_id
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
            DROP VIEW users_questions_repeats_stats;
        ");
    }
}
