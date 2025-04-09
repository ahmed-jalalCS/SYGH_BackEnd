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
    Route::delete('/user/{id}','destroy');// we should update the code 

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
    Route::post('project','store');
    Route::put('/project/{id}','update');
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
    Route::controller(UserController::class)->group(function () {
       Route::post('/user/{id}','createAdmin');

    });

});


Route::prefix('/admin')->controller(AdminController::class)->middleware(['auth:sanctum','admin'])->group(function () {

    Route::controller(CollegeController::class)->group(function () {
       Route::get('/colleges', 'getAllColleges');
       Route::get('/colleges/{id}', 'show');
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


    Route::controller(DepartmentController::class)->group(function (){
        Route::get('/departments', 'getAllDepartments');
        Route::post('/departments/{id}', 'store');
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

    });
    Route::controller(ProjectController::class)->group(function (){

    });
});
// Route::middleware(['auth:sanctum', 'super_admin'])->group(function () {
//   // Users
//   Route::get('/users', [SuperAdminController::class, 'getAllUsers']);
//   Route::post('/users', [SuperAdminController::class, 'createUser']);
//   Route::get('/users/{id}', [SuperAdminController::class, 'viewUser']);
//   Route::put('/users/{id}', [SuperAdminController::class, 'updateUser']);
//   Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser']);

//   // Students
//   Route::get('/students', [SuperAdminController::class, 'getAllStudents']);
//   Route::post('/students', [SuperAdminController::class,
//     'createStudent'
//   ]);
//   Route::get('/students/{id}', [SuperAdminController::class, 'viewStudent']);
//   Route::put('/students/{id}', [SuperAdminController::class,
//     'updateStudent'
//   ]);
//   Route::delete('/students/{id}', [SuperAdminController::class, 'deleteStudent']);

//   // Universities
//   Route::get('/universities', [SuperAdminController::class, 'getAllUniversities']);
//   Route::post('/universities', [SuperAdminController::class, 'createUniversity']);
//   Route::get('/universities/{id}', [SuperAdminController::class, 'viewUniversity']);
//   Route::put('/universities/{id}', [SuperAdminController::class, 'updateUniversity']);
//   Route::delete('/universities/{id}', [SuperAdminController::class, 'deleteUniversity']);

//   // Departments
//   Route::get('/departments', [SuperAdminController::class, 'getAllDepartments']);
//   Route::post('/departments',
//     [SuperAdminController::class, 'createDepartment']
//   );
//   Route::get('/departments/{id}', [SuperAdminController::class, 'viewDepartment']);
//   Route::put('/departments/{id}', [SuperAdminController::class, 'updateDepartment']);
//   Route::delete('/departments/{id}', [SuperAdminController::class, 'deleteDepartment']);

//   // Projects
//   Route::get('/projects', [SuperAdminController::class, 'getAllProjects']);
//   Route::post('/projects', [SuperAdminController::class, 'createProject']);
//   Route::get('/projects/{id}', [SuperAdminController::class, 'viewProject']);
//   Route::put('/projects/{id}', [SuperAdminController::class, 'updateProject']);
//   Route::delete('/projects/{id}', [SuperAdminController::class, 'deleteProject']);

//   // Colleges
//   Route::get('/colleges', [SuperAdminController::class, 'getAllColleges']);
//   Route::post('/colleges', [SuperAdminController::class, 'createCollege']);
//   Route::get('/colleges/{id}', [SuperAdminController::class, 'viewCollege']);
//   Route::put('/colleges/{id}', [SuperAdminController::class, 'updateCollege']);
//   Route::delete('/colleges/{id}', [SuperAdminController::class, 'deleteCollege']);

//   // Supervisors
//   Route::get('/supervisors', [SuperAdminController::class, 'getAllSupervisors']);
//   Route::post('/supervisors', [SuperAdminController::class, 'createSupervisor']);
//   Route::get('/supervisors/{id}', [SuperAdminController::class, 'viewSupervisor']);
//   Route::put('/supervisors/{id}', [SuperAdminController::class, 'updateSupervisor']);
//   Route::delete('/supervisors/{id}', [SuperAdminController::class,
//     'deleteSupervisor'
//   ]);

//   // Library Staffs
//   Route::get('/library-staffs', [SuperAdminController::class, 'getAllLibraryStaffs']);
//   Route::post('/library-staffs', [SuperAdminController::class, 'createLibraryStaff']);
//   Route::get('/library-staffs/{id}',
//     [SuperAdminController::class, 'viewLibraryStaff']
//   );
//   Route::put('/library-staffs/{id}', [SuperAdminController::class, 'updateLibraryStaff']);
//   Route::delete('/library-staffs/{id}', [SuperAdminController::class, 'deleteLibraryStaff']);
// });

Route::middleware(['auth:sanctum', 'super_admin'])->group(function () {
    // Dashboard Stats
    Route::get('/admin/dashboard/stats', [SuperAdminController::class, 'getDashboardStats']);

    // View Only Routes
    Route::get('/admin/users', [SuperAdminController::class, 'getAllUsers']);
    Route::get('/admin/students', [SuperAdminController::class, 'getAllStudents']);

    // Universities Management
    Route::prefix('admin/universities')->group(function () {
        Route::get('/', [SuperAdminController::class, 'getAllUniversities']);
        Route::post('/', [SuperAdminController::class, 'createUniversity']);
        Route::get('/{id}', [SuperAdminController::class, 'viewUniversity']);
        Route::put('/{id}', [SuperAdminController::class, 'updateUniversity']);
        Route::delete('/{id}', [SuperAdminController::class, 'deleteUniversity']);
    });

    // Colleges Management
//    Route::prefix('admin/colleges')->group(function () {
//        Route::get('/', [SuperAdminController::class, 'getAllColleges']);
//        Route::post('/', [SuperAdminController::class, 'createCollege']);
//        Route::get('/{id}', [SuperAdminController::class, 'viewCollege']);
//        Route::put('/{id}', [SuperAdminController::class, 'updateCollege']);
//        Route::delete('/{id}', [SuperAdminController::class, 'deleteCollege']);
//    });

    // Library Staff Management
//    Route::prefix('admin/library-staff')->group(function () {
//        Route::get('/', [SuperAdminController::class, 'getAllLibraryStaff']);
//        Route::post('/', [SuperAdminController::class, 'createLibraryStaff']);
//        Route::get('/{id}', [SuperAdminController::class, 'viewLibraryStaff']);
//        Route::put('/{id}', [SuperAdminController::class, 'updateLibraryStaff']);
//        Route::delete('/{id}', [SuperAdminController::class, 'deleteLibraryStaff']);
//    });
//
//    // Projects Management
//    Route::prefix('admin/projects')->group(function () {
//        Route::get('/', [SuperAdminController::class, 'getAllProjects']);
//        Route::post('/', [SuperAdminController::class, 'createProject']);
//        Route::get('/{id}', [SuperAdminController::class, 'viewProject']);
//        Route::put('/{id}', [SuperAdminController::class, 'updateProject']);
//        Route::delete('/{id}', [SuperAdminController::class, 'deleteProject']);
//    });

    // Import Projects
//    Route::post('/admin/import-projects', [SuperAdminController::class, 'importProjects']);
//});

// for uploading the excel file:
//Route::post('/import-projects', [SuperAdminController::class, 'importProjects'])
//    ->middleware(['auth:sanctum', 'super_admin']);
//
//
//
//// Library-Staff Endpoints:
//Route::controller(LibraryStaffController::class)->middleware(['auth:sanctum', 'library_staff'])->group(function () {
//    // Dashboard and Import
//
//    Route::get('/library/dashboard/stats', 'getDashboardStats');
//    Route::post('/library/import-projects','importProjects');

//    // Student Management
//    Route::prefix('library/students')->group(function () {
//        Route::get('/', [LibraryStaffController::class, 'getStudents']);
//        Route::post('/', [LibraryStaffController::class, 'createStudent']);
//        Route::put('/{id}', [LibraryStaffController::class, 'updateStudent']);
//        Route::delete('/{id}', [LibraryStaffController::class, 'deleteStudent']);
//    });
//
//
//    // Project Management
//    Route::prefix('library/projects')->group(function () {
//        Route::get('/', [LibraryStaffController::class, 'getProjects']);
//        Route::post('/', [LibraryStaffController::class, 'createProject']);
//        Route::get('/{id}', [LibraryStaffController::class, 'viewProject']);
//        Route::put('/{id}', [LibraryStaffController::class, 'updateProject']);
//        Route::delete('/{id}', [LibraryStaffController::class, 'deleteProject']);
//    });
//
//
//    // Supervisor Management
//    Route::prefix('library/supervisors')->group(function () {
//        Route::get('/', [LibraryStaffController::class, 'getSupervisors']);
//        Route::post('/', [LibraryStaffController::class, 'createSupervisor']);
//        Route::get('/{id}', [LibraryStaffController::class, 'viewSupervisor']);
//        Route::put('/{id}', [LibraryStaffController::class, 'updateSupervisor']);
//        Route::delete('/{id}', [LibraryStaffController::class, 'deleteSupervisor']);
//    });
//
//
//    // Department Management
//    Route::prefix('library/departments')->group(function () {
//        Route::get('/', [LibraryStaffController::class, 'getDepartments']);
//        Route::post('/', [LibraryStaffController::class, 'createDepartment']);
//        Route::get('/{id}', [LibraryStaffController::class, 'viewDepartment']);
//        Route::put('/{id}', [LibraryStaffController::class, 'updateDepartment']);
//        Route::delete('/{id}', [LibraryStaffController::class, 'deleteDepartment']);
//    });
});

