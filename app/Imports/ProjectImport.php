<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProjectImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Get role IDs once at the start
        $supervisorRole = Role::where('slug', 'supervisor')->first();
        $studentRole = Role::where('slug', 'student')->first();

        if (!$supervisorRole || !$studentRole) {
            throw new \Exception('Required roles not found in the database');
        }

        $currentProject = null;
        $currentSupervisor = null;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            // Process new project and supervisor when Title and Supervisor are present
            if (!empty($row[2]) && !empty($row[4])) {
                // Create supervisor user first
                $supervisorUser = User::firstOrCreate(
                    ['email' => Str::slug($row[4]) . '@sygh.com'],
                    [
                        'name' => $row[4],
                        'password' => Hash::make('password'),
                        'role_id' => $supervisorRole->id  // Set the supervisor role_id
                    ]
                );

                // Create or find supervisor
                $currentSupervisor = Supervisor::firstOrCreate(
                    ['user_id' => $supervisorUser->id],
                    ['college_id' => $row[1] ?? 1]
                );

                // Get or create department
                $department = Department::firstOrCreate(
                    ['name' => $row[7]],
                    ['college_id' => $row[1]]
                );

                // Create project
                $currentProject = Project::create([
                    'title' => $row[2],
                    'description' => $row[3],
                    'projectYear' => now()->year . '-01-01',
                    'department_id' => $department->id,
                    'supervisor_id' => $currentSupervisor->id,
                ]);
            }

            // Process student if we have a student ID and name
            if (!empty($row[5]) && !empty($row[6]) && $currentProject) {
                // Create student user
                $studentEmail = $row[5] . '.' . Str::slug($row[6]) . '@sygh.com';
                $studentUser = User::firstOrCreate(
                    ['email' => $studentEmail],
                    [
                        'name' => $row[6],
                        'password' => Hash::make('password'),
                        'role_id' => $studentRole->id  // Set the student role_id
                    ]
                );

                // Get student's department
                $studentDepartment = Department::firstOrCreate(
                    ['name' => $row[7]],
                    ['college_id' => $row[1] ?? 1]
                );

                // Handle numeric and decimal student IDs
                $studentId = str_replace('.', '', $row[5]); // Remove decimal points
                
                // Create student record
                Student::create([
                    'studentUnid' => $studentId,
                    'user_id' => $studentUser->id,
                    'department_id' => $studentDepartment->id,
                    'project_id' => $currentProject->id,
                    'isTeamLeader' => empty($currentProject->students()->count()),
                ]);
            }
        }
    }
}
