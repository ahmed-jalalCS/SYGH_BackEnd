<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, ensure roles exist
        DB::table('roles')->insertOrIgnore([
            ['name'=>'SuperAdmin','slug'=>'super_admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Library Staff', 'slug' => 'library_staff'],
            ['name' => 'Supervisor', 'slug' => 'supervisor'],
            ['name' => 'Student', 'slug' => 'student'],
            ['name' => 'User', 'slug' => 'user'],
        ]);

        // Migrate existing users
        DB::table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $roleSlug = strtolower(str_replace(' ', '_', $user->role));
                $role = DB::table('roles')->where('slug', $roleSlug)->first();

                if ($role) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['role_id' => $role->id]);
                }
            }
        });
    }

    public function down(): void
    {
        // No need for down method as we can't reliably reverse this operation
    }
};
