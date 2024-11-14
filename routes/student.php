<?php

use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Student\StudentController;



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/assigned-tasks', [StudentController::class, 'viewAssignedTasks']);

    //rate and comment to teacher
    Route::post('/rate-teacher', [ProfileController::class, 'rateTeacher']);
    Route::post('/update-comment/{commentId}', [ProfileController::class, 'updateComment']);
    Route::post('/delete-comment/{commentId}', [ProfileController::class, 'deleteComment']);
});
//
