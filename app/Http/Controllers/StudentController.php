<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

public function uploadProject(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'videoUrl' => 'nullable|url',
        'projectYear' => 'required|integer',
        'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        'supervisorName' => 'nullable|string',
        'students' => 'nullable|array',
        'students.*.name' => 'required|string',
        'students.*.email' => 'required|email',
        'students.*.social_links' => 'nullable|array',
    ]);

    // Upload document and get public URL if provided
    $documentPath = null;
    if ($request->hasFile('document')) {
        $path = $request->file('document')->store('projects/documents', 'public');
        $documentPath = Storage::url($path); // Get public link
    }

    // You can calculate average rating from students if available
    $averageRating = 0;
    if ($request->has('students')) {
        $ratings = collect($request->input('students'))->pluck('rating')->filter();
        $averageRating = $ratings->count() ? round($ratings->avg(), 2) : 0;
    }

    // Save the project to the database
    $project = Project::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'video_url' => $request->input('videoUrl'),
        'project_year' => $request->input('projectYear'),
        'average_rating' => $averageRating,
        'document_path' => $documentPath,
        'supervisor_name' => $request->input('supervisorName'),
    ]);

    // Optional: Handle storing student data (JSON or related table)
    if ($request->has('students')) {
        foreach ($request->input('students') as $student) {
            $project->students()->create([
                'name' => $student['name'],
                'email' => $student['email'],
                'social_links' => json_encode($student['social_links'] ?? []),
            ]);
        }
    }

    return response()->json(['message' => 'Project uploaded successfully!', 'project' => $project]);
}



    }

