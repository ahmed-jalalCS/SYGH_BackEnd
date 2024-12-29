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
    public function index(int $id)
    {
             
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
        'data'=>$datainsert,
    ]);
    
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
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
}
