<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Imports\ProjectImportLibraryStaff;
use App\Models\Department;
use App\Models\LibrarayStaff;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\LibraryStaff;
use App\Models\Role;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LibraryStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // // get all the projects (for testing)
    // public function getProjects()
    // {
    //     $projects = Project::all();

    //     return response()->json([
    //         "Projects:" => $projects,
    //     ]);

    //     // // Logic to return projects
    //     // return response()->json(['projects' => []]);
    // }

    /**
     * Get dashboard statistics for the library staff's college
     */
    public function getDashboardStats()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $libraryStaff = LibraryStaff::where('user_id', $user->id)->first();
            if (!$libraryStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Library staff record not found'
                ], 404);
            }

            $collegeId = $libraryStaff->college_id;

            $stats = [
                'projects' => [
                    'total' => Project::whereHas('department', function ($query) use ($collegeId) {
                        $query->where('college_id', $collegeId);
                    })->count(),

                    'by_department' => Department::where('college_id', $collegeId)
                        ->withCount('projects')
                        ->having('projects_count', '>', 0)
                        ->get()
                        ->map(function ($dept) {
                            return [
                                'department' => $dept->name,
                                'count' => $dept->projects_count
                            ];
                        })
                ],

                'students' => [
                    'total' => Student::whereHas('department', function ($query) use ($collegeId) {
                        $query->where('college_id', $collegeId);
                    })->count(),

                    'by_department' => Department::where('college_id', $collegeId)
                        ->withCount('students')
                        ->having('students_count', '>', 0)
                        ->get()
                        ->map(function ($dept) {
                            return [
                                'department' => $dept->name,
                                'count' => $dept->students_count
                            ];
                        })
                ],

                'supervisors' => [
                    'total' => Supervisor::where('college_id', $collegeId)->count(),
                    'by_department' => Department::where('college_id', $collegeId)
                        ->withCount(['projects as supervisors_count' => function ($query) {
                            $query->select(DB::raw('COUNT(DISTINCT supervisor_id)'));
                        }])
                        ->having('supervisors_count', '>', 0)
                        ->get()
                        ->map(function ($dept) {
                            return [
                                'department' => $dept->name,
                                'count' => $dept->supervisors_count
                            ];
                        })
                ],

                'departments' => [
                    'total' => Department::where('college_id', $collegeId)->count(),
                    'departments' => Department::where('college_id', $collegeId)
                        ->withCount('projects')
                        ->get()
                        ->map(function ($dept) {
                            return [
                                'department' => $dept->name,
                                'count' => $dept->projects_count
                            ];
                        })
                ],

                'recent_activity' => [
                    'latest_projects' => Project::whereHas('department', function ($query) use ($collegeId) {
                        $query->where('college_id', $collegeId);
                    })
                        ->with(['supervisor.user:id,name', 'department:id,name'])
                        ->latest()
                        ->take(5)
                        ->get()
                        ->map(function ($project) {
                            return [
                                'id' => $project->id,
                                'title' => $project->title,
                                'supervisor' => $project->supervisor->user->name,
                                'department' => $project->department->name,
                                'created_at' => $project->created_at
                            ];
                        })
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import projects from Excel file
     */
    public function importProjects(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $libraryStaff = LibraryStaff::where('user_id', $user->id)->first();
        if (!$libraryStaff) {
            return response()->json([
                'success' => false,
                'message' => 'Library staff record not found'
            ], 404);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ProjectImportLibraryStaff($libraryStaff->college_id), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Projects imported successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing projects: ' . $e->getMessage()
            ], 500);
        }
    }


    // Student Management
    /**
     * Get all students in the library staff's college
     */
    public function getStudents()
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $students = Student::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->with(['user', 'department', 'project'])->get();

            return response()->json(['success' => true, 'data' => $students]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new student
     */
    public function createStudent(Request $request)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'studentUnid' => 'required|string|unique:students,studentUnid',
                'department_id' => 'required|exists:departments,id',
                'project_id' => 'nullable|exists:projects,id'
            ]);

            // Verify department belongs to library staff's college
            $department = Department::where('id', $request->department_id)
                ->where('college_id', $libraryStaff->college_id)
                ->first();

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department does not belong to your college'
                ], 403);
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password'),
                'role_id' => Role::where('slug', 'student')->first()->id
            ]);

            // Create student
            $student = Student::create([
                'studentUnid' => $request->studentUnid,
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'project_id' => $request->project_id,
                'isTeamLeader' => $request->project_id ? false : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => $student->load(['user', 'department', 'project'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a student
     */
    public function updateStudent(Request $request, $id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $student = Student::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->find($id);

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $student->user_id,
                'studentUnid' => 'sometimes|string|unique:students,studentUnid,' . $student->id,
                'department_id' => 'sometimes|exists:departments,id',
                'project_id' => 'nullable|exists:projects,id'
            ]);

            if ($request->has('department_id')) {
                $department = Department::where('id', $request->department_id)
                    ->where('college_id', $libraryStaff->college_id)
                    ->first();

                if (!$department) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department does not belong to your college'
                    ], 403);
                }
            }

            // Update user
            if ($request->has('name') || $request->has('email')) {
                $student->user->update($request->only(['name', 'email']));
            }

            // Update student
            $student->update($request->only(['studentUnid', 'department_id', 'project_id']));

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $student->load(['user', 'department', 'project'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a student
     */
    public function deleteStudent($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $student = Student::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->find($id);

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            // Delete user and student (assuming cascade delete is set up)
            $student->user->delete();
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    // Project Management

    /**
     * Get all projects in the library staff's college
     */
    public function getProjects()
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $projects = Project::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->with(['supervisor.user', 'department', 'students.user'])->get();

            return response()->json(['success' => true, 'data' => $projects]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new project
     */
    public function createProject(Request $request)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'department_id' => 'required|exists:departments,id',
                'supervisor_id' => 'required|exists:supervisors,id',
                'projectYear' => 'required|date_format:Y-m-d'
            ]);

            // Verify department belongs to library staff's college
            $department = Department::where('id', $request->department_id)
                ->where('college_id', $libraryStaff->college_id)
                ->first();

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department does not belong to your college'
                ], 403);
            }

            // Verify supervisor belongs to library staff's college
            $supervisor = Supervisor::where('id', $request->supervisor_id)
                ->where('college_id', $libraryStaff->college_id)
                ->first();

            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supervisor does not belong to your college'
                ], 403);
            }

            $project = Project::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project->load(['supervisor.user', 'department'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * View a specific project
     */
    public function viewProject($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $project = Project::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->with(['supervisor.user', 'department', 'students.user'])
            ->find($id);

            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $project]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a project
     */
    public function updateProject(Request $request, $id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $project = Project::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->find($id);

            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            $request->validate([
                'title' => 'sometimes|string',
                'description' => 'sometimes|string',
                'department_id' => 'sometimes|exists:departments,id',
                'supervisor_id' => 'sometimes|exists:supervisors,id',
                'projectYear' => 'sometimes|date_format:Y-m-d'
            ]);

            if ($request->has('department_id')) {
                $department = Department::where('id', $request->department_id)
                    ->where('college_id', $libraryStaff->college_id)
                    ->first();

                if (!$department) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department does not belong to your college'
                    ], 403);
                }
            }

            if ($request->has('supervisor_id')) {
                $supervisor = Supervisor::where('id', $request->supervisor_id)
                    ->where('college_id', $libraryStaff->college_id)
                    ->first();

                if (!$supervisor) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Supervisor does not belong to your college'
                    ], 403);
                }
            }

            $project->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project->load(['supervisor.user', 'department', 'students.user'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a project
     */
    public function deleteProject($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $project = Project::whereHas('department', function ($query) use ($libraryStaff) {
                $query->where('college_id', $libraryStaff->college_id);
            })->find($id);

            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    // Supervisor Management
    /**
     * Get all supervisors in the library staff's college
     */
    public function getSupervisors()
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $supervisors = Supervisor::where('college_id', $libraryStaff->college_id)
                ->with(['user', 'projects'])
                ->get();

            return response()->json(['success' => true, 'data' => $supervisors]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new supervisor
     */
    public function createSupervisor(Request $request)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'supervisorDgree' => 'nullable|string'
            ]);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password'), // Default password
                'role_id' => Role::where('slug', 'supervisor')->first()->id
            ]);

            // Create supervisor
            $supervisor = Supervisor::create([
                'user_id' => $user->id,
                'college_id' => $libraryStaff->college_id,
                'supervisorDgree' => $request->supervisorDgree
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supervisor created successfully',
                'data' => $supervisor->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * View a specific supervisor
     */
    public function viewSupervisor($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $supervisor = Supervisor::where('college_id', $libraryStaff->college_id)
                ->with(['user', 'projects'])
                ->find($id);

            if (!$supervisor) {
                return response()->json(['success' => false, 'message' => 'Supervisor not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $supervisor]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a supervisor
     */
    public function updateSupervisor(Request $request, $id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $supervisor = Supervisor::where('college_id', $libraryStaff->college_id)->find($id);
            if (!$supervisor) {
                return response()->json(['success' => false, 'message' => 'Supervisor not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $supervisor->user_id,
                'supervisorDgree' => 'nullable|string'
            ]);

            // Update user
            if ($request->has('name') || $request->has('email')) {
                $supervisor->user->update($request->only(['name', 'email']));
            }

            // Update supervisor
            $supervisor->update($request->only(['supervisorDgree']));

            return response()->json([
                'success' => true,
                'message' => 'Supervisor updated successfully',
                'data' => $supervisor->load(['user', 'projects'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a supervisor
     */
    public function deleteSupervisor($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $supervisor = Supervisor::where('college_id', $libraryStaff->college_id)->find($id);
            if (!$supervisor) {
                return response()->json(['success' => false, 'message' => 'Supervisor not found'], 404);
            }

            // Check if supervisor has any active projects
            if ($supervisor->projects()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete supervisor with active projects'
                ], 400);
            }

            // Delete user and supervisor (assuming cascade delete is set up)
            $supervisor->user->delete();
            $supervisor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Supervisor deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }




    // Department Management

    /**
     * Get all departments in the library staff's college
     */
    public function getDepartments()
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $departments = Department::where('college_id', $libraryStaff->college_id)
                ->with(['projects', 'students'])
                ->get();

            return response()->json(['success' => true, 'data' => $departments]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new department
     */
    public function createDepartment(Request $request)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $request->validate([
                'name' => 'required|string'
            ]);

            $department = Department::create([
                'name' => $request->name,
                'college_id' => $libraryStaff->college_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'data' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * View a specific department
     */
    public function viewDepartment($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $department = Department::where('college_id', $libraryStaff->college_id)
                ->with(['projects', 'students.user:id,name,email'])
                ->find($id);

            if (!$department) {
                return response()->json(['success' => false, 'message' => 'Department not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $department]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a department
     */
    public function updateDepartment(Request $request, $id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $department = Department::where('college_id', $libraryStaff->college_id)
                ->find($id);

            if (!$department) {
                return response()->json(['success' => false, 'message' => 'Department not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|string',
                'description' => 'nullable|string'
            ]);

            $department->update($request->only(['name', 'description']));

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
                'data' => $department->load(['projects', 'students'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a department
     */
    public function deleteDepartment($id)
    {
        try {
            $libraryStaff = LibraryStaff::where('user_id', Auth::id())->first();
            if (!$libraryStaff) {
                return response()->json(['success' => false, 'message' => 'Library staff not found'], 404);
            }

            $department = Department::where('college_id', $libraryStaff->college_id)
                ->find($id);

            if (!$department) {
                return response()->json(['success' => false, 'message' => 'Department not found'], 404);
            }

            // Check if department has any students or projects
            if ($department->students()->count() > 0 || $department->projects()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department with active students or projects'
                ], 400);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
