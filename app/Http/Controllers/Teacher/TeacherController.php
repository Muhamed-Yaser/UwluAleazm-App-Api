<?php

namespace App\Http\Controllers\Teacher;

use App\Models\User;
use App\Models\Session;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function viewTeacherStudents()
    {
        $teacher = Auth::user();

        if ($teacher->role !== 'teacher') {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        $students = Student::with('user')
            ->where('teacher_id', $teacher->teacher->id)
            ->paginate(10);

        return response()->json([
            'message' => __('messages.teacher_students'),
            'status' => 200,
            'data' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'profile_picture' => $student->user->photo_url,
                    'name' => $student->user->name,
                    'total_points' => $student->total_points,
                    'quran_parts_memorized' => $student->quran_parts_memorized,
                ];
            }),
        ], 200);
    }

    public function viewStudentDetails($id)
    {
        $teacher = Auth::user();

        if ($teacher->role !== 'teacher') {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        $student = Student::with('user')
            ->where('id', $id)
            ->where('teacher_id', $teacher->teacher->id)
            ->firstOrFail();

        return response()->json([
            'message' => __('messages.student_details'),
            'status' => 200,
            'data' => [
                'id' => $student->id,
                'profile_picture' => $student->user->photo_url,
                'role' => $student->user->role,
                'name' => $student->user->name,
                'total_points' => $student->total_points,
                'quran_parts_memorized' => $student->quran_parts_memorized,
            ],
        ], 200);
    }

    public function giveTask(Request $request, $studentId)
    {
        $request->validate([
            'surah_name' => 'required|string|max:255',
            'verses_from' => 'nullable|string|max:255',
            'verses_to' => 'nullable|string|max:255',
            'scheduled_time' => 'nullable|date_format:Y-m-d H:i:s',
            'meeting_link' => 'nullable|url',
        ]);

        $teacher = Auth::user();

        if ($teacher->role !== 'teacher') {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        $student = Student::where('id', $studentId)
            ->where('teacher_id', $teacher->teacher->id)
            ->firstOrFail();

        $session = Session::create([
            'teacher_id' => $teacher->teacher->id,
            'student_id' => $student->id,
            'surah_name' => $request->surah_name,
            'verses_from' => $request->verses_from,
            'verses_to' => $request->verses_to,
            'scheduled_time' => $request->scheduled_time,
            'meeting_link' => $request->meeting_link,
        ]);

        return response()->json([
            'message' => __('messages.assigned_task_success'),
            'status' => 201,
            'data' => $session,
        ], 201);
    }


    public function setRatingAndNotes(Request $request, $sessionId)
    {
        $request->validate([
            'teacher_notes' => 'nullable|string',
            'parts_memorized_increment' => 'nullable|integer|min:0|max:30' // Allow teacher to input how many parts the student gained
        ]);

        $teacher = Auth::user();

        if ($teacher->role !== 'teacher') {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        // Find the session and ensure it belongs to the teacher
        $session = Session::where('id', $sessionId)
            ->where('teacher_id', $teacher->teacher->id)
            ->firstOrFail();

        $session->update([
            'teacher_rating' => $request->rating,
            'teacher_notes' => $request->teacher_notes,
        ]);

        // Find the student and update their total points
        $student = $session->student;
        $student->total_points += $request->rating;

        // Update Quran parts memorized if provided by teacher
        if ($request->filled('parts_memorized_increment')) {
            $newPartsMemorized = $student->quran_parts_memorized + $request->parts_memorized_increment;

            // Ensure that parts memorized doesn't exceed 30
            if ($newPartsMemorized > 30) {
                $newPartsMemorized = 30;
            }

            $student->quran_parts_memorized = $newPartsMemorized;
        }

        $student->save();

        return response()->json([
            'message' => __('messages.rating_notes_updated'),
            'status' => 200,
            'data' => $session,
        ], 200);
    }
}
