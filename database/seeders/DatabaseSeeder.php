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
         User::factory(70)->create();
         University::factory(5)->create();
         College::factory(6)->create();
         Department::factory(10)->create();
         LibrarayStaff::factory(6)->create();
         Supervisor::factory(10)->create();
         Project::factory(20)->create();
         Student::factory(54)->create();
         Socialmedie::factory(10)->create();
         Evaluate::factory(40)->create();
         Comment::factory(30)->create();
    
    }
}
