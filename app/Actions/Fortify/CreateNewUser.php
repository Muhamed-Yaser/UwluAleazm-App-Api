<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:admin,student,teacher'],
            'status' => ['in:pending,approved,rejected'],
            'photo' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:100'],
            'job' => ['nullable', 'string', 'max:100'],
            'age' => ['nullable', 'integer', 'min:1'],
            'gender' => ['nullable'],
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'phone_number' => $input['phone_number'],
            'role' => $input['role'], // 'student', 'teacher', or 'admin'
            'status' => $input['status'] ?? 'pending',
            'photo' => $input['photo'] ?? null,
            'country' => $input['country'] ?? null,
            'language' => $input['language'] ?? null,
            'job' => $input['job'] ?? null,
            'age' => $input['age'] ?? null,
            'gender' => $input['gender'] ?? null,
        ]);
    }
}
