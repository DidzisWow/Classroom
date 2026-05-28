<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $classes = Classroom::with('teacher')->withCount(['students', 'assignments'])->latest()->get();
        } elseif ($user->isTeacher()) {
            $classes = $user->teachingClasses()->withCount(['students', 'assignments'])->latest()->get();
        } else {
            $classes = $user->enrolledClasses()->with('teacher')->withCount(['students', 'assignments'])->latest()->get();
        }

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        if (!Auth::user()->isTeacher() && !Auth::user()->isAdmin()) abort(403);
        return view('classes.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isTeacher() && !Auth::user()->isAdmin()) abort(403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'color'       => ['nullable', 'string', 'max:20'],
        ]);

        $class = Classroom::create([
            ...$data,
            'teacher_id' => Auth::id(),
        ]);

        return redirect()->route('classes.show', $class)->with('success', 'Class created successfully!');
    }

    public function show(Classroom $classroom)
    {
        $this->authorizeView($classroom);

        $assignments = $classroom->assignments()->with(['files', 'submissions'])->get();

        return view('classes.show', compact('classroom', 'assignments'));
    }

    public function edit(Classroom $classroom)
    {
        if (!Auth::user()->isAdmin() && $classroom->teacher_id !== Auth::id()) abort(403);
        return view('classes.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        if (!Auth::user()->isAdmin() && $classroom->teacher_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'color'       => ['nullable', 'string', 'max:20'],
        ]);

        $classroom->update($data);

        return redirect()->route('classes.show', $classroom)->with('success', 'Class updated.');
    }

    public function destroy(Classroom $classroom)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $classroom->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted.');
    }

    public function join(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $classroom = Classroom::where('code', strtoupper($request->code))->first();

        if (!$classroom) {
            return back()->withErrors(['code' => 'Class not found. Check the code and try again.']);
        }

        $user = Auth::user();

        if ($classroom->students()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['code' => 'You are already enrolled in this class.']);
        }

        $classroom->students()->attach($user->id);

        return redirect()->route('classes.show', $classroom)->with('success', 'Joined class!');
    }

    private function authorizeView(Classroom $classroom): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) return;
        if ($user->isTeacher() && $classroom->teacher_id === $user->id) return;
        if ($user->isStudent() && $classroom->students()->where('user_id', $user->id)->exists()) return;

        abort(403);
    }
}