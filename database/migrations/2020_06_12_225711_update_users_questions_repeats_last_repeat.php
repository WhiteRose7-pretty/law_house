<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersQuestionsRepeatsLastRepeat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_questions_repeats', function (Blueprint $table) {
            $table->renameColumn('next_answer_at','next_repeat_at');
        });
        DB::statement("
            ALTER TABLE `users_questions_repeats`
            CHANGE `next_repeat_at` `next_repeat_at` TIMESTAMP NULL DEFAULT NULL
        ");
        Schema::table('users_questions_repeats', function (Blueprint $table) {
            $table->timestamp('last_repeat_at')->nullable()->after('last_answer_at');
            $table->timestamp('last_correct_at')->nullable()->after('last_answer_at');
            $table->timestamp('last_incorrect_at')->nullable()->after('last_answer_at');
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
            $table->renameColumn('next_repeat_at','next_answer_at');
        });
        DB::statement("
            ALTER TABLE `users_questions_repeats`
            CHANGE `next_answer_at` `next_answer_at` TIMESTAMP NULL DEFAULT NULL
        ");
        Schema::table('users_questions_repeats', function (Blueprint $table) {
            $table->dropColumn([
                'last_repeat_at', 'last_correct_at', 'last_incorrect_at'
            ]);
        });
    }
}
