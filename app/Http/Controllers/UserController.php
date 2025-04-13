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
    public function update(Request $request)
    {

        $superAdmin=User::where('id',Auth::id())
            ->where('role_id',1) ->first();
        if($superAdmin){
                $validator = Validator::make($request->all(), [
                    'name' => 'nullable',
                    'password' => 'nullable|min:10',
                ]);
                $validatedData = $validator->validated();
                if (isset($validatedData['password'])) {
                    $validatedData['password'] = Hash::make($validatedData['password']);
                }
                $superAdmin->update($validatedData);
                return response()->json([
                    'success' => true,
                    'data' => $superAdmin
                ], 200);
        }

        $admin=User::where('id',Auth::id())->where('role_id',2) ->first();
        if($admin){
                $validator = Validator::make($request->all(), [
                    'name' => 'nullable',
                    'password' => 'nullable|min:10',
                ]);
                $validatedData = $validator->validated();
                if (isset($validatedData['password'])) {
                    $validatedData['password'] = Hash::make($validatedData['password']);
                }
                $admin->update($validatedData);
                return response()->json([
                    'success' => true,
                    'data' => $admin
                ], 200);
        }
        $librarystaff=User::with('librarystaffs') ->where('id',Auth::id())
            ->where('role_id',3) ->first();
        if($librarystaff){
                $validator = Validator::make($request->all(), [
                    'name' => 'nullable',
                    'password' => 'nullable|min:10',
                ]);

                $validatedData = $validator->validated();
                if (isset($validatedData['password'])) {
                    $validatedData['password'] = Hash::make($validatedData['password']);
                }
                $librarystaff->update($validatedData);
                return response()->json([
                    'success' => true,
                    'data' => $librarystaff
                ], 200);
        }
        $supervisor=User::with('supervisors') ->where('id',Auth::id())
            ->where('role_id',4) ->first();
        if($supervisor){
                $validator = Validator::make($request->all(), [
                    'name' => 'nullable',
                    'password' => 'nullable|min:10',
                    'supervisorDgree' => 'nullable|string|max:255',
                ]);
                $validatedData = $validator->validated();
                if (isset($validatedData['password'])) {
                    $validatedData['password'] = Hash::make($validatedData['password']);
                }
                $supervisorDegree = $validatedData['supervisorDgree'] ?? null;
                unset($validatedData['supervisorDgree']);
                $supervisor->update($validatedData);
                if ($supervisorDegree !== null && $supervisor->supervisors) {
                    $supervisor->supervisors->update([
                        'supervisorDgree' => $supervisorDegree
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => $supervisor->load('supervisors') // reload relation
                ], 200);
        }
        $student=User::with('students','students.socialmedies') ->where('id',Auth::id())
            ->where('role_id',5) ->first();
        if($student){
                $validator = Validator::make($request->all(), [
                    'name' => 'nullable|string|max:255',
                    'password' => 'nullable|min:10',
                    'linkes' => 'nullable|url', // Assuming linkes is a URL
                ]);
                $validatedData = $validator->validated();
                if (isset($validatedData['password'])) {
                    $validatedData['password'] = Hash::make($validatedData['password']);
                }
                $linkes = $validatedData['linkes'] ?? null;
                unset($validatedData['linkes']);
                $student->update($validatedData);
                if ($linkes !== null && $student->students && $student->students->socialmedies) {
                    $student->students->socialmedies->update([
                        'linkes' => $linkes
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => $student->load('students.socialmedies')
                ], 200);

        }
        $user=User::where('id',Auth::id())
            ->where('role_id',6) ->first();
        if($user){
                $validator = Validator::make($request->all(), [
                    'name' => 'nullable|string|max:255',
                    'password' => 'nullable|min:10',
                ]);
                $validatedData = $validator->validated();
                if (isset($validatedData['password'])) {
                    $validatedData['password'] = Hash::make($validatedData['password']);
                }
                $user->update($validatedData);
                return response()->json([
                    'success' => true,
                    'data' => $user
                ], 200);
        }
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
