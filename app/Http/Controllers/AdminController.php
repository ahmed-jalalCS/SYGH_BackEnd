<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        $allAdmin = User::with('university:id,name,user_id')
            ->where('role_id', 2)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'university_name' => $user->university->name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $allAdmin
        ], 200);


    }

    public function create()
    {
        //
    }

    public function store(Request $request,int $id)
    {
        try {

            $university=University::findOrFail($id);
            if ($university->user_id !=null)
            {
                return response()->json([
                    'success'=>true,
                    'message'=>'هذة الجامعة لديها مسؤل بالفعل'
                ],200);
            }
            $validator= Validator::make($request->all(),[
                'name'=> 'required',
                'email'=>'required|email',
                'password'=>'required',
            ]);
            if($validator->fails()){
                return response()->json(['success'=>false,'errors'=>$validator->errors()]);
            }

            $ValidateData=$validator->validated();
            $ValidateData['role_id'] = 2;
            $ValidateData['password'] = Hash::make($ValidateData['password']);
            $user = User::create($ValidateData);
            $university->user_id = $user->id;
            $university->save();
            return response()->json([
                'success'=>true,
                'message'=>'تم الاظافه بنجاح',
                'data'=>$user
            ],200);
        }catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "لايوجد جامعة"
            ], 404);
        }
    }


    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, int $id)
    {
        try {


            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'  => 'sometimes|required|string',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'تم التحديث بنجاح',
                'data'    => $user
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'الجامعة أو المستخدم غير موجود',
            ], 404);
        }
    }

    public function destroyUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $university = University::where('user_id', $userId)->first();

            if ($university) {
                $university->user_id = null;
                $university->save();
            }
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المستخدم وتحديث الجامعة بنجاح',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود',
            ], 404);
        }
    }

    public function getAllColleges(Request $request)
    {
        $colleges = College::where('university_id', function ($query) {
            $query->select('id')
                ->from('universities')
                ->where('user_id', Auth::id())
                ->limit(1);
        })->get();
        if ($colleges->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Colleges not found'
            ]);
        }
        return response()->json(['success' => true, 'data' => $colleges]);

    }
}
