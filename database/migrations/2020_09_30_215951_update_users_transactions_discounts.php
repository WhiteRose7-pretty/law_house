<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTransactionsDiscounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_transactions', function (Blueprint $table) {
            $table->string('discount_code')->nullable()->after('description');
            $table->unsignedTinyInteger('discount')->nullable()->after('discount_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'discount_code',
                'discount'
            ]);
        });
    }
}
