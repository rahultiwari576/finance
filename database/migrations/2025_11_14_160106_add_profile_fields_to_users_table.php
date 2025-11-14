<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('age');
            $table->string('area')->nullable()->after('address');
            $table->string('city')->nullable()->after('area');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->string('profession')->nullable()->after('zip_code');
            $table->string('education')->nullable()->after('profession');
            $table->text('additional_info')->nullable()->after('education');
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
            $table->dropColumn([
                'address',
                'area',
                'city',
                'state',
                'zip_code',
                'profession',
                'education',
                'additional_info',
            ]);
        });
    }
}
