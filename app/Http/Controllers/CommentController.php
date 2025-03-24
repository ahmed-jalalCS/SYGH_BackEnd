<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function store(Request $request,Project $project)
    {

        $data=$request->validate(['body'=>'required']);
        $project->comments()->create([
            'body' => $data['body'],
            'user_id' =>Auth::user()->id,
        ]);
        return response()->json([
            'state'=>'success',
            'message'=>'تم بنجاح ',
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
    public function update(Request $request, int $id)
    {
        $comment = Comment::findOrFail($id);

        // Check if the authenticated user owns the comment
        if (Auth::id() !== $comment->user_id) {
            return response()->json([
                'state' => 'error',
                'message' => 'ليس لديك صلاحية لتعديل هذا التعليق',
            ], 403); // 403 Forbidden
        }

        // Validate request data
        $data = $request->validate([
            'body' => 'required|string',
        ]);

        // Update the comment
        $comment->update($data);

        return response()->json([
            'state' => 'success',
            'message' => 'تم التعديل بنجاح',
        ], 200);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $comment = Comment::findOrFail($id);
        if (Auth::id() !== $comment->user_id) {
            return response()->json([
                'state' => 'error',
                'message' => 'ليس لديك صلاحية لحذف هذا التعليق',
            ], 403); // 403 Forbidden
        }
        if(Auth::id()==$comment->user_id)
            $comment->delete();

        return response()->json([
            'state' => 'success',
            'message' => 'تم الحذف بنجاح ',
        ]);

    }

}
