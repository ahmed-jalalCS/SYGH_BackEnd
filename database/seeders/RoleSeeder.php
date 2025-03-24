<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        php artisan db:seed --class=RoleSeeder
        $roles = [
            ['name'=>'SuperAdmin','slug'=>'super_admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Library Staff', 'slug' => 'library_staff'],
            ['name' => 'Supervisor', 'slug' => 'supervisor'],
            ['name' => 'Student', 'slug' => 'student'],
            ['name' => 'User', 'slug' => 'user'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

    }
}
