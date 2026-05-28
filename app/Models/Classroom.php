<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'code',
        'teacher_id',
        'assigned_teacher_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($classroom) {
            $classroom->code = strtoupper(Str::random(6));
        });
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_user', 'classroom_id', 'user_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class)->latest();
    }
}