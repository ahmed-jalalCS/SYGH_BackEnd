<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use App\Models\LibrarayStaff;
use function Symfony\Component\String\u;

class SupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $id)
    {

        $libraryStaff = LibrarayStaff::where('user_id', $id)->first();
        $collegeId = $libraryStaff->college_id;
        $supervisors = Supervisor::where('college_id', $collegeId)
            ->with('user:id,name')
            ->get(['id', 'user_id', 'supervisorDgree']);
            return response()->json([
                'success'=>true,
                'message'=>'جميع المشرفين',
                'data'=>$supervisors,

            ]);
        // return view('Supervisors.all-supervisors', ['supervisors' => $supervisors]);

    }

    public function create()
    {
    }

    // the library staff functionality
    public  function getAllSupervisors()
    {
        $librarayStaff=LibrarayStaff::where('user_id', Auth::id())->value('college_id');

        if(!$librarayStaff){
            return response()->json(['successes'=>true,'message'=>'لايوجد مشرفين '],200);
        }

        $supervisor=Supervisor::with('user:id,name,email,password','college:id,name')->where('college_id', $librarayStaff)->get();

        return response()->json([
            'success'=>true,
            'message'=>'كل المشرفين ',
            'data'=>$supervisor,
        ],200);
    }


    


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'supervisorDgree' => 'nullable|string|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors(),
                ]);
            }
    
            $validatedData = $validator->validate();
            $validatedData['email'] = Str::random(8) . '@gmail.com';
            $rawPassword = Str::random(12); // keep for optional use or logging
            $validatedData['password'] = Hash::make($rawPassword);
            $validatedData['role_id'] = 4;
    
            $collegeId = LibrarayStaff::where('user_id', Auth::id())->value('college_id');
            $user = User::create($validatedData);
    
            $supervisor = Supervisor::create([
                'user_id' => $user->id,
                'college_id' => $collegeId,
                'supervisorDgree' => $validatedData['supervisorDgree'] ?? null,
            ]);
    
            // Merge user and supervisor data
            $mergedData = array_merge($user->toArray(), $supervisor->toArray());
    
            return response()->json([
                'success' => true,
                'message' => 'تمت الاضافة بنجاح',
                'data' => $mergedData,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطاء اثناء الاضافة',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    




    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {

    }



    public function update(Request $request, int $id)
{
    try {
        $supervisor = Supervisor::find($id);
        if (!$supervisor) {
            return response()->json([
                'success' => false,
                'message' => 'لايوجد هذا المشرف ',
            ]);
        }

        $libraryStaff = LibrarayStaff::where('college_id', $supervisor->college_id)
            ->where('user_id', Auth::id())
            ->get();

        if ($libraryStaff->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $supervisor->user_id,
            'password' => 'nullable|string|max:255',
            'supervisorDgree' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors(),
            ]);
        }

        $validatedData = $validator->validate();

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $user = User::find($supervisor->user_id);
        if ($user) {
            $user->update($validatedData);
        }

        $supervisor->update([
            'supervisorDgree' => $validatedData['supervisorDgree'] ?? $supervisor->supervisorDgree,
        ]);

        // Merge user and supervisor data
        $mergedData = array_merge($user->toArray(), $supervisor->toArray());

        return response()->json([
            'success' => true,
            'message' => 'تم التحديث بنجاح',
            'data' => $mergedData,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطاء اثناء التعديل ',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function destroy(int $id)
    {
        try {
            $supervisor = Supervisor::find($id);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايوجد هذا المشرف ',
                ]);
            }

            $libraryStaff = LibrarayStaff::where('college_id', $supervisor->college_id)
                ->where('user_id', Auth::id())
                ->get();

            if ($libraryStaff->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك',]);
            }

            if ($supervisor->projects()->count() >0)
            {
                return response()->json(['success' => false,'message' => 'لايمكن حذف هذه المشرف  '], 400);
            }

            $user = User::find($supervisor->user_id);
            if ($user) {
                $user->delete();
            }
            $supervisor->delete();
            return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح',]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
