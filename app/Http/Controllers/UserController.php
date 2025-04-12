<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
//use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
  public function index()
    {

        $user = User::where("id",Auth::id())->first();

        return response()->json($user, 200);
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email', // Ensure email is unique
                'password' => 'required|min:10',
            ]);

            $validatedData = $validator->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);

            $newUser = User::create($validatedData);
            $token = $newUser->createToken('auth_token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'تم انشاء الحساب بنجاح',
                'token' => $token, // Include the authentication token
                'user' => $newUser, // Optional: Return user data if needed
            ], 201);

        }catch (Exception $e){
            return response()->json(['success'=>false,'message'=>'حدث خطاء اثناء التسجيل ', 'error'=>$e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {

        $userData=User::findOrFail($id);

        return response()->json([
            'success'=>true,
            'message'=>'بيانات المستخدم ',
            'data'=>$userData,
        ]);
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
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy()
    {
        $user=User::where('role_id',6)->firstWhere('id', Auth::id());
        if (!$user) {
            return response()->json(['success'=>true,'message'=>'لايمكنك حذف الحساب لارتباطة في بيانات اخرى',]);
        }
        $user->evaluates()->delete();
        $user->comments()->delete();
        $user->delete();
        return response()->json([
            'success'=>true,
            'message'=>'تم حذف الحساب بنجاح ',

        ]);

    }

//    public function createAdmin(Request $request,int $id){
//
//        try {
//
//            $university=University::findOrFail($id);
//            if ($university->user_id !=null)
//                {
//                    return response()->json([
//                        'success'=>true,
//                        'message'=>'هذة الجامعة لديها مسؤل بالفعل'
//                    ],200);
//                }
//            $validator= Validator::make($request->all(),[
//                'name'=> 'required',
//                'email'=>'required|email',
//                'password'=>'required',
//            ]);
//            if($validator->fails()){
//                return response()->json(['success'=>false,'errors'=>$validator->errors()]);
//            }
//
//            $ValidateData=$validator->validated();
//            $ValidateData['role_id'] = 2;
//            $ValidateData['password'] = Hash::make($ValidateData['password']);
//            $user = User::create($ValidateData);
//            $university->user_id = $user->id;
//            $university->save();
//            return response()->json([
//                'success'=>true,
//                 'message'=>'تم الاظافه بنجاح',
//                'data'=>$user
//            ],200);
//        }catch (ModelNotFoundException $e) {
//            return response()->json([
//                'error' => "لايوجد جامعة"
//            ], 404);
//        }
//    }
}
