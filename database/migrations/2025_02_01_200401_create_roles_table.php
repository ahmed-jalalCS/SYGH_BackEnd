<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only create roles table if it doesn't exist
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->timestamps();
            });

            // Insert default roles
            DB::table('roles')->insert([
                ['name' => 'Admin', 'slug' => 'admin'],
                ['name' => 'Library Staff', 'slug' => 'library_staff'],
                ['name' => 'Supervisor', 'slug' => 'supervisor'],
                ['name' => 'Student', 'slug' => 'student'],
                ['name' => 'User', 'slug' => 'user'],
            ]);
        }

        // Only add role_id if it doesn't exist
        if (!Schema::hasColumn('users', 'role_id')) {
            // Add role_id column without constraint first
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable();
            });

            // Update existing users with appropriate role_ids
            $roles = DB::table('roles')->get();
            foreach ($roles as $role) {
                DB::table('users')
                    ->where('role', 'like', $role->name)
                    ->update(['role_id' => $role->id]);
            }

            // Now add the foreign key constraint
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('roles');
    }
};
