<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    // Approve teacher
    public function approveTeacher(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->status === 'approved') {
            return response()->json(['message' => __('messages.teacher_already_approved')], 200);
        }

        if ($user->role === 'teacher') {
            $user->status = 'approved';
            $user->save();

            return response()->json([
                'message' => __('messages.Teacher approved successfully'),
                'status' => 200,
                'data' => $user,
            ], 200);
        }

        return response()->json(['message' => __('messages.User is not a teacher')], 400);
    }

    public function approveAllPendingTeachers()
    {
        $pendingTeachers = User::where('role', 'teacher')->where('status', 'pending')->get();

        foreach ($pendingTeachers as $teacher) {
            $teacher->status = 'approved';
            $teacher->save();
        }

        return response()->json([
            'message' => __('messages.All pending teachers approved successfully'),
            'status' => 200,
        ], 200);
    }

    // Reject teacher
    public function rejectTeacher($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'teacher') {
            return response()->json(['message' => __('messages.User is not a teacher')], 400);
        }

        $user->delete();

        return response()->json([
            'message' => __('messages.Teacher rejected and deleted successfully'),
            'status' => 200,
        ], 200);
    }

    public function rejectAllPendingTeachers()
    {
        $pendingTeachers = User::where('role', 'teacher')->where('status', 'pending')->get();

        foreach ($pendingTeachers as $teacher) {
            $teacher->delete();
        }

        return response()->json([
            'message' => __('messages.All pending teachers rejected and deleted successfully'),
            'status' => 200,
        ], 200);
    }

    // Approve student and assign a teacher
    public function approveStudent(Request $request, $id)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $user = User::findOrFail($id);

        if ($user->role !== 'student') {
            return response()->json(['message' => __('messages.User is not a student')], 400);
        }

        if ($user->status === 'approved') {
            return response()->json(['message' => __('messages.Student is already approved')], 200);
        }

        $student = Student::updateOrCreate(
            ['user_id' => $user->id],
            ['teacher_id' => $request->teacher_id]
        );

        $user->status = 'approved';
        $user->save();

        return response()->json([
            'message' => __('messages.Student approved and assigned to teacher successfully'),
            'status' => 200,
            'data' => $user,
        ], 200);
    }

    public function rejectStudent($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'student') {
            return response()->json(['message' => __('messages.User is not a student')], 400);
        }

        $user->delete();

        return response()->json([
            'message' => __('messages.Student rejected and deleted successfully'),
            'status' => 200,
        ], 200);
    }

    public function showAllUsers()
    {
        $users = User::where('status', 'approved')->latest()->paginate(10);

        return response()->json([
            'message' => __('messages.Latest joined Teachers and Students'),
            'status' => 200,
            'data' => $users,
        ], 200);
    }

    public function showAllTeachers()
    {
        $teachers = User::where('role', 'teacher')->where('status', 'approved')
            ->latest()
            ->with(['teacher' => function ($query) {
                $query->select('user_id', 'years_of_experience', 'rating')
                    ->withCount('students as number_of_students');
            }])
            ->paginate(10);

        // Add number of students to each teacher
        $teachers->transform(function ($teacher) {
            $teacher->number_of_students = $teacher->teacher->number_of_students ?? 0;
            return $teacher;
        });

        return response()->json([
            'message' => __('messages.Latest joined Teachers'),
            'status' => 200,
            'data' => $teachers,
        ], 200);
    }

    public function showAllStudents()
    {
        $students = User::where('role', 'student')->where('status', 'approved')
            ->latest()
            ->with(['student' => function ($query) {
                $query->select('user_id', 'quran_parts_memorized', 'total_points', 'teacher_id');
            }, 'student.teacher.user:id,name'])
            ->paginate(10);

        // Add teacher name to each student
        $students->transform(function ($student) {
            if ($student->student && $student->student->teacher && $student->student->teacher->user) {
                $student->teacher_name = $student->student->teacher->user->name;
            } else {
                $student->teacher_name = null;
            }
            return $student;
        });

        return response()->json([
            'message' => __('messages.Latest joined Students'),
            'status' => 200,
            'data' => $students,
        ], 200);
    }

    public function getDashboardNumbers()
    {
        $totalTeachers = User::where('role', 'teacher')->where('status', 'approved')->count();

        $totalStudents = User::where('role', 'student')->where('status', 'approved')->count();

        $pendingTeachers = User::where('role', 'teacher')->where('status', 'pending')->count();

        $pendingStudents = User::where('role', 'student')->where('status', 'pending')->count();

        return response()->json([
            'total_teachers_number' => $totalTeachers,
            'pending_teachers_number' => $pendingTeachers,
            'total_students_number' => $totalStudents,
            'pending_students_number' => $pendingStudents,
        ], 200);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query');

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('phone_number', 'LIKE', "%{$query}%")
                ->orWhere('gender', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhere('country', 'LIKE', "%{$query}%")
                ->orWhere('language', 'LIKE', "%{$query}%")
                ->orWhere('job', 'LIKE', "%{$query}%")
                ->orWhere('role', 'LIKE', "%{$query}%");
        })
            ->latest()
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => __('messages.No matching search'),
                'status' => 200,
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => __('messages.Search results'),
            'status' => 200,
            'data' => $users,
        ], 200);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'teacher' || $user->role === 'student') {
            $user->delete();
            return response()->json([
                'message' => __('messages.User deleted successfully'),
                'status' => 200,
            ], 200);
        }

        return response()->json(['message' => __('messages.User is not a teacher or student')], 400);
    }
}
