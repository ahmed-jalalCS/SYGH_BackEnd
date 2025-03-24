<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::select('id', 'name', 'college_id')
            ->with([
                'college:id,name,universitie_id',
                'college.university:id,name',
            ])
            ->get();
        if ($departments->isEmpty()) {
            return response()->json(['message' => 'لايوجد اقسام']);
        }

        return response()->json(['success' => true, 'data' => $departments], 200);


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
    public function store(Request $request,int $collegeId)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $validatedData['college_id'] = $collegeId;
        $department = Department::create($validatedData);
        return response()->json(['success' => true, 'message' => 'تمت الإضافة بنجاح'], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {

        $departmentProject = Project::select('id','title','description')
            ->where('department_id', $id)
            ->where('supervisorStatus', true)
            ->where('lbraryStatus', true)
            ->with(['document' => fn($query) => $query->select('id', 'pathDo','project_id')])
            ->get();
        if ($departmentProject->isEmpty())
        {
            return response()->json(['message' => 'لايوجد مشاريع لهذا القسم '], 404);
        }
        return response()->json(['success' => true, 'data' => $departmentProject], 200);
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
    public function update(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);
        $department = Department::findOrFail($id);
        $department->update($validatedData);

        return response()->json(['success' => true, 'message' => 'تم التعديل بنجاح '], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح '], 200);
    }

}
