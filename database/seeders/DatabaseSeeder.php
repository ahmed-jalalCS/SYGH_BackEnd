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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
            User::factory()->count(70)->create();
            University::factory()->count(10)->create();
            College::factory()->count(6)->create();
            Department::factory()->count(10)->create();
            LibrarayStaff::factory()->count(6)->create();
            Supervisor::factory()->count(10)->create();
            Project::factory()->count(20)->create();
            Student::factory()->count(54)->create();
            Socialmedie::factory()->count(10)->create();
            Evaluate::factory()->count(40)->create();
            Comment::factory()->count(30)->create();
    
    }
}
