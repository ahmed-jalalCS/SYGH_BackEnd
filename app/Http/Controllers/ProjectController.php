<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Department;
use App\Models\Supervisor;
use Illuminate\Http\Request;

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
    public function GetDepartmentIdAndSupervisorId(int $id){

        $userCollegeId = $id;
        $department = Department::whereRelation('college', 'id', $userCollegeId)->get();
        $supervisorData =Supervisor::where('college_id', $userCollegeId)
                      ->with(['user:id,name'])
                      ->get();
                      $supervisors = $supervisorData->map(function ($supervisor) {
                        return [
                            'supervisor_id' => $supervisor->id,
                            'name' => $supervisor->user->name,

                        ];
                    });
    return response()->json([
        'success' => true,
        'department'=>$department,
        'supervisor'=>$supervisors,
    ], 200);

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData=$request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'videoUrl' => 'nullable|url',
            'projectYear' => 'required',
            'department_id' => 'required',
            'supervisor_id'=>'required',
        ]);


        $datainsert=Project::create($validatedData);
        return response()->json([
            'success'=>true,
            'message'=>'تمت الاضافة بنجاح ',
        ]);

        }

    /**
     * Display the specified resource.
     */
    public function show(int  $id)
    {

           $project = Project::find($id);
           if (!$project) {
               return response()->json(['message' => 'Project not found'], 404);
           }

           $projectDetails = $project->getProjectDetails();

           return response()->json($projectDetails);


        //  project 'title','description','videoUrl',and the doucment pathDo of the this project from the document table
        // also the  suopervisor name  of the project and the students name , emial and linkes   that belong to this project
        // also the comment of this project with the name of the user that post this comment


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
        // Validate incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'videoUrl' => 'nullable|url',
            'projectYear' => 'required',
            'department_id' => 'required',
            'supervisor_id' => 'required',
        ]);
    
        // Find project by ID
        $project = Project::find($id);
    
        // Check if project exists
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'المشروع غير موجود',
            ], 404);
        }
    
        // Update project
        $project->update($validatedData);
    
        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح',
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
