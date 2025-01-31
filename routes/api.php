<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\SocialmediaController;

Route::get('/test', function () {
    return response()->json(['message' => 'API routes are working']);
  });

  // Public routes
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);

  // Protected routes
  Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
  });


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

    Route::get('/colleges','index');
    Route::post('/college/{id}','store');
    Route::get('/college/{id}','show');
    Route::put('/college/{id}','update');
    Route::delete('/college/{id}','destroy');
});
Route::controller(DepartmentController::class)->group(function (){

    Route::get('/departments','index');
    Route::post('/department/{id}','store');
    Route::get('/department/{id}','show');
    Route::put('/department/{id}','update');
    Route::delete('/department/{id}','destroy');

});
Route::controller(CommentController::class)->group(function (){

    Route::post('project/{project:id}/comment','store');// this is for add new comment to the project
    Route::post('comment/{id}/delete','destroy');//delete the comment
    Route::put('comment/{id}/update','update');// update  the comment


});

Route::controller(UserController::class)->group(function (){
    Route::get('/users','index');
    Route::post('/newUser','store');
    Route::get('user/information/{id}','show');// we should update the code
    Route::delete('/delete/user/{id}','destroy');// we should update the code

});
Route::controller(SocialmediaController::class)->group(function(){

    Route::post('/newLinke','store');// add the linkes of the student
    Route::delete('/deleteLinke/{id}','destroy');// delete



});
Route::controller(StudentController::class)->group(function(){
   Route::get('/uploaddocument/{id}','UploadProject');// return the detail about its project
});
Route::controller(DocumentController::class)->group(function(){
   Route::get('/projectwithdocument','index');
    Route::post('/document/{id}','store');// whene student post the file and the other input to save its project

});

Route::controller(ProjectController::class)->group(function (){
    
    Route::get('project/{id}/detials','show');
});
