<?php

namespace App\Http\Controllers;

use App\Models\College;
use Illuminate\Http\Request;

class CollegeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $colleges = College::select('id', 'name', 'universitie_id')->get();
        return response()->json(['success' => true, 'data' => $colleges], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validatedData['universitie_id'] = $id;
        $college = College::create($validatedData);

        return response()->json(['success' => true, 'data' => $college], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $college = College::findOrFail($id);
        return response()->json(['success' => true, 'data' => $college], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'universitie_id' => 'sometimes|required|exists:universities,id',
        ]);

        $college = College::findOrFail($id);
        $college->update($validatedData);

        return response()->json(['success' => true, 'data' => $college], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $college = College::findOrFail($id);
        $college->delete();

        return response()->json(['success' => true, 'message' => 'College deleted successfully'], 200);
    }
}
