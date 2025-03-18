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
    // Return a JSON response with the university data
    return response()->json([
        'success' => true,
        'message' => 'تمت الإضافة بنجاح',
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
        $university = University::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($university->image) {
                Storage::disk('public')->delete($university->image);
             }
           $imagePath = $request->file('image')->store('universities', 'public');
           $validatedData['image'] = $imagePath;
        } else {
            $validatedData['image'] = $university->image;
        }
        $university->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'تم التعديل بنجاح ',
        ], 200);
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
