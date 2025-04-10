<?php

namespace App\Http\Controllers;

use App\Models\Evaluate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluateController extends Controller
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
    public function store(Request $request, $project_id)
    {
       $data=$request->validate([
            'rating' => 'required|numeric|min:1|max:5', // Ensure rating is between 1 and 5
        ]);
    
        $data['user_id']=Auth::id();
        $data['project_id']=$project_id;
        $evaluate = Evaluate::create($data);
        return response()->json([
            'message' => 'تم ',
            'evaluate' => $evaluate,
        ], 201);
    }

    public function show(Request $request,string $id)
    {
        $rating = Evaluate::where("project_id", $id)->where("user_id", Auth::id())->get("rating");
        
        if (count(value: $rating) == 0) {
            return response()->json([
               'message' => 'لم يتم تقييم المشروع حتى ',
            ], 404);
            
        } else {
            return response()->json([
               'message' => 'تم تقييم المشروع بنجا�� ',
                'rating' => $rating[0]->rating,
            ], 200);
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
}
