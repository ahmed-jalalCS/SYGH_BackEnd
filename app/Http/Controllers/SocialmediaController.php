<?php

namespace App\Http\Controllers;

use App\Models\Socialmedie;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialmediaController extends Controller
{

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request,int $id)
    {
        $studentSocial=$request->validate([
             'linkes'=>'required',
        ]);
        $student=Student::where('user_id','=',$id)->first('id');
        $student->socialmedie()->create($studentSocial);
        return response()->json([
            'success'=>true,
            'message'=>'تم اضافة الحساب بنجاح '

        ]);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'linkes' => 'required|string',
        ]);
        $student = Student::where('user_id', Auth::id())->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود',
            ], 404);
        }
        $socialMedia = $student->socialmedie()->where('id', $id)->first();
        if (!$socialMedia) {
            return response()->json([
                'success' => false,
                'message' => 'حساب وسائل التواصل غير موجود',
            ], 404);
        }
        $socialMedia->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحساب بنجاح',
        ]);
    }

    public function destroy(int $id)
    {
        $student = Student::where('user_id', Auth::id())->first(['id']);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود',
            ], 404);
        }
        $socialData = Socialmedie::findOrFail($id);
        if ($socialData->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حذف هذا الحساب',
            ], 403);
        }

        $socialData->delete();
        return response()->json([
            'success' => true,
            'message' => 'تم الحذف بنجاح',
        ]);
    }

}
