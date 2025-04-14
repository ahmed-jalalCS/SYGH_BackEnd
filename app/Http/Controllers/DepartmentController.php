<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Department;
use App\Models\LibrarayStaff;
use App\Models\Project;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\LibraryStaff;

class DepartmentController extends Controller
{

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
    public function show(int $id)
    {
         $projects = Project::with('evaluates','department')
         ->where('department_id',$id)
        ->where('lbraryStatus', 1)
        ->where('supervisorStatus', 1)
        ->get()
        ->map(function ($project) {
            $averageRating = $project->evaluates->avg('rating'); // Calculate the average rating using Eloquent

            return [
                'id'=>$project->id,
                'title' => $project->title,
                'description' => $project->description,
                'projectYear' => $project->projectYear,
                'average_rating' => round($averageRating, 2) ?? 0, // Handle cases where no ratings exist
                'department_name'=>$project->department->name
            ];
        });

    return response()->json($projects);
    }

    public function create()
    {
        //
    }


// the library staff functionality

    public function getAllDepartments(Request $request)
    {
        try {
            $department= LibrarayStaff::with([
                'college:id',
                'college.department:id,name,college_id'
            ])
                ->where('user_id', Auth::id())
                ->get(['user_id', 'college_id']);

            return response()->json(['success' => true, 'data' => $department], 200);

        }   catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);
        }


        
    }
    public function store(Request $request)
    {
        try {
            $libraraystaff = LibrarayStaff::where('user_id', Auth::id())->value('college_id');
            $validator= Validator::make($request->all(),[
                'name'=> 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }
            $validatedData = $validator->validated();
            $validatedData['college_id'] = $libraraystaff;
            $department = Department::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'تمت الاضافة بنجاح ',
                '$collegeId' => $department,
            ], 200);
        } catch (\Exception $exception) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);

        }
    }






    public function edit(string $id)
    {
        //
    }
    public function update(Request $request, int $id)
    {
        try {
            $department = Department::find($id);
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'القسم غير موجود',
                ]);
            }
            $collegedata = College::with('libraryStaffs')->find($department->college_id);
            $libraryStaff = LibrarayStaff::where('college_id', $collegedata->id)
                ->where('user_id', Auth::id())
                ->get();
            if ($libraryStaff->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك',
                ]);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ]);
            }
            $validatedData = $validator->validated();
            $department->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'تم التحديث بنجاح',
                'department' => $department,
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة ',
                'error' => $exception->getMessage()
            ],500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy(int $id)
    {
        try {
            $department = Department::find($id);
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'القسم غير موجود',
                ]);
            }
            $collegedata = College::find($department->college_id);
            $libraryStaff = LibrarayStaff::where('college_id', $collegedata->id)
                ->where('user_id', Auth::id())
                ->get();

            if ($libraryStaff->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك',
                ]);
            }
            if ($department->projects()->count() > 0 || $department->students()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايمكن حذف هذه القسم '
                ], 400);
            }
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم الحذف بنجاح',
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف ',
                'error' => $exception->getMessage()
            ],500);
        }
    }

}
