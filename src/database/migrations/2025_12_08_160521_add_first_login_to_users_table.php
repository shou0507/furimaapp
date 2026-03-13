<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstLoginToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'first_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('first_login')->default(true);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'first_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('first_login');
            });
        }
    }
}
