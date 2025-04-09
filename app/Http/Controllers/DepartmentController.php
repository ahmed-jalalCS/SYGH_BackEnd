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
        $department = Department::find($id);

        $departmentProject = Project::select('id','title','description')
            ->where('department_id', $id)
            ->where('supervisorStatus', true)
            ->where('lbraryStatus', true)
            // ->with(['document' => fn($query) => $query->select('id', 'pathDo','project_id')])
            ->get();
        if ($departmentProject->isEmpty())
        {
            return response()->json(['message' => 'لايوجد مشاريع لهذا القسم '], 404);
        }
        return response()->json(['success' => true, 
        "department_name"=>$department->name,
        'data' => $departmentProject], 200);
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
                'college.departments:id,name,college_id'
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
    public function store(Request $request, int $collegeId)
    {
        try {
            $collegedata = College::with('libraryStaffs')->find($collegeId);
            if (!$collegedata) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايوجد كلية ',
                ]);
            }

            $libraraystaff = LibrarayStaff::where('college_id', $collegedata->id)
                ->where('user_id', Auth::id())
                ->get();

            if ($libraraystaff->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك ',
                ]);
            }
            $validator= Validator::make($request->all(),[
                'name'=> 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }
            $validatedData = $validator->validated();
            $validatedData['college_id'] = $collegedata->id;
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
