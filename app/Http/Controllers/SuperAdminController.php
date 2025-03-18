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
    // View Only Methods
    public function getAllUsers(Request $request)
    {
        $query = User::with('role');

        // Filter by role if provided
        if ($request->has('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function getAllStudents(Request $request)
    {
        $students = User::where('role_id', Role::where('slug', 'student')->first()->id)
            ->with(['student.department'])
            ->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $students]);
    }

    // Universities Management
    public function getAllUniversities(Request $request)
    {
        $universities = University::with('colleges')
            ->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $universities]);
    }

    public function createUniversity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|string',
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $university = University::create($request->only(['name', 'address', 'image']));
        return response()->json(['success' => true, 'data' => $university], 201);
    }

    public function viewUniversity($id)
    {
        $university = University::with(['colleges.departments'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $university]);
    }

    public function updateUniversity(Request $request, $id)
    {
        $university = University::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'address' => 'sometimes|string',
            'image' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $university->update($request->only(['name', 'address', 'image']));
        return response()->json(['success' => true, 'data' => $university]);
    }

    public function deleteUniversity($id)
    {
        $university = University::findOrFail($id);
        
        if ($university->colleges()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete university with existing colleges'
            ], 400);
        }

        $university->delete();
        return response()->json(['success' => true, 'message' => 'University deleted successfully']);
    }

    // Colleges Management
    public function getAllColleges(Request $request)
    {
        $colleges = College::with(['university', 'departments'])
            ->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $colleges]);
    }

    public function createCollege(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'universitie_id' => 'required|exists:universities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $college = College::create($request->only(['name', 'universitie_id']));
        return response()->json(['success' => true, 'data' => $college->load('university')], 201);
    }

    public function viewCollege($id)
    {
        $college = College::with(['university', 'departments', 'libraryStaffs.user:id,name'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $college]);
    }

    public function updateCollege(Request $request, $id)
    {
        $college = College::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'universitie_id' => 'sometimes|exists:universities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $college->update($request->only(['name', 'universitie_id']));
        return response()->json(['success' => true, 'data' => $college->load('university')]);
    }

    public function deleteCollege($id)
    {
        $college = College::findOrFail($id);
        
        if ($college->departments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete college with existing departments'
            ], 400);
        }

        $college->delete();
        return response()->json(['success' => true, 'message' => 'College deleted successfully']);
    }

    // Library Staff Management
    public function getAllLibraryStaff(Request $request)
    {
        $staff = LibraryStaff::with(['user:id,name', 'college.university:id,name'])
            ->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $staff]);
    }

    public function createLibraryStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'college_id' => 'required|exists:colleges,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            // Check if a library staff already exists for this college
            $existingStaff = LibraryStaff::where('college_id', $request->college_id)->first();
            if ($existingStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'A library staff already exists for this college'
                ], 400);
            }

            // Create user with library staff role
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => Role::where('slug', 'library_staff')->firstOrFail()->id
            ]);

            // Create library staff record
            $libraryStaff = LibraryStaff::create([
                'user_id' => $user->id,
                'college_id' => $request->college_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $libraryStaff->load(['user', 'college'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating library staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewLibraryStaff($id)
    {
        $staff = LibraryStaff::with(['user:id,name', 'college.university:id,name'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $staff]);
    }

    public function updateLibraryStaff(Request $request, $id)
    {
        $staff = LibraryStaff::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $staff->user_id,
            'college_id' => 'sometimes|exists:colleges,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            if ($request->has('college_id') && $request->college_id !== $staff->college_id) {
                $existingStaff = LibraryStaff::where('college_id', $request->college_id)->first();
                if ($existingStaff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A library staff already exists for this college'
                    ], 400);
                }
            }

            // Update user
            if ($request->has('name') || $request->has('email')) {
                $staff->user->update($request->only(['name', 'email']));
            }

            // Update library staff
            if ($request->has('college_id')) {
                $staff->update(['college_id' => $request->college_id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $staff->load(['user', 'college'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating library staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteLibraryStaff($id)
    {
        try {
            DB::beginTransaction();

            $staff = LibraryStaff::findOrFail($id);
            $user = $staff->user;

            $staff->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Library staff deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting library staff: ' . $e->getMessage()
            ], 500);
        }
    }

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
