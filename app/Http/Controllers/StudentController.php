<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
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
        //
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
    public function destroy(string $id)
    {
        //
    }

    public function UploadProject(int $id){

        $projectDetails = Student::where('user_id', $id) // Replace $id with Auth::id() if needed
                                ->where('isTemLeder', 1)
                                ->with(['project:id,title,description,videoUrl,supervisor_id']) // Eager load the project with selected fields
                                ->first()
                                ->project;
        return response()->json([
            'success' => true,
            'data' => $projectDetails,
        ]);



    }
}
