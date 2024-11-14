<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    protected $arabCountries = [
        'Egypt',
        'Saudi Arabia',
        'Jordan',
        'UAE',
        'Kuwait',
        'Qatar',
        'Oman',
        'Bahrain',
        'Lebanon',
        'Syria',
        'Iraq',
        'Yemen',
        'Palestine',
        'Sudan',
        'Libya',
        'Algeria',
        'Morocco',
        'Tunisia',
        'Mauritania',
        'Somalia',
        'Djibouti',
        'Comoros'
    ];

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8'],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:admin,student,teacher'],
            'country' => ['nullable', 'string', 'max:100'],
            'language' => ['required', 'in:en,ar'],
            'job' => ['nullable', 'string', 'max:100'],
            'age' => ['nullable', 'integer', 'min:1'],
            'gender' => ['nullable', 'in:male,female'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'years_of_experience' => ['nullable', 'string', 'max:255'],
            'quran_parts_memorized' => ['nullable', 'string', 'max:255']
        ]);

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'status' => 'pending', // Default status set to pending
            'country' => $request->country,
            'language' => $request->language,
            'job' => $request->job,
            'age' => $request->age,
            'gender' => $request->gender,
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
            $user->save();
        }

        if ($request->role === 'teacher') {
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'years_of_experience' => $request->years_of_experience,
            ]);
            $userData = array_merge($user->toArray(), ['years_of_experience' => $teacher->years_of_experience]);
        }

        if ($request->role === 'student') {
            $student = Student::create([
                'user_id' => $user->id,
                'quran_parts_memorized' => $request->quran_parts_memorized,
            ]);
            $userData = array_merge($user->toArray(), ['quran_parts_memorized' => $student->quran_parts_memorized]);
        }

        // Send email verification
        // try {
        //    // $user->sendEmailVerificationNotification();
        //    event(new Registered($user));
        // } catch (\Exception $e) {
        //     return response()->json([
        //          'message' => __('password_reset_link_error'),
        //         'error' => $e->getMessage()
        //     ], 500);
        // }

        return response()->json([
            'message' => __('messages.user_registered_successfully'),
            'status' => 201,
            'data' => $userData,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => "required|email",
            'password' => "required"
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => __('auth.failed'),
                'status' => 401
            ], 401);
        }

        if ($user->status === 'pending') {
            return response()->json([
                'message' => __('messages.wait_admin_approval'),
                'status' => 403
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $role =  $user->role;

        return response()->json([
            'token' => $token,
            'role' => $role,
            'status' => 200,
            'data' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => __('Successfully logged out')]);
    }
}
