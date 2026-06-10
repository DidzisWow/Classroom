<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function view(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin() || 
               ($user->isTeacher() && $classroom->assigned_teacher_id === $user->id) ||
               $classroom->students()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isTeacher() || $user->isAdmin();
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $classroom->assigned_teacher_id === $user->id);
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin();
    }
}