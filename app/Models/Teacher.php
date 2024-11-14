<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'years_of_experience',
        'rating',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'teacher_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'teacher_id');
    }
}
