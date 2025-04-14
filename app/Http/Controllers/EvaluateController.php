<?php

namespace App\Http\Controllers;

use App\Models\Evaluate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluateController extends Controller
{

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

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

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
