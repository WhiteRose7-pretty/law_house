<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('hash',9);
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_package_id')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('outdated_at')->nullable();
            $table->timestamp('billed_at')->nullable();
            $table->enum('status',['cancelled','verified','registered','new'])->default('new');
            $table->string('p24_token')->nullable()->unique();
            $table->string('description');
            $table->mediumInteger('amount_net');
            $table->mediumInteger('amount_vat');
            $table->mediumInteger('amount_gross');
            $table->enum('bill_format',['invoice','receipt']);
            $table->text('user_invoice_data')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_package_id')->references('id')->on('users_packages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_transactions');
    }
}
