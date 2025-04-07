<?php

namespace App\Http\Controllers;

use App\Imports\ProjectImport;
use App\Models\{University, College, Department, Student, User, Project, Supervisor, LibrarayStaff, Comment, Evaluate, LibraryStaff, Role, Socialmedie};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SuperAdminController extends Controller
{




    // Projects Management
    public function getAllProjects(Request $request)
    {
        $projects = Project::with(['department', 'supervisor.user'])
            ->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $projects]);
    }

    public function viewProject($id)
    {
        $project = Project::with(['department', 'supervisor.user', 'students.user'])
            ->findOrFail($id);
        return response()->json(['success' => true, 'data' => $project]);
    }

    public function importProjects(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new ProjectImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Projects imported successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing projects: ' . $e->getMessage()
            ], 500);
        }
    }

    // Dashboard Statistics
    public function getDashboardStats()
    {
        try {
            $stats = [
                'total_counts' => [
                    'projects' => Project::count(),
                    'students' => Student::count(),
                    'colleges' => College::count(),
                    'universities' => University::count(),
                    'supervisors' => Supervisor::count(),
                    'departments' => Department::count(),
                    'users' => User::count(),
                ],

                'users_by_role' => Role::withCount('users')->get()->map(function ($role) {
                    return [
                        'role' => $role->name,
                        'count' => $role->users_count
                    ];
                }),

                'projects_stats' => [
                    'total' => Project::count(),
                    'by_year' => Project::selectRaw('YEAR(projectYear) as year, COUNT(*) as count')
                        ->groupBy('year')
                        ->orderBy('year', 'desc')
                        ->get(),
                    'by_department' => Department::withCount('projects')
                        ->having('projects_count', '>', 0)
                        ->get()
                        ->map(function ($dept) {
                            return [
                                'department' => $dept->name,
                                'count' => $dept->projects_count
                            ];
                        })
                ],

                'students_stats' => [
                    'total' => Student::count(),
                    'by_department' => Department::withCount('students')
                        ->having('students_count', '>', 0)
                        ->get()
                        ->map(function ($dept) {
                            return [
                                'department' => $dept->name,
                                'count' => $dept->students_count
                            ];
                        })
                ],

                'supervisors_stats' => [
                    'total' => Supervisor::count(),
                    'by_college' => College::withCount('supervisors')
                        ->having('supervisors_count', '>', 0)
                        ->get()
                        ->map(function ($college) {
                            return [
                                'college' => $college->name,
                                'count' => $college->supervisors_count
                            ];
                        })
                ],

                'departments_stats' => [
                    'total' => Department::count(),
                    'by_college' => College::withCount('departments')
                        ->get()
                        ->map(function ($college) {
                            return [
                                'college' => $college->name,
                                'count' => $college->departments_count
                            ];
                        })
                ],

                'recent_activity' => [
                    'latest_projects' => Project::with(['supervisor.user:id,name', 'department:id,name'])
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
                        }),
                    'latest_users' => User::with('role')
                        ->latest()
                        ->take(5)
                        ->get()
                        ->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $user->role->name,
                                'created_at' => $user->created_at
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
                'message' => 'Error fetching dashboard statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
