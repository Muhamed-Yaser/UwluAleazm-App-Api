<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'student_id',
        'surah_name',
        'verses_from',
        'verses_to',
        'scheduled_time',
        'meeting_link',
        'teacher_rating',
        'teacher_notes',
    ];
    
    protected $hidden = [
        'updated_at',
        'created_at'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
