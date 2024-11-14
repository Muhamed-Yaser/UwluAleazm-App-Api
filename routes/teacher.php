<?php

use App\Http\Controllers\Teacher\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (){
    Route::get('get-teacher-students' , [TeacherController::class , 'viewTeacherStudents']);
    Route::get('get-student-details/{id}' , [TeacherController::class , 'viewStudentDetails']);

    Route::post('/teacher/give-task/{studentId}', [TeacherController::class, 'giveTask']);
    Route::post('/teacher/rate-note-teacher/{sessionId}', [TeacherController::class, 'setRatingAndNotes']);
});
//
