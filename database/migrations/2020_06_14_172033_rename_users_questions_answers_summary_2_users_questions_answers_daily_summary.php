<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUsersQuestionsAnswersSummary2UsersQuestionsAnswersDailySummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            DROP VIEW users_questions_answers_summary
        ");

        DB::statement("
            CREATE VIEW
                users_stats_questions_answers_daily_summary
            AS SELECT
                user_id,
                DATE_FORMAT(created_at, '%Y-%m-%d') as answered_at,
                sum(correct) as correct,
                count(*) as total
            FROM
                users_questions_answers
            GROUP BY
                DATE_FORMAT(created_at, '%Y-%m-%d'),
                user_id
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
            DROP VIEW users_stats_questions_answers_daily_summary
        ");

        DB::statement("
            CREATE VIEW
                users_questions_answers_summary
            AS SELECT
                user_id,
                DATE_FORMAT(created_at, '%Y-%m-%d') as answered_at,
                sum(correct) as correct,
                count(*) as total
            FROM
                users_questions_answers
            GROUP BY
                DATE_FORMAT(created_at, '%Y-%m-%d'),
                user_id
        ");
    }
}
