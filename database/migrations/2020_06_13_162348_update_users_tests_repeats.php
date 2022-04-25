<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTestsRepeats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `users_tests`
            MODIFY COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER updated_at
        ");

        Schema::table('users_tests', function (Blueprint $table) {
            $table->boolean('is_repeat')->default(false);
            $table->date('repeat_date')->nullable();
            $table->string('repeat_sets_ids')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_tests', function (Blueprint $table) {
            $table->dropColumn([
                'is_repeat', 'repeat_date', 'repeat_sets_ids'
            ]);
        });
    }
}
