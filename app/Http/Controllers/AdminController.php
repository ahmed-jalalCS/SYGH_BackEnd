<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
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
    public function getAllColleges(Request $request)
    {
        $colleges = College::where('university_id', function ($query) {
            $query->select('id')
                ->from('universities')
                ->where('user_id', Auth::id())
                ->limit(1);
        })->get();
        if ($colleges->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Colleges not found'
            ]);
        }
        return response()->json(['success' => true, 'data' => $colleges]);

//        $colleges = College::with(['university', 'departments'])
//            ->paginate($request->query('per_page', 10));
//        return response()->json(['success' => true, 'data' => $colleges]);

    }
}
