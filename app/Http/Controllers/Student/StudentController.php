<?php

namespace App\Http\Controllers\Student;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function viewAssignedTasks()
    {
        $student = Auth::user();

        if ($student->role !== 'student') {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        // Get all sessions assigned to this student
        $sessions = Session::where('student_id', $student->student->id)
            ->with('teacher.user')
            ->get();

        return response()->json([
            'message' => __('messages.assigned_tasks'),
            'status' => 200,
            'data' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'surah_name' => $session->surah_name,
                    'verses_from' => $session->verses_from,
                    'verses_to' => $session->verses_to,
                    'scheduled_time' => $session->scheduled_time,
                    'meeting_link' => $session->meeting_link,
                    'teacher_notes' => $session->teacher_notes,
                    'teacher_name' => $session->teacher->user->name,
                ];
            }),
        ], 200);
    }
}
