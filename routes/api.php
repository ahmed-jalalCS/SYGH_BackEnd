<?php

use App\Http\Controllers\EvaluateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SocialmediaController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\LibraryStaffController;
use App\Http\Controllers\AdminController;

Route::get('/test', function () {

    return response()->json(['message' => 'API routes are working']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route:: middleware(['auth:sanctum'] )->controller(UniversityController::class)->group(function(){
    Route::get('/universities','index')->withoutMiddleware(['auth:sanctum']);// all universities
    Route::get('/university/{id}','show')->withoutMiddleware(['auth:sanctum']);// all college of the university
});

Route::middleware(['auth:sanctum'] )->controller(CollegeController::class)->group(function(){
    Route::get('/colleges','index')->withoutMiddleware(['auth:sanctum']);// all college and its forgien key
    Route::get('/college/{id}','show')->withoutMiddleware(['auth:sanctum']);// all department of this college
});

Route:: middleware(['auth:sanctum'] )->controller(DepartmentController::class)->group(function (){
    Route::get('/departments','index')->withoutMiddleware(['auth:sanctum']);//
    Route::get('/department/{id}','show')->withoutMiddleware(['auth:sanctum']);// show all project of department
});


Route:: middleware(['auth:sanctum'] )->controller(CommentController::class)->group(function (){

    Route::post('/project/{project:id}/comment','store');// this is for add new comment to the project
    Route::delete('/comment/{id}','destroy');//delete the comment
    Route::put('/comment/{id}','update');// update  the comment
});



Route::controller(UserController::class)->group(function (){
    Route::get('/users','index')->middleware("auth:sanctum");
    Route::post('/user','store');// create new user
    Route::get('/user/{id}','show');// we should update the code
    Route::delete('/user','destroy');// we should update the code

});


Route::middleware(['auth:sanctum'] )->controller(SocialmediaController::class)->group(function(){

    Route::post('/newLinke','store');// add the linkes of the student
    Route::delete('/deletelinke/{id}','destroy');// delete
    Route::put('/updatelinke/{id}','update');


});


Route::middleware(['auth:sanctum'] )->controller(StudentController::class)->group(function(){
    Route::get('/upload','UploadProject');
});


Route::middleware(['auth:sanctum'] )->controller(DocumentController::class)->group(function(){
    Route::get('/projectwithdocument','index')->withoutMiddleware(['auth:sanctum']);
    Route::post('/document/{id}','store');// whene student post the file and the other input to save its project

});
Route::middleware(['auth:sanctum'] )->controller(EvaluateController::class)->group(function (){

     Route::post('/evaluate/{id}','store');
     Route::get('evaluate/{id}','show');


});
Route::middleware(['auth:sanctum'])->controller(ProjectController::class)->group(function (){
    Route::get('/projects','index')->withoutMiddleware(['auth:sanctum']);
    Route::post('/projects','uploadeproject');
    Route::get('/project/{id}','show')->withoutMiddleware(['auth:sanctum']);

});

Route::prefix('/superadmin')->controller(SuperAdminController::class)->middleware(['auth:sanctum', 'super_admin'])->group(function () {


    Route::controller(UniversityController::class)->group(function () {
        Route::get('/universities', 'getAllUniversities');
        Route::post('/university', 'store');
        Route::get('/university/{id}', 'viewUniversity');
        Route::put('/university/{id}', 'update');
        Route::delete('/university/{id}', 'destroy');
    });
//    Route::controller(UserController::class)->group(function () {
//       Route::post('/user/{id}','createAdmin');
//
//    });

    Route::controller(AdminController::class)->group(function () {
        Route::get('/admins','index');
        Route::post('/admin/{id}','store');
        Route::get('/admin/{id}','show');
        Route::put('/admin/{id}','update');
        Route::delete('/admin/{id}','destroy');
    });

});


Route::prefix('/admin')->controller(AdminController::class)->middleware(['auth:sanctum','admin'])->group(function () {

    Route::controller(CollegeController::class)->group(function () {
       Route::get('/colleges', 'getAllColleges');
       //Route::get('/colleges/{id}', 'show');
       Route::post('/colleges/{id}', 'store');
       Route::put('/colleges/{id}', 'update');
       Route::delete('/colleges/{id}', 'deleteCollege');
    });
    Route::controller(LibraryStaffController::class)->group(function (){
       Route::post('/colleges/{id}/addlibraraystaff','store');
       Route::delete('/colleges/{id}/deletelibraraystaff','destroy');
    });
});
Route::prefix('librarayStaff')->controller(LibraryStaffController::class)->middleware(['auth:sanctum','libraraystaff'])->group(function (){



    Route::get('/','index');
    Route::controller(DepartmentController::class)->group(function (){
        Route::get('/departments', 'getAllDepartments');
        Route::post('/departments', 'store');
        Route::put('/departments/{id}', 'update');
        Route::delete('/departments/{id}', 'destroy');

    });
        Route::controller(SupervisorController::class)->group(function (){
        Route::get('/supervisors', 'getAllSupervisors');
        Route::post('/supervisors', 'store');
        Route::put('/supervisors/{id}', 'update');
        Route::delete('/supervisors/{id}', 'destroy');
    });
    Route::controller(StudentController::class)->group(function (){

        Route::get('/students', 'index');
        Route::post('/students', 'store');
        Route::put('/students/{id}', 'update');
        Route::delete('/students/{id}', 'destroy');


    });
    Route::controller(ProjectController::class)->group(function (){
        Route::get('/projects', 'getAllProjects');
        Route::post('/projects', 'store');
        Route::put('/projects/{id}', 'update');
        Route::delete('/projects/{id}', 'destroy');


    });
});

//
//Route::middleware(['auth:sanctum', 'super_admin'])->group(function () {
//    // Dashboard Stats
//    Route::get('/admin/dashboard/stats', [SuperAdminController::class, 'getDashboardStats']);
//
//    // View Only Routes
//    Route::get('/admin/users', [SuperAdminController::class, 'getAllUsers']);
//    Route::get('/admin/students', [SuperAdminController::class, 'getAllStudents']);
//
//    // Universities Management
//    Route::prefix('admin/universities')->group(function () {
//        Route::get('/', [SuperAdminController::class, 'getAllUniversities']);
//        Route::post('/', [SuperAdminController::class, 'createUniversity']);
//        Route::get('/{id}', [SuperAdminController::class, 'viewUniversity']);
//        Route::put('/{id}', [SuperAdminController::class, 'updateUniversity']);
//        Route::delete('/{id}', [SuperAdminController::class, 'deleteUniversity']);
//    });
//}
