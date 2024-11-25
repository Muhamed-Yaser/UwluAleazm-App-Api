<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\AuthAdminController;

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    //Auth
    Route::post('/admin-login' , [AuthAdminController::class , 'login'])->withoutMiddleware(['auth:sanctum','admin']);
    Route::get('/admin-profile' , [ProfileController::class , 'show']);
    Route::post('/admin-logout' , [AuthAdminController::class , 'logout']);

    //Approve Users
    Route::post('/approve-teacher/{id}', [AdminController::class, 'approveTeacher']);
    Route::post('/approve-student/{id}', [AdminController::class, 'approveStudent']);

    //Reject Users
    Route::post('/reject-teacher/{id}', [AdminController::class, 'rejectTeacher']);
    Route::post('/reject-student/{id}', [AdminController::class, 'rejectStudent']);

    //Approve or reject all teachers
    Route::post('/approve-all-teachers', [AdminController::class, 'approveAllPendingTeachers']);
    Route::post('/reject-all-teachers', [AdminController::class, 'rejectAllPendingTeachers']);

    //Show and operations on users
    Route::get('/users', [AdminController::class, 'showAllUsers']);
    Route::get('/teachers', [AdminController::class, 'showAllTeachers']);
    Route::get('/pending-teachers', [AdminController::class, 'showAllPendingTeachers']);

    Route::get('/students', [AdminController::class, 'showAllStudents']);
    Route::get('/pending-students', [AdminController::class, 'showAllPendingStudents']);

    Route::get('/users-count', [AdminController::class, 'getDashboardNumbers']);
    Route::post('/delete-user/{id}', [AdminController::class, 'deleteUser']);

    //search
    Route::get('/search-users', [AdminController::class, 'searchUsers']);









});
//
