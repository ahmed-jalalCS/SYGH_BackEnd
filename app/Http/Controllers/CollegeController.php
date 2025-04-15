<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Department;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;

class CollegeController extends Controller
{

    public function index()
    {
        $colleges = College::select('id', 'name', 'universitie_id')->get();
        if ($colleges->isEmpty()) {
            return response()->json(['message' => 'لايوجد كليات ']);
        }
        return response()->json(['success' => true, 'data' => $colleges], 200);

    }
    public function show(int $id)
    {
        $college = College::find($id);
        if (!$college) {
            return response()->json([
                'success' => false,
                'message' => 'الكلية غير موجودة'
            ], 404);
        }
        $departments = Department::where('college_id', $id)->get();
        return response()->json([
            'success' => true,
            'college_name' => $college->name,
            'data' => $departments
        ], 200);
    }
    // Admin Functionalty
    public function getAllColleges(Request $request)
    {
        $colleges = College::where('universitie_id', function ($query) {
            $query->select('id')
                ->from('universities')
                ->where('user_id', Auth::id())
                ->limit(1);
        })->get();

        if ($colleges->isEmpty()) {return response()->json(['success' => true, 'message' => 'لايوجد كليات']);}

        return response()->json(['success' => true, 'data' => $colleges]);

    }

    // public function store(Request $request, int $id)
    // {
    //     try {
    //         $university = University::find($id);
    //         if (!$university) {return response()->json(['success'=>false,'message'=>'لاتوجد هذه الجامعة']);}

    //         if (Auth::user()->id!==$university->user_id) {return response()->json(['error' => 'غير مصرح بك '], 403);}
    //         $validator=Validator::make($request->all(), [
    //            'name' => 'required |string|max:255',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['error'=>$validator->errors()], 401);
    //         }

    //         $validatedData=$validator->validated();
    //         $validatedData['universitie_id']=$university->id;
    //         $collegeData= College::create($validatedData);
    //         return response()->json(['success' => true,'message' => 'تمت الإضافة بنجاح'],200);
    //     }catch (\Exception $e){
    //         return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء التعديل ', 'error'=>$e->getMessage()], 500);

    //     }
    // }

    public function store(Request $request)
    {
        try {
            $university = University::where('user_id', Auth::user()->id)->get()->first();
            if (!$university) {
                return response()->json(['success' => false, 'message' => 'لاتوجد هذه الجامعة']);
            }

            if (Auth::user()->id !== $university->user_id) {
                return response()->json(['error' => 'غير مصرح بك '], 403);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required |string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            $validatedData = $validator->validated();

            $validatedData['universitie_id'] = $university->id;
            $collegeData = College::create($validatedData);
            return response()->json(['success' => true, 'message' => 'تمت الإضافة بنجاح'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطاء اثناء التعديل ', 'error' => $e->getMessage()], 500);
        }
    }
    public function showAdmin(int $id)
    {
        try {
            $college=College::find($id);
            if (!$college) {return response()->json(['success' => false, 'message'=>'لايوجد كلية']);}
            if(Auth::user()->id!==$college->university->user_id){return response()->json(['error' => 'غير مصرح لفعل هذه العملية'], 403);}
            return response()->json(['success' => true, 'data' => $college->makeHidden('university')], 200);
        }catch (\Exception $e){

            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء التعديل ', 'error'=>$e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {

        try {
            $college=College::find($id);
            if (!$college) {
                return response()->json(['success' => false, 'message'=>'لايوجد كلية'
                ]);
            }

            if(Auth::user()->id!==$college->university->user_id){
                return response()->json(['error' => 'غير مصرح لفعل هذه العملية'], 403);
            }
            $validator=Validator::make($request->all(), [
                'name' => 'required|string|max:255',

            ]);
            $college->update($validator->validated());
            return response()->json(['success'=>true,'message'=>'تم التعديل بنجاح']);
        }catch (\Exception $e){
            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء التعديل ', 'error'=>$e->getMessage()], 401);
        }
    }

    public function deleteCollege($id)
    {
        try {
            $college=College::find($id);
            if (!$college) {
                return response()->json(['success' => false, 'message'=>'لايوجد كلية'
                ]);
            }
            if(Auth::user()->id!==$college->university->user_id){
                return response()->json(['error' => 'غير مصرح لفعل هذه العملية'], 403);
            }
            if ($college->department()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايمكن حذف هذه الكيلة'
                ], 400);
            }
            $college->delete();
            return response()->json(['success'=>true,'message'=>'تم الحذف بنجاح']);
        }catch (\Exception $e){
            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء التعديل ', 'error'=>$e->getMessage()], 401);
        }

    }

    public function destroy(int $id)
    {

    }


}
