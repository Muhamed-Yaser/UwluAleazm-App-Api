<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
        'status',
        'photo',
        'country',
        'language',
        'job',
        'age',
        'gender',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'photo',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at'
    ];

    protected $appends = [
        'photo_url'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function joinRequest()
    {
        return $this->hasOne(JoinRequest::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }


    //Accessor
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return 'https://www.shutterstock.com/image-vector/profile-default-avatar-icon-user-600nw-2463844171.jpg';
        }
        if (Str::startsWith($this->photo, ['http://', 'https://'])) {
            return $this->photo;
        }
        return url('storage/' . $this->photo);
    }
}
