<?php

namespace App\Http\Controllers;

use App\Models\User;


use App\Models\LibrarayStaff;

use App\Models\Project;
use App\Models\Department;
use App\Models\Supervisor;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with('evaluates')
        ->select('id', 'title', 'description', 'projectYear')
        ->where('lbraryStatus', 1)
        ->where('supervisorStatus', 1)
        ->get()
        ->map(function ($project) {
            $averageRating = $project->evaluates->avg('rating'); // Calculate the average rating using Eloquent

            return [
                'title' => $project->title,
                'description' => $project->description,
                'projectYear' => $project->projectYear,
                'average_rating' => round($averageRating, 2) ?? 0, // Handle cases where no ratings exist
            ];
        });

    return response()->json($projects);

    }

    public function DepartmentProjects(int $id){

        $Projects=Project::where('department_id',$id);
        return response()->json($Projects);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }
//    public function GetDepartmentIdAndSupervisorId(int $id){
//
//        $userCollegeId = $id;
//        $department = Department::whereRelation('college', 'id', $userCollegeId)->get();
//        $supervisorData =Supervisor::where('college_id', $userCollegeId)
//                      ->with(['user:id,name'])
//                      ->get();
//                      $supervisors = $supervisorData->map(function ($supervisor) {
//                        return [
//                            'supervisor_id' => $supervisor->id,
//                            'name' => $supervisor->user->name,
//
//                        ];
//                    });
//    return response()->json([
//        'success' => true,
//        'department'=>$department,
//        'supervisor'=>$supervisors,
//    ], 200);
//
//    }

    public function departmentId(): int
    {

        $libraraystaff = LibrarayStaff::with(['college:id', 'college.department:id,name,college_id'])
            ->where('user_id', Auth::id())
            ->first();
        if (!$libraraystaff ||
            !$libraraystaff->college){
            throw new \Exception('لايوجد مشاريع ');
        }
        return $libraraystaff->college->department->first()->id;
    }


    public function getAllProjects(Request $request)
    {
        try {

            $departmentId = $this->departmentId();
            $projects = Project::with(['department', 'supervisor'])
                ->where('department_id', $departmentId)
                ->get();
            $formattedProjects = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => $project->description,
                    'videoUrl' => $project->videoUrl,
                    'projectYear' => $project->projectYear,
                    'department_name' => $project->department->name ?? null,
                    'supervisor_name' => $project->supervisor->user->name ?? null,
                ];
            });


            return response()->json([
                'success' => true,
                'data' => $formattedProjects
            ], 200);

//            return response()->json(['success' => true, 'data' => $projects], 200);

        }   catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */



public function uploadeproject(Request $request)
{
    $request->validate([
        'title' => 'required|string', // for lookup only
        'description' => 'required|string',
        'videoUrl' => 'nullable|url',
        'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
    ]);

    // Search for the project by its title
    $project = Project::where('title', $request->title)->first();

    if (!$project) {
        return response()->json([
            'success' => false,
            'message' => 'Project not found with the given title.',
        ], 404);
    }

    // Update fields
    $project->description = $request->input('description');
    $project->videoUrl = $request->input('videoUrl');

    // Upload new document if provided
   if ($request->hasFile('document')) {
    $file = $request->file('document');
    $originalName = $file->getClientOriginalName(); // ✅ THIS is what you're missing
    $path = $file->store('projects/documents', 'public');

    $project->document = Storage::url($path);
    // $project->document = $originalName; // ✅ You must assign this
}

    $project->save();

    return response()->json([
        'success' => true,
        'message' => 'Project updated successfully.',
        'project' => $project
    ]);
}


public function store(Request $request){

    try{
            $validator=Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'videoUrl' => 'nullable|url',
                'projectYear' => 'required|date',
                'department_id' => 'required',
                'supervisor_id'=>'required',
            ]);

            $validatedData = $validator->validate();

            $Project = Project::create($validatedData);

            return response()->json(['success' => true,'message'=>'تمت الاضافة بنجاح ', 'data' => $Project], 201);

        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $e->getMessage()
            ],500);
        }
        }
    /**
     * Display the specified resource.
     */
     public function show(int  $id)
    {

        //supervisorStatus
        $project = Project::where('id', $id)
                            ->where('supervisorStatus', true)
                            ->where('lbraryStatus', true)
                            ->first();
           if (!$project)
           {
               return response()->json(['message' => 'Project not found'], 404);
           }
           $projectDetails = $project->getProjectDetails();
           return response()->json($projectDetails);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'videoUrl' => 'nullable|url',
                'projectYear' => 'nullable|date',
                'department_id' => 'nullable',
                'supervisor_id' => 'nullable',
            ]);

            $validatedData = $validator->validate();

            $project = Project::findOrFail($id);

            $project->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'تم التحديث بنجاح',
                'data' => $project
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة',
                'error' => $exception->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $project = Project::find($id);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشروع غير موجود',
                ]);
            }
            $department=Department::find($project->department_id);

            $libraryStaff = LibrarayStaff::where('college_id', $department->college_id)
                ->where('user_id', Auth::id())
                ->get();

            if ($libraryStaff->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك',
                ]);
            }

            if ($project->students->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايمكن حذف هذه المشروع '
                ], 400);
            }

            $project->delete();
            return response()->json([
                'success' => true,
                'message' => 'تم الحذف بنجاح',
            ], 200);

        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف ',
                'error' => $exception->getMessage()
            ],500);
        }


    }
}
