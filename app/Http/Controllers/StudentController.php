<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {




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
    public function store(Request $request)
    {

        try {

            $validator= Validator::make($request->all(),[
                'name'=> 'required|max:255|string',
                'studentUnid'=>'required|integer',
                'isTemLeder'=>'required|integer',
                'project_id'=>'required|integer',
                'department_id'=>'required|integer',
            ]);
            if($validator->fails()){
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $validatedData = $validator->validate();
            $validatedData['email']=Str::random(8) . '@gmail.com';
            $validatedData['password']=Str::random(12);
            $validatedData['password']=Hash::make($validatedData['password']);
            $validatedData['role_id']=5;

            $userData=User::create($validatedData);
            $validatedData['user_id']=$userData->id;
            $studentdata=Student::create($validatedData);


            return response()->json(['success'=>true,'message'=>'تمت الاضافة بنجاح' ,'data'=>$studentdata]);

        }catch (\Exception $e){
            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء الاضافة ', 'error'=>$e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, $id)
    {
        try {

            $student = Student::with('user', 'project')->findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name'          => 'nullable|string|max:255',
                'email'         => 'nullable|email|max:255|unique:users,email,' . $student->user_id,
                'password'      => 'nullable|string|max:255',
                'studentUnid'   => 'nullable|integer',
                'isTemLeder'    => 'nullable|integer',
                'project_id'    => 'nullable|integer|exists:projects,id',
                'department_id' => 'nullable|integer|exists:departments,id',
            ]);

            $validated = $validator->validate();
            $updateUserData = [];
            if (isset($validated['name'])) {
                $updateUserData['name'] = $validated['name'];
            }
            if (isset($validated['email'])) {
                $updateUserData['email'] = $validated['email'];
            }
            if (isset($validated['password'])) {
                $updateUserData['password'] = Hash::make($validated['password']);
            }
            if (!empty($updateUserData)) {
                $student->user->update($updateUserData);
            }
            $updateStudentData = [];
            if (isset($validated['studentUnid'])) {
                $updateStudentData['studentUnid'] = $validated['studentUnid'];
            }
            if (isset($validated['isTemLeder'])) {
                $updateStudentData['isTemLeder'] = $validated['isTemLeder'];
            }
            if (isset($validated['project_id'])) {
                $updateStudentData['project_id'] = $validated['project_id'];
            }
            if (isset($validated['department_id'])) {
                $updateStudentData['department_id'] = $validated['department_id'];
            }
            if (!empty($updateStudentData)) {
                $student->update($updateStudentData);
            }
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات الطالب بنجاح',
                'data' => $student->load('user', 'project')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التعديل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $student = Student::with('user')->findOrFail($id);
            if ($student->user) {
                $student->user->evaluates()->delete();
                $student->user->comments()->delete();
                $student->user->delete(); // Delete the user
            }
            $student->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الطالب وجميع البيانات المرتبطة به بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function UploadProject(int $id){

        $projectDetails = Student::where('user_id', $id) // Replace $id with Auth::id() if needed
                                ->where('isTemLeder', 1)
                                ->with(['project:id,title,description,videoUrl,supervisor_id']) // Eager load the project with selected fields
                                ->first()
                                ->project;
        return response()->json([
            'success' => true,
            'data' => $projectDetails,
        ]);



    }
}
