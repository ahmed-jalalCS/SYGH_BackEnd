<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $universities = University::with(['colleges' => function ($query) {
            $query->select('id', 'name', 'universitie_id'); // Ensure 'university_id' is included for relationship
        }])->get();
        // we return the name of college for the design
        return response()->json($universities);

    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Allow optional image uploads
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('universities', 'public'); // Store the image in the 'public/universities' folder
        }
        $validatedData['image']=$imagePath;
        $university=University::create($validatedData);

    // Build the full public URL for the image
    $publicImageUrl = $imagePath ? asset('storage/' . $imagePath) : null;

    // Return a JSON response with the university data
    return response()->json([
        'success' => true,
        'message' => 'University added successfully.',
        'data' => [
            'id' => $university->id,
            'name' => $university->name,
            'address' => $university->address,
            'image_url' => $publicImageUrl, // Include the full public URL for the image
        ],
    ], 201);
}




    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $universityCollege=College::where('universitie_id',$id)->get();
        return response()->json($universityCollege);

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

     }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $university = University::findOrFail($id);
        if ($university->image) {
            Storage::disk('public')->delete($university->image);
        }
        $university->delete();
        return response()->json([
            'success' => true,
            'message' => 'University deleted successfully.',
        ], 200);
    }

}
