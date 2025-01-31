<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        $userData=$request->validate([
            'name'=> 'required',
            'email'=>'required|email',
            'password'=>'required',
        ]);

        $newUser=User::create($userData);

        return response()->json([
            'successes'=>'true',
            'message'=>'تم انشاء الحساب بنجاح ',

        ]);

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
}
