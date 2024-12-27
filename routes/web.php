<?php

use App\Http\Controllers\CollegeController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniversityController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return ["laravel"=> app()->version(). " for Ouis Alhetar"];
});

Route::get('/allunv',[UniversityController::class,'index']);
Route::get('/allcollege',[CollegeController::class,'index']);

Route::controller(ProjectController::class)->group(function () {
    Route::get('/allprojects', 'index');
});