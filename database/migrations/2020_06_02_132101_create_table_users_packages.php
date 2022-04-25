<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_packages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('name');
            $table->enum('type',['free','1m','3m','1y']);
            $table->decimal('price',6,2)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_packages');
    }
}
