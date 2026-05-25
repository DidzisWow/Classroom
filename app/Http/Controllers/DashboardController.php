<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isTeacher() || $user->isAdmin()) {
            $classes = $user->isAdmin()
                ? Classroom::with('teacher')->withCount(['students', 'assignments'])->latest()->get()
                : $user->teachingClasses()->withCount(['students', 'assignments'])->latest()->get();

            $stats = [
                'classes'     => $classes->count(),
                'assignments' => Assignment::whereIn('classroom_id', $classes->pluck('id'))->count(),
                'pending'     => Assignment::whereIn('classroom_id', $classes->pluck('id'))
                                           ->whereDate('due_date', '>=', now())->count(),
                'students'    => $classes->sum('students_count'),
            ];
        } else {
            $classes = $user->enrolledClasses()->with('teacher')->withCount(['students', 'assignments'])->latest()->get();

            $stats = [
                'classes'     => $classes->count(),
                'assignments' => Assignment::whereIn('classroom_id', $classes->pluck('id'))->count(),
                'pending'     => Assignment::whereIn('classroom_id', $classes->pluck('id'))
                                           ->whereDate('due_date', '>=', now())->count(),
            ];
        }

        $recentAssignments = Assignment::with('classroom')
            ->whereIn('classroom_id', $classes->pluck('id'))
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.index', compact('classes', 'stats', 'recentAssignments'));
    }
}
