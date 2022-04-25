<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultipleViewsUsersStatsAggregateSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ##
        ## GROUP BY USER ID AND QUESTIONS SETS IDS
        ##

        // DAILY

        DB::statement("
            CREATE OR REPLACE VIEW
                users_stats_daily_aggregate_set_summary
            AS SELECT
                a.user_id,
                a.date,
                a.questions_set_id,
                sum(a.new) as `new`,
                sum(a.repeat) as `repeat`,
                sum(a.answers) as `answers`,
                sum(a.correct) as `correct`,
                sum(a.incorrect) as `incorrect`,
                sum(a.all_answers) as `all_answers`,
                sum(a.all_correct) as `all_correct`,
                sum(a.all_incorrect) as `all_incorrect`,
                sum(a.all_repeat) as `all_repeat`,
                sum(if(a.correct_in_row=0,1,0)) as `correct_0`,
                sum(if(a.correct_in_row=1,1,0)) as `correct_1`,
                sum(if(a.correct_in_row=2,1,0)) as `correct_2`,
                sum(if(a.correct_in_row=3,1,0)) as `correct_3`,
                sum(if(a.correct_in_row=4,1,0)) as `correct_4`,
                sum(if(a.correct_in_row=5,1,0)) as `correct_5`,
                sum(if(a.correct_in_row>5,1,0)) as `correct_m`
            FROM
                users_stats_daily_aggregate a
            GROUP BY
                a.user_id,
                a.date,
                a.questions_set_id
        ");

        // WEEKLY

        DB::statement("
            CREATE OR REPLACE VIEW
                users_stats_weekly_aggregate_set_summary
            AS SELECT
                a.user_id,
                max(a.date) as `date`,
                a.questions_set_id,
                sum(a.new) as `new`,
                sum(a.repeat) as `repeat`,
                sum(a.answers) as `answers`,
                sum(a.correct) as `correct`,
                sum(a.incorrect) as `incorrect`,
                sum(if(aaa.all_answers,aaa.all_answers,0)) as `all_answers`,
                sum(if(aaa.all_correct,aaa.all_correct,0)) as `all_correct`,
                sum(if(aaa.all_incorrect,aaa.all_incorrect,0)) as `all_incorrect`,
                sum(if(aaa.all_repeat,aaa.all_repeat,0)) as `all_repeat`,
                sum(if(aaa.correct_0,aaa.correct_0,0)) as `correct_0`,
                sum(if(aaa.correct_1,aaa.correct_1,0)) as `correct_1`,
                sum(if(aaa.correct_2,aaa.correct_2,0)) as `correct_2`,
                sum(if(aaa.correct_3,aaa.correct_3,0)) as `correct_3`,
                sum(if(aaa.correct_4,aaa.correct_4,0)) as `correct_4`,
                sum(if(aaa.correct_5,aaa.correct_5,0)) as `correct_5`,
                sum(if(aaa.correct_m,aaa.correct_m,0)) as `correct_m`
            FROM
                users_stats_daily_aggregate_set_summary a
                left join (
                    select
                        max(`date`) as `date`,
                        user_id,
                        questions_set_id
                    from
                        users_stats_daily_aggregate_set_summary
                    group by
                        user_id,
                        questions_set_id,
                        YEAR(`date`),
                        WEEKOFYEAR(`date`)
                ) aa on aa.date = a.date and aa.user_id = a.user_id and aa.questions_set_id = a.questions_set_id
                left join (
                    select
                        *
                    from
                        users_stats_daily_aggregate_set_summary
                ) aaa on aaa.date = aa.date and aaa.user_id = a.user_id and aaa.questions_set_id = a.questions_set_id
            GROUP BY
                a.user_id,
                a.questions_set_id,
                YEAR(a.date),
                WEEKOFYEAR(a.date)
        ");

        // MONTHLY

        DB::statement("
            CREATE OR REPLACE VIEW
                users_stats_monthly_aggregate_set_summary
            AS SELECT
                a.user_id,
                max(a.date) as `date`,
                a.questions_set_id,
                sum(a.new) as `new`,
                sum(a.repeat) as `repeat`,
                sum(a.answers) as `answers`,
                sum(a.correct) as `correct`,
                sum(a.incorrect) as `incorrect`,
                sum(if(aaa.all_answers,aaa.all_answers,0)) as `all_answers`,
                sum(if(aaa.all_correct,aaa.all_correct,0)) as `all_correct`,
                sum(if(aaa.all_incorrect,aaa.all_incorrect,0)) as `all_incorrect`,
                sum(if(aaa.all_repeat,aaa.all_repeat,0)) as `all_repeat`,
                sum(if(aaa.correct_0,aaa.correct_0,0)) as `correct_0`,
                sum(if(aaa.correct_1,aaa.correct_1,0)) as `correct_1`,
                sum(if(aaa.correct_2,aaa.correct_2,0)) as `correct_2`,
                sum(if(aaa.correct_3,aaa.correct_3,0)) as `correct_3`,
                sum(if(aaa.correct_4,aaa.correct_4,0)) as `correct_4`,
                sum(if(aaa.correct_5,aaa.correct_5,0)) as `correct_5`,
                sum(if(aaa.correct_m,aaa.correct_m,0)) as `correct_m`
            FROM
                users_stats_daily_aggregate_set_summary a
                left join (
                    select
                        max(`date`) as `date`,
                        user_id,
                        questions_set_id
                    from
                        users_stats_daily_aggregate_set_summary
                    group by
                        user_id,
                        questions_set_id,
                        YEAR(`date`),
                        MONTH(`date`)
                ) aa on aa.date = a.date and aa.user_id = a.user_id and aa.questions_set_id = a.questions_set_id
                left join (
                    select
                        *
                    from
                        users_stats_daily_aggregate_set_summary
                ) aaa on aaa.date = aa.date and aaa.user_id = a.user_id and aaa.questions_set_id = a.questions_set_id
            GROUP BY
                a.user_id,
                a.questions_set_id,
                YEAR(a.date),
                MONTH(a.date)
        ");

        ##
        ## GROUP BY USER_ID
        ##

        // DAILY

        DB::statement("
            CREATE OR REPLACE VIEW
                users_stats_daily_aggregate_total_summary
            AS SELECT
                a.user_id,
                a.date,
                sum(a.new) as `new`,
                sum(a.repeat) as `repeat`,
                sum(a.answers) as `answers`,
                sum(a.correct) as `correct`,
                sum(a.incorrect) as `incorrect`,
                sum(a.all_answers) as `all_answers`,
                sum(a.all_correct) as `all_correct`,
                sum(a.all_incorrect) as `all_incorrect`,
                sum(a.all_repeat) as `all_repeat`,
                sum(if(a.correct_in_row=0,1,0)) as `correct_0`,
                sum(if(a.correct_in_row=1,1,0)) as `correct_1`,
                sum(if(a.correct_in_row=2,1,0)) as `correct_2`,
                sum(if(a.correct_in_row=3,1,0)) as `correct_3`,
                sum(if(a.correct_in_row=4,1,0)) as `correct_4`,
                sum(if(a.correct_in_row=5,1,0)) as `correct_5`,
                sum(if(a.correct_in_row>5,1,0)) as `correct_m`
            FROM
                users_stats_daily_aggregate a
            GROUP BY
                a.user_id,
                a.date
        ");

        // WEEKLY

        DB::statement("
            CREATE OR REPLACE VIEW
                users_stats_weekly_aggregate_total_summary
            AS SELECT
                a.user_id,
                max(a.date) as `date`,
                sum(a.new) as `new`,
                sum(a.repeat) as `repeat`,
                sum(a.answers) as `answers`,
                sum(a.correct) as `correct`,
                sum(a.incorrect) as `incorrect`,
                sum(if(aaa.all_answers,aaa.all_answers,0)) as `all_answers`,
                sum(if(aaa.all_correct,aaa.all_correct,0)) as `all_correct`,
                sum(if(aaa.all_incorrect,aaa.all_incorrect,0)) as `all_incorrect`,
                sum(if(aaa.all_repeat,aaa.all_repeat,0)) as `all_repeat`,
                sum(if(aaa.correct_0,aaa.correct_0,0)) as `correct_0`,
                sum(if(aaa.correct_1,aaa.correct_1,0)) as `correct_1`,
                sum(if(aaa.correct_2,aaa.correct_2,0)) as `correct_2`,
                sum(if(aaa.correct_3,aaa.correct_3,0)) as `correct_3`,
                sum(if(aaa.correct_4,aaa.correct_4,0)) as `correct_4`,
                sum(if(aaa.correct_5,aaa.correct_5,0)) as `correct_5`,
                sum(if(aaa.correct_m,aaa.correct_m,0)) as `correct_m`
            FROM
                users_stats_daily_aggregate_total_summary a
                left join (
                    select
                        max(`date`) as `date`,
                        user_id
                    from
                        users_stats_daily_aggregate_total_summary
                    group by
                        user_id,
                        YEAR(`date`),
                        WEEKOFYEAR(`date`)
                ) aa on aa.date = a.date and aa.user_id = a.user_id
                left join (
                    select
                        *
                    from
                        users_stats_daily_aggregate_total_summary
                ) aaa on aaa.date = aa.date and aaa.user_id = a.user_id
            GROUP BY
                a.user_id,
                YEAR(a.date),
                WEEKOFYEAR(a.date)
        ");

        // MONTHLY

        DB::statement("
            CREATE OR REPLACE VIEW
                users_stats_monthly_aggregate_total_summary
            AS SELECT
                a.user_id,
                max(a.date) as `date`,
                sum(a.new) as `new`,
                sum(a.repeat) as `repeat`,
                sum(a.answers) as `answers`,
                sum(a.correct) as `correct`,
                sum(a.incorrect) as `incorrect`,
                sum(if(aaa.all_answers,aaa.all_answers,0)) as `all_answers`,
                sum(if(aaa.all_correct,aaa.all_correct,0)) as `all_correct`,
                sum(if(aaa.all_incorrect,aaa.all_incorrect,0)) as `all_incorrect`,
                sum(if(aaa.all_repeat,aaa.all_repeat,0)) as `all_repeat`,
                sum(if(aaa.correct_0,aaa.correct_0,0)) as `correct_0`,
                sum(if(aaa.correct_1,aaa.correct_1,0)) as `correct_1`,
                sum(if(aaa.correct_2,aaa.correct_2,0)) as `correct_2`,
                sum(if(aaa.correct_3,aaa.correct_3,0)) as `correct_3`,
                sum(if(aaa.correct_4,aaa.correct_4,0)) as `correct_4`,
                sum(if(aaa.correct_5,aaa.correct_5,0)) as `correct_5`,
                sum(if(aaa.correct_m,aaa.correct_m,0)) as `correct_m`
            FROM
                users_stats_daily_aggregate_total_summary a
                left join (
                    select
                        max(`date`) as `date`,
                        user_id
                    from
                        users_stats_daily_aggregate_total_summary
                    group by
                        user_id,
                        YEAR(`date`),
                        MONTH(`date`)
                ) aa on aa.date = a.date and aa.user_id = a.user_id
                left join (
                    select
                        *
                    from
                        users_stats_daily_aggregate_total_summary
                ) aaa on aaa.date = aa.date and aaa.user_id = a.user_id
            GROUP BY
                a.user_id,
                YEAR(a.date),
                MONTH(a.date)
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
          DROP VIEW users_stats_daily_aggregate_set_summary;
      ");

      DB::statement("
          DROP VIEW users_stats_weekly_aggregate_set_summary;
      ");

      DB::statement("
          DROP VIEW users_stats_monthly_aggregate_set_summary;
      ");

      DB::statement("
          DROP VIEW users_stats_daily_aggregate_total_summary;
      ");

      DB::statement("
          DROP VIEW users_stats_weekly_aggregate_total_summary;
      ");

      DB::statement("
          DROP VIEW users_stats_monthly_aggregate_total_summary;
      ");
    }
}
