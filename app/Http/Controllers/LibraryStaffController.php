<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Imports\ProjectImportLibraryStaff;
use App\Models\Department;
use App\Models\LibrarayStaff;
use Illuminate\Support\Facades\Validator;
use League\Csv\Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\isEmpty;


class LibraryStaffController extends Controller
{

    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح بك'
                ], 401);
            }
            $libraryStaff = LibrarayStaff::where('user_id', $user->id)->first();
            if (!$libraryStaff) {
                return response()->json([
                    'success' => false,
                    'message'=>'غير موجود هذا الموظف',
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

    public function departmentId(): array
    {
        $libraraystaff = LibrarayStaff::with(['college:id', 'college.department:id,name,college_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$libraraystaff || !$libraraystaff->college) {
            throw new \Exception('لايوجد مشاريع ');
        }

        $departmentIds = $libraraystaff->college->department->pluck('id')->toArray();

        return $departmentIds;
    }


    public function getAllStudents(Request $request)
    {


        try {

            $students=Student::with('user')->whereIn('department_id', $this->departmentId())->get();

            $formattedProjects = $students->map(function ($students) {
                return [
                    'id' => $students->id,
                    'graduation_year'=>$students->graduation_year,
                    'isTemLeder'=>$students->isTemLeder,
                    'name' => $students->user->name,
                    'email' => $students->user->email,
                    'plain_password' => $students->user->plain_password ?? null
                ];
            });
            return response()->json([
                'success' => true,
                'data' => $formattedProjects
            ], 200);
        }   catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);
        }


    }

    public  function getAllSupervisors(Request $request)
    {
        $librarystaff = LibrarayStaff::where('user_id', Auth::id())->value('college_id');
        $supervisor=Supervisor::with('user:id,email,plain_password')->where('college_id',$librarystaff)->get();
        return response()->json([
            'success' => true,
            'data' => $supervisor
        ]);
    }

    public function getAllProjectsstatus(Request $request)
    {

        try {
            $projects = Project::with(['document','students.user:id,name'])
                ->where('lbraryStatus', 0)
                ->whereIn('department_id', $this->departmentId())
                ->get();
            $formattedProjects = $projects->map(function ($project) {
                $leader = $project->students->firstWhere('isTemLeder', 1);
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => $project->description,
                    'projectYear' => $project->projectYear,
                    'pathDo' => optional($project->document)->pathDo, // ✅ حماية من n
                    'student_name' => optional($leader->user ?? null)->name,
                ];
            });
            return response()->json([
                'success' => true,
                'data' => $formattedProjects
            ], 200);
        }   catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);
        }


    }

    public function ActiveProject(Request $request,$id)
    {

        $project = Project::findOrFail($id); // Get the actual Project model
        $project->update(['lbraryStatus' => 1]); // Update its status
        $project->students()->update(['isTemLeder' => 0]); // Use relationship method (with parentheses) to update related student
        if ($project)
        {
            return response()->json([

                'success' => true,
                'message'=>'تم التفعيل بنجاح'
            ]);
        }

        else
        {
            return response()->json([
                'success' => false,
                'meesage'=>'حدث خطاء اثناء التفعيل '
            ]);
        }


    }
    public function create()
    {

    }

// The admin functionality
    public function store(Request $request,int $id)
    {
        try {
            $college=College::find($id);
            if (!$college) {return response()->json(['success' => false, 'message'=>'لايوجد كلية']);}

            if(Auth::user()->id!==$college->university->user_id){return response()->json(['error' => 'غير مصرح لفعل هذه العملية'], 403);}
            $libraryStaff = LibrarayStaff::where('college_id', $college->id)
                                         ->value('id');
            if ($libraryStaff) {
                return response()->json([
                    'success' => true,
                    'message' => 'يوجد بالفعل مسؤول مكتبة'
                ], 200);
            }

            $validator= Validator::make($request->all(),[
                'name'=> 'required',
                'email'=>'required|email|unique:users,email',
                'password'=>'required|min:10',
            ]);
            if($validator->fails()){
                return response()->json(['success'=>false,'errors'=>$validator->errors()]);
            }

            $ValidateData=$validator->validated();
            $ValidateData['role_id'] = Role::where('name','Library Staff')->value('id');
            $ValidateData['password'] = Hash::make($ValidateData['password']);
            $user = User::create($ValidateData);
            $addlibraraystaff=LibrarayStaff::create([
                'user_id'=>$user->id,
                'college_id'=>$college->id,
                'isSuperAdmin'=>1
            ]);
            return response()->json([
             'success'=>true,
             'message' =>'تم انشاء مسوؤل مكتبة',
                'data'=>$addlibraraystaff
            ],200);
        }catch (\Exception $e){

            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء الاضافة ', 'error'=>$e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $libraryStaff = LibrarayStaff::find($id);
            if (!$libraryStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد مسؤول مكتبة'
                ]);
            }
            $user = $libraryStaff->user_id;
            $libraryStaff->delete();
            User::where('id', $user)->delete();
            return response()->json([
                'success' => true,
                'message' => 'تم حذف مسؤول المكتبة بنجاح'
            ]);
        }catch (\Exception $e){
            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء الحذف ', 'error'=>$e->getMessage()], 500);
        }
    }
    // The end admin functionality
    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
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


    /**
     * Get dashboard statistics for the library staff's college
     */

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

}
