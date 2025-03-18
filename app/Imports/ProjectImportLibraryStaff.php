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

class ProjectImportLibraryStaff implements ToCollection
{
    protected $libraryStaffCollegeId;
    protected $currentProject = null;
    protected $currentDepartment = null;
    protected $currentSupervisor = null;
    protected $processedProjects = [];
    protected $processedStudents = [];

    public function __construct($collegeId)
    {
        $this->libraryStaffCollegeId = $collegeId;
    }

    public function collection(Collection $rows)
    {
        // Get role IDs once at the start
        $supervisorRole = Role::where('slug', 'supervisor')->first();
        $studentRole = Role::where('slug', 'student')->first();

        if (!$supervisorRole || !$studentRole) {
            throw new \Exception('Required roles not found in the database');
        }

        // Skip header row
        $rows->shift();

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty($row[0]) && empty($row[3])) continue;

            // If we have a title, it's a new project
            if (!empty($row[0])) {
                $projectTitle = trim($row[0]);
                
                // Check if we've already processed this project
                if (in_array($projectTitle, $this->processedProjects)) {
                    $this->currentProject = Project::where('title', $projectTitle)
                        ->where('supervisor_id', $this->currentSupervisor->id)
                        ->first();
                    continue;
                }

                // Get department
                $departmentName = trim($row[5]); // Department name is in 6th column
                $this->currentDepartment = Department::where('name', $departmentName)
                    ->where('college_id', $this->libraryStaffCollegeId)
                    ->first();

                if (!$this->currentDepartment) {
                    throw new \Exception("Department '{$departmentName}' not found in your college");
                }

                // Create or get supervisor
                $supervisorName = trim($row[2]); // Supervisor name in 3rd column
                $supervisorEmail = Str::slug($supervisorName) . '@sygh.com';
                
                $supervisorUser = User::firstOrCreate(
                    ['email' => $supervisorEmail],
                    [
                        'name' => $supervisorName,
                        'password' => Hash::make('password'),
                        'role_id' => $supervisorRole->id
                    ]
                );

                $this->currentSupervisor = Supervisor::firstOrCreate(
                    ['user_id' => $supervisorUser->id],
                    ['college_id' => $this->libraryStaffCollegeId]
                );

                // Check if project already exists
                $this->currentProject = Project::firstOrCreate(
                    [
                        'title' => $projectTitle,
                        'supervisor_id' => $this->currentSupervisor->id
                    ],
                    [
                        'description' => $row[1],
                        'projectYear' => now()->year . '-01-01',
                        'department_id' => $this->currentDepartment->id,
                    ]
                );

                $this->processedProjects[] = $projectTitle;
            }

            // Process student if we have student data
            if (!empty($row[3]) && !empty($row[4]) && $this->currentProject) {
                $studentId = trim($row[3]);
                $studentName = trim($row[4]);
                
                // Create unique key for student
                $studentKey = $studentId . '-' . $this->currentProject->id;
                
                // Skip if we've already processed this student for this project
                if (in_array($studentKey, $this->processedStudents)) {
                    continue;
                }

                // Create student user
                $studentEmail = $studentId . '.' . Str::slug($studentName) . '@sygh.com';
                $studentUser = User::firstOrCreate(
                    ['email' => $studentEmail],
                    [
                        'name' => $studentName,
                        'password' => Hash::make('password'),
                        'role_id' => $studentRole->id
                    ]
                );

                // Check if student already exists in this project
                $existingStudent = Student::where('studentUnid', $studentId)
                    ->where('project_id', $this->currentProject->id)
                    ->first();

                if (!$existingStudent) {
                    // Create student record
                    Student::create([
                        'studentUnid' => $studentId,
                        'user_id' => $studentUser->id,
                        'department_id' => $this->currentDepartment->id,
                        'project_id' => $this->currentProject->id,
                        'isTeamLeader' => $this->isFirstStudentInProject($this->currentProject->id)
                    ]);
                }

                $this->processedStudents[] = $studentKey;
            }
        }
    }

    private function isFirstStudentInProject($projectId)
    {
        return Student::where('project_id', $projectId)->count() === 0;
    }
} 