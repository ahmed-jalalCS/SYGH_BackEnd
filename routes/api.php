<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(UniversityController::class)->group(function(){

    Route::get('/universities','index');
    Route::post('/university','store');
    Route::get('/university/{id}','show');
    Route::put('/university/{id}','update');
    Route::delete('/university/{id}','destroy');
});

Route::controller(CollegeController::class)->group(function(){
 Route::get('university/{id}/colleges','index');
});

Route::controller(DepartmentController::class)->group(function (){

    Route::get('college/{id}/department','index');
});

Route::controller(ProjectController::class)->group(function (){

    Route::get('department/{id}/projects','index');
    Route::get('departmentandsupervisorid/{id}','GetDepartmentIdAndSupervisorId');
});