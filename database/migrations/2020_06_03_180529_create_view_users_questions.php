<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewUsersQuestions extends Migration
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
                users_questions
            AS SELECT
                qs.user_id,
                q.*
            FROM
                users_packages_questions_sets qs
                JOIN questions q on q.questions_set_id = qs.questions_set_id
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
            DROP VIEW users_questions;
        ");
    }
}
