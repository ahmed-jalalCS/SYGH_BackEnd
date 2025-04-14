<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index()
    {

    }

    public function create()
    {
        //
    }

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

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

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
