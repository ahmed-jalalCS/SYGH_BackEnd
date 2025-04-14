<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{

    public function index()
    {
        //$projects = Project::with('document')->get();

        $documents=Document::all();
        return response()->json([
            'success'=>true,
            'message'=>$documents,

        ]);
    }

    public function create()
    {
        //
    }


    public function store(Request $request,int $id)
    {
//        $validatedData = $request->validate([
//            'description' => 'nullable|string',
//            'videoUrl' => 'nullable|url',
//            'pathDo' => 'required|file|mimes:pdf,doc,docx',
//        ]);
//
//        // Update the project details
//        $project = Project::findOrFail($id);
//        $project->update([
//            'description' => $validatedData['description'] ?? $project->description,
//            'videoUrl' => $validatedData['videoUrl'] ?? $project->videoUrl,
//        ]);
//
//        // Handle file upload and store the file path
//        if ($request->hasFile('pathDo')) {
//            $file = $request->file('pathDo');
//            $filePath = $file->store('documents', 'public'); // Save to 'storage/app/public/documents'
//
//            // Store the file path in the Document table
//            $Document=Document::create([
//                'pathDo' => $filePath,
//                'project_id' => $id,
//            ]);
//            return response()->json([
//                'success'=>true,
//                'message'=>'Document uploaded successfully',
//                'document'=>$Document,
//            ]);
//        }
//

    }

    public function show(string $id)
    {
        //
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
