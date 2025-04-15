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

    public function show()
    {

        $superAdmin=User::where('id',Auth::id())
                                ->where('role_id',1) ->first();
        if($superAdmin){
            return response()->json(['successes'=>true,'data'=>$superAdmin],200);
        }
        $admin=User::where('id',Auth::id())
                   ->where('role_id',2) ->first();
        if($admin){
            return response()->json(['successes'=>true,'data'=>$admin],200);
        }
        $librarystaff=User::with('librarystaffs') ->where('id',Auth::id())
                            ->where('role_id',3) ->first();
        if($librarystaff){
            return response()->json(['successes'=>true,'data'=>$librarystaff],200);
        }
        $supervisor=User::with('supervisors') ->where('id',Auth::id())
                          ->where('role_id',4) ->first();
        if($supervisor){
            return response()->json(['successes'=>true,'data'=>$supervisor],200);
        }
        $student=User::with('students','students.socialmedies') ->where('id',Auth::id())
                      ->where('role_id',5) ->first();
        if($student){
            return response()->json(['successes'=>true,'data'=>$student],200);
        }
        $user=User::where('id',Auth::id())
            ->where('role_id',6) ->first();
        if($user){
            return response()->json(['successes'=>true,'data'=>$user],200);
        }



    }
    public function edit(string $id)
    {
        //
    }

    public function update(Request $request)
    {
        $roles = [
            1 => 'superAdmin',
            2 => 'admin',
            3 => 'librarystaff',
            4 => 'supervisor',
            5 => 'student',
            6 => 'user',
        ];

        $user = User::with(['students.socialmedie', 'supervisors', 'librarystaffs'])
            ->where('id', Auth::id())
            ->first();

        if (!$user || !array_key_exists($user->role_id, $roles)) {
            return response()->json(['success' => false, 'message' => 'User not found or invalid role'], 404);
        }

        $rules = [
            'name' => 'nullable|string|max:255',
            'old_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:10|confirmed',
        ];

        if ($user->role_id == 4) {
            $rules['supervisorDgree'] = 'nullable|string|max:255';
        }

        if ($user->role_id == 5) {
            $rules['linkes'] = 'nullable|url';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['password'])) {
            if (!Hash::check($validatedData['old_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور القديمة غير صحيحة',
                ], 422);
            }
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        unset($validatedData['old_password']);
        if ($user->role_id == 4) {
            $supervisorDegree = $validatedData['supervisorDgree'] ?? null;
            unset($validatedData['supervisorDgree']);
        }

        if ($user->role_id == 5) {
            $linkes = $validatedData['linkes'] ?? null;
            unset($validatedData['linkes']);
        }
        $user->update($validatedData);
        if ($user->role_id == 4 && isset($supervisorDegree) && $user->supervisors) {
            $user->supervisors->update(['supervisorDgree' => $supervisorDegree]);
        }

        if ($user->role_id == 5) {
            $linkes = $validatedData['linkes'] ?? null;
            unset($validatedData['linkes']);
        }

        $user->update($validatedData);

        if ($user->role_id == 5 && isset($linkes) && $user->students->isNotEmpty()) {
            $student = $user->students->first();
            $social = $student->socialmedie->first(); // لأنها hasMany
            if ($social) {
                $social->update(['linkes' => $linkes]);
            }
        }

        $relations = match ($user->role_id) {
            3 => ['librarystaffs'],
            4 => ['supervisors'],
            5 => ['students.socialmedie'],
            default => [],
        };
        return response()->json([
            'success' => true,
            'data' => $user->load($relations)
        ], 200);
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
}
