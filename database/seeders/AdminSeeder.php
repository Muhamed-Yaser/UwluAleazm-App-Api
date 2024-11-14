<?php

namespace Database\Seeders;

use DB;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'tahfez@gmail.com',
            'password' => Hash::make('tahfez_125_40'),
            'phone_number' => '0000000000',
            'role' => 'admin',
            'status' => 'approved',
            'photo' => null,
            'country' => 'Egypt',
            'language' => 'ar',
            'job' => 'Admin',
            'age' => null,
            'gender' => 'ذكر',
        ]);
    }
}
