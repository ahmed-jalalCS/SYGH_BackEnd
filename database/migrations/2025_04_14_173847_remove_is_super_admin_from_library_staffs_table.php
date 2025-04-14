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
        if (Schema::hasColumn('library_staffs', 'isSuperAdmin')) {
            Schema::table('library_staffs', function (Blueprint $table) {
                $table->dropColumn('isSuperAdmin');
            });
        }
    }

    public function down()
    {
        Schema::table('library_staffs', function (Blueprint $table) {
            $table->boolean('isSuperAdmin')->default(0);
        });
    }
};
