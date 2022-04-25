<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersWithMinimumFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['user', 'editor', 'admin'])->after('id')->default('user');
            $table->string('surname')->after('name')->nullable();;
            $table->string('address_street_name')->nullable();
            $table->string('address_street_number')->nullable();
            $table->string('address_apartment_number')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_post_code')->nullable();
            $table->string('tax_nip')->nullable();
            $table->softDeletes('deleted_at', 0)->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'type',
                    'surname',
                    'address_street_name',
                    'address_street_number',
                    'address_apartment_number',
                    'address_city',
                    'address_post_code',
                    'tax_nip',
                    'deleted_at'
                ]
              );
        });
    }
}
