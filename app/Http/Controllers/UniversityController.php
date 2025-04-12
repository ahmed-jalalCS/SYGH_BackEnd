<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UniversityController extends Controller
{
    public function index()
    {

        $universities = University::with(['colleges' => function ($query) {
            $query->select('id', 'name', 'universitie_id'); // Ensure 'university_id' is included for relationship
        }])->get();
        // we return the name of college for the design

        if ($universities->isEmpty()) {
            return response()->json(['message' => 'لايوجد جامعات  ']);
        }
        return response()->json($universities);

    }

   public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'address' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('universities', 'public');
            }

            $validatedData = $request->only(['name', 'address']);
            $validatedData['image'] = $imagePath;
            $university = University::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'تمت الإضافة بنجاح',
                'data' => $university,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب.',
                'error' => $e->getMessage()
            ], 500);
        }
}

    public function getAllUniversities(Request $request)
    {
        $universities = University::with('colleges')
            ->paginate($request->query('per_page', 10));
        return response()->json(['success' => true, 'data' => $universities]);
    }

    public function getALLUniversitiesDoesNotHaveAdmin()
    {
        $universities= University::where('user_id',null)->get();
        return response()->json(['success' => true, 'data' => $universities]);
    }

    public function viewUniversity($id)
    {
        $university = University::with(['colleges.departments'])->find($id);
        if (!$university) {
            return response()->json([
                'success' => false,
                'message'=>'لايوجد جامعة'
            ],200);
        }
        return response()->json(['success' => true, 'data' => $university]);
    }

    /**
     * Display the specified resource.
     */
 public function show(int $id)
    {

        $universityCollege=College::where('universitie_id',$id)->get();
        return response()->json($universityCollege);

    }



    public function edit(string $id)
    {

    }
    public function update(Request $request, int $id)
    {
        try {
            $university = University::find($id);
            if (!$university) {
                return response()->json([
                    'success' => false,
                    'message'=>'لايوجد جامعة'
                ],200);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string|max:255',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:4096',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 400);
            }
            $validatedData = $validator->validated();
            if ($request->hasFile('image')) {
                if ($university->image) {
                    Storage::disk('public')->delete($university->image);
                }
                $validatedData['image'] = $request->file('image')->store('universities', 'public');
            }
            $university->update($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'تم التعديل بنجاح',
                'data' => $university,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function destroy(int $id)
    {

        try {
            $university = University::find($id);
            if (!$university) {
                return response()->json([
                    'success' => false,
                    'message'=>'لايوجد جامعة'
                ],200);
            }
            if ($university->colleges()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لايمكن حذف هذه الجامعة'
                ], 400);
            }
            if ($university->image) {
            Storage::disk('public')->delete($university->image);
           }
            $university->delete();
            return response()->json(['success' => true,'message' => 'تم الحذف بنجاح',]);
        }catch (\Exception $e){
            return response()->json([
               'success' => false,
                'message' => 'حدث خطأ أثناء الحذف ',
                'error' => $e->getMessage()
            ],500);
        }
    }

}
