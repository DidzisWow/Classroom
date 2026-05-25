<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()?->isAdmin()) abort(403);
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::latest()->paginate(20);

        $stats = [
            'total_users' => User::count(),
            'teachers'    => User::where('role', 'teacher')->count(),
            'students'    => User::where('role', 'student')->count(),
            'classes'     => Classroom::count(),
        ];

        $actionHistory = ActionLog::with('user')->latest()->take(30)->get();

        return view('admin.index', compact('users', 'stats', 'actionHistory'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:student,teacher,admin'],
        ]);

        $old = $user->role;
        $user->update(['role' => $request->role]);

        ActionLog::create([
            'user_id' => Auth::id(),
            'action'  => "Changed {$user->name}'s role from {$old} to {$request->role}",
        ]);

        return back()->with('success', 'Role updated.');
    }

    public function resetPassword(User $user)
    {
        $newPassword = 'ClassNova@123';
        $user->update(['password' => Hash::make($newPassword)]);

        ActionLog::create([
            'user_id' => Auth::id(),
            'action'  => "Reset password for {$user->name}",
        ]);

        return back()->with('success', "Password reset to: {$newPassword}");
    }
}