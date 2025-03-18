<?php

namespace App\Http\Controllers;

use App\Models\Socialmedie;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialmediaController extends Controller
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
    public function store(Request $request,int $id)
    {
        $studentSocial=$request->validate([
             'linkes'=>'required',
        ]);
             //Auth::id() // we put it rether then the id paramter
        $student=Student::where('user_id','=',$id)->first('id');
        $student->socialmedie()->create($studentSocial);
        return response()->json([
            'success'=>true,
            'message'=>'تم اضافة الحساب بنجاح '

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $socialData=Socialmedie::findOrFail($id);
        $socialData->delete();
        return response()->json([
            'success'=>true,
            'message'=>'تم الحذف بنجاح ',
        ]);

    }
}
