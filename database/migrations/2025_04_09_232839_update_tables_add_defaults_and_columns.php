<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
        Schema::table('projects', function (Blueprint $table) {
            $table->string('document')->nullable();// stores original file name or extra info
    });
}

public function down()
{
    Schema::table('evaluates', function (Blueprint $table) {
        $table->integer('rating')->change(); // rollback
    });

    Schema::table('projects', function (Blueprint $table) {
        $table->dropColumn('document');
    });
}

};
