<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\Department;
use App\Models\Role;


class AdminController extends Controller
{

    public function index()
    {
        $allAdmin = User::with('university:id,name,user_id')
            ->where('role_id', 2)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'university_name' => $user->university->name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $allAdmin
        ], 200);


    }

    public function getAdminDashboardStats()
    {
        try {
            $university = University::where('user_id', Auth::id())->first();

            if (!$university) {
                return response()->json([
                    'success' => false,
                    'message' => 'University not found for this user.'
                ], 404);
            }

            $stats = [
                'total_counts' => [
                    'projects' => Project::whereHas('students.department.college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                    'students' => Student::whereHas('department.college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                    'colleges' => College::where('universitie_id', $university->id)->count(),

                    'departments' => Department::whereHas('college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                    'supervisors' => Supervisor::whereHas('college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                ],

                'projects_stats' => [
                    'total' => Project::whereHas('students.department.college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                    'by_year' => Project::whereHas('students.department.college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })
                        ->selectRaw('YEAR(projectYear) as year, COUNT(*) as count')
                        ->groupBy('year')
                        ->orderBy('year', 'desc')
                        ->get(),
                    'by_department' => Department::whereHas('college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })
                        ->withCount(['projects' => function ($q) use ($university) {
                            $q->whereHas('students.department.college.university', function ($sub) use ($university) {
                                $sub->where('id', $university->id);
                            });
                        }])
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
                    'total' => Student::whereHas('department.college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),
                    'by_department' => Department::whereHas('college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })
                        ->withCount(['students' => function ($q) use ($university) {
                            $q->whereHas('department.college.university', function ($sub) use ($university) {
                                $sub->where('id', $university->id);
                            });
                        }])
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
                    'total' => Supervisor::whereHas('college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                    'by_college' => College::where('universitie_id', $university->id)
                        ->withCount('supervisors')
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
                    'total' => Department::whereHas('college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })->count(),

                    'by_college' => College::where('universitie_id', $university->id)
                        ->withCount('department')
                        ->get()
                        ->map(function ($college) {
                            return [
                                'college' => $college->name,
                                'count' => $college->departments_count
                            ];
                        })
                ],

                'recent_activity' => [
                    'latest_projects' => Project::whereHas('students.department.college.university', function ($q) use ($university) {
                        $q->where('id', $university->id);
                    })
                        ->with(['supervisor.user:id,name', 'department:id,name'])
                        ->latest()
                        ->take(5)
                        ->get()
                        ->map(function ($project) {
                            return [
                                'id' => $project->id,
                                'title' => $project->title,
                                'supervisor' => $project->supervisor->user->name ?? 'N/A',
                                'department' => $project->department->name ?? 'N/A',
                                'created_at' => $project->created_at
                            ];
                        }),
              ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching admin dashboard statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request,int $id)
    {
        try {

            $university=University::findOrFail($id);
            if ($university->user_id !=null)
            {
                return response()->json([
                    'success'=>true,
                    'message'=>'هذة الجامعة لديها مسؤل بالفعل'
                ],200);
            }
            $validator= Validator::make($request->all(),[
                'name'=> 'required',
                'email'=>'required|email',
                'password'=>'required|min:10',
            ]);
            if($validator->fails()){
                return response()->json(['success'=>false,'errors'=>$validator->errors()]);
            }

            $ValidateData=$validator->validated();
            $ValidateData['role_id'] = 2;
//            $ValidateData['plain_password']=$ValidateData['password'] ?? '';
            $ValidateData['password'] = Hash::make($ValidateData['password']);
            $user = User::create($ValidateData);
            $university->user_id = $user->id;
            $university->save();
            return response()->json([
                'success'=>true,
                'message'=>'تم الاظافه بنجاح',
                'data'=>$user
            ],200);
        }catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "لايوجد جامعة"
            ], 404);
        }
    }


    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, int $id)
    {
        try {


            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'  => 'sometimes|required|string',
                'password' => 'nullable|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

//            $ValidateData['plain_password'] =$validatedData['password'] ?? '';
            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'تم التحديث بنجاح',
                'data'    => $user
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'الجامعة أو المستخدم غير موجود',
            ], 404);
        }
    }

    public function destroy($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $university = University::where('user_id', $userId)->first();

            if ($university) {
                $university->user_id = null;
                $university->save();
            }
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المستخدم وتحديث الجامعة بنجاح',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود',
            ], 404);
        }
    }

    public function getAllColleges(Request $request)
    {
        $colleges = College::where('university_id', function ($query) {
            $query->select('id')
                ->from('universities')
                ->where('user_id', Auth::id())
                ->limit(1);
        })->get();
        if ($colleges->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Colleges not found'
            ]);
        }
        return response()->json(['success' => true, 'data' => $colleges]);

    }
}
