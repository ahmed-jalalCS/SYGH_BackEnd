<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
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

    }
}
