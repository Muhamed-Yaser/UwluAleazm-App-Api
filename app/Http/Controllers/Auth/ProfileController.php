<?php

namespace App\Http\Controllers\Auth;

use App\Models\Comment;
use App\Models\Session;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'country' => $user->country,
            'photo_url' => $user->photo_url,
        ];

        if ($user->role === 'student') {

            $totalPoints = $user->student->total_points;
            $comments = Comment::where('user_id', $user->id)
                ->with('teacher.user')
                ->get();

            $commentsData = $comments->map(function ($comment) {
                return [
                    'comment_id' => $comment->id,
                    'teacher_name' => $comment->teacher->user->name,
                    'rating' => $comment->rating,
                    'comment' => $comment->comment,
                    'date_commented' => $comment->created_at->toDateString(),
                ];
            });

            $data['total_points'] = $totalPoints;
            $data['comments'] = $commentsData;
        }

        return response()->json([
            'data' => $data,
        ], 200);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->country = $request->country;

        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Store new photo
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
        }

        $user->save();

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'country' => $user->country,
            'photo_url' => $user->photo_url,
        ];

        if ($user->role === 'student') {

            $totalPoints = $user->student->total_points;
            $comments = Comment::where('user_id', $user->id)
                ->with('teacher.user')
                ->get();

            $commentsData = $comments->map(function ($comment) {
                return [
                    'comment_id' => $comment->id,
                    'teacher_name' => $comment->teacher->user->name,
                    'rating' => $comment->rating,
                    'comment' => $comment->comment,
                    'date_commented' => $comment->created_at->toDateString(),
                ];
            });

            $data['total_points'] = $totalPoints;
            $data['comments'] = $commentsData;
        }

        return response()->json([
            'message' => __('messages.profile_update_success'),
            'data' => $data,
        ], 200);
    }

    public function rateTeacher(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'rating' => 'required|numeric|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $student = Auth::user();

        if ($student->role !== 'student') {
            return response()->json(['message' => __('You are not authorized to perform this action')], 403);
        }

        Comment::create([
            'teacher_id' => $request->teacher_id,
            'user_id' => $student->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Update the teacher's average rating
        $teacher = Teacher::findOrFail($request->teacher_id);
        $averageRating = Comment::where('teacher_id', $request->teacher_id)->avg('rating');
        $teacher->rating = round($averageRating, 2); // Update rating to the average of all ratings
        $teacher->save();

        return response()->json([
            'message' => __('messages.teacher_rating_comment_success'),
        ], 200);
    }

    public function updateComment(Request $request, $commentId)
    {
        $request->validate([
            'rating' => 'required|numeric|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $student = Auth::user();

        if ($student->role !== 'student') {
            return response()->json(['message' => __('You are not authorized to perform this action')], 403);
        }

        // Find the comment
        $comment = Comment::where('id', $commentId)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $comment->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => __('messages.comment_updated'),
        ], 200);
    }

    public function deleteComment($commentId)
    {
        $student = Auth::user();

        if ($student->role !== 'student') {
            return response()->json(['message' => __('You are not authorized to perform this action')], 403);
        }

        $comment = Comment::where('id', $commentId)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $comment->delete();

        return response()->json([
            'message' => __('messages.comment_deleted'),
        ], 200);
    }
}
