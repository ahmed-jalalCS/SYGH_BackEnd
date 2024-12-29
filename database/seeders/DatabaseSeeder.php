<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\College;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Student;
use App\Models\Evaluate;
use App\Models\Department;
use App\Models\Supervisor;
use App\Models\University;
use App\Models\Socialmedie;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\LibrarayStaff;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  User::factory(10)->create();
        //  University::factory(5)->create();
        //  College::factory(6)->create();
        //  Department::factory(10)->create();
        //  LibrarayStaff::factory(6)->create();
        //  Supervisor::factory(10)->create();
        //  Project::factory(20)->create();
        //  Student::factory(54)->create();
        //  Socialmedie::factory(10)->create();
        //  Evaluate::factory(40)->create();
        //  Comment::factory(30)->create();


        // Create users (#Admins and superAdmins) 
        // User::factory()->count(10)->create();
        $superAdmin = User::factory()->create([
            'name' => 'Ouis Alhetar',
            'email' => 'ouis@gmail.com',
        ]);
        $superAdminRole = Role::create(['name' => 'Super-Admin']);
        $superAdmin->assignRole($superAdminRole);

        $user = User::factory()->create([
            'name' => 'Noory',
            'email' => 'noory@gmail.com',
        ]);

        $adminRole = Role::create(['name' => 'Admin']);
        $user->assignRole($adminRole);

        $testUser = User::factory()->create([
            'name' => 'Writer',
            'email' => 'writer@gmail.com',
        ]);

        $testRole = Role::create(['name' => 'Writer']);
        $testUser->assignRole($testRole);
    
    }
}
