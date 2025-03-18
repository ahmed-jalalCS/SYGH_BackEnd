<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetDefaultUserIdInSupervisorsTable extends Migration
{
    public function up()
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(1)->change(); // Replace 1 with the default user_id
        });
    }

    public function down()
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->default(null)->change();
        });
    }
}
