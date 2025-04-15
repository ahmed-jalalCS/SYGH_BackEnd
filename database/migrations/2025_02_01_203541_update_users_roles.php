<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First ensure all users have correct role_id based on their role string
        $roleMapping = DB::table('roles')->pluck('id', 'name');

        foreach ($roleMapping as $roleName => $roleId) {
            DB::table('users')
                ->where('role', $roleName)
                ->update(['role_id' => $roleId]);
        }


        // Then remove the role column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        // Add back the role column if we need to rollback
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable();
        });

        // Restore the role values from role_id
        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            DB::table('users')
                ->where('role_id', $role->id)
                ->update(['role' => $role->name]);
        }
    }
};
