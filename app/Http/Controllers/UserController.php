<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
//use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // Validate the incoming request
        $userData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email', // Ensure email is unique
            'password' => 'required|min:6',
        ]);

        // Hash the password before storing it
        $userData['password'] = Hash::make($userData['password']);
        // Create the new user
        $newUser = User::create($userData);
        // Generate a token for the new user
        $token = $newUser->createToken('auth_token')->plainTextToken;
        // Return response with token
        return response()->json([
            'success' => true,
            'message' => 'تم انشاء الحساب بنجاح',
            'token' => $token, // Include the authentication token
            'user' => $newUser, // Optional: Return user data if needed
        ], 201);
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $user=User::where('role','User')->firstWhere('id', $id);
        if (!$user) {
            return response()->json(['success'=>true,'message'=>'لايمكنك حذف الحساب لارتباطة في بيانات اخرى',]);
        }
        $user->delete();
        return response()->json([
            'success'=>true,
            'message'=>'تم حذف الحساب بنجاح ',

        ]);

    }

    public function createAdmin(Request $request,int $id){

        try {

            $university=University::findOrFail($id);
            if ($university->user_id !==0)
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
            $ValidateData['role_id'] = 1;
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
}
