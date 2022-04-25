<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTransactionsInvoicing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_transactions', function (Blueprint $table) {
            $table->unsignedTinyInteger('tax')->nullable()->after('discount');
            $table->text('service_invoice_data')->nullable();
            $table->string('service_invoice_token')->nullable();
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
                'tax',
                'service_invoice_data',
                'service_invoice_token'
            ]);
        });
    }
}
