<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use App\Models\LibrarayStaff;

class SupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $id)
    {

        $libraryStaff = LibrarayStaff::where('user_id', $id)->first();
        $collegeId = $libraryStaff->college_id;
        $supervisors = Supervisor::where('college_id', $collegeId)
            ->with('user:id,name')
            ->get(['id', 'user_id', 'supervisorDgree']);    

            return response()->json([
                'success'=>true,
                'message'=>'جميع المشرفين',
                'data'=>$supervisors,

            ]);
        // return view('Supervisors.all-supervisors', ['supervisors' => $supervisors]);
    
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('Supervisors.new-supervisor');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'supervisorDgree' => 'nullable|string|max:255',
        ]);
        
        $libraryStaff = LibrarayStaff::where('user_id', Auth::id())->firstOrFail();
        $collegeId = $libraryStaff->college_id;
        $randomEmail = Str::random(10) . '@example.com';
        $randomPassword = Str::random(12);
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $randomEmail,
            'password' => Hash::make($randomPassword),
            'role' => 'Supervisor',
        ]);
        $supervisor = Supervisor::create([
            'user_id' => $user->id,
            'college_id' => $collegeId,
            'supervisorDgree' => $validatedData['supervisorDgree'] ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تمت الاضافة بنجاح ',
        ], 201);
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
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'supervisorDgree' => 'nullable|string|max:255',
        ]);
        $supervisor = Supervisor::findOrFail($id);
        $supervisor->user->update(['name' => $validatedData['name']]);
        $supervisor->update(['supervisorDgree' => $validatedData['supervisorDgree'] ?? null]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل البيانات بنجاح ',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
