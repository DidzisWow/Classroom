<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClassroomController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Debug: ieraksta logā skolotāja ID
        if ($user->isTeacher()) {
            Log::info('Teacher ID: ' . $user->id);
        }

        if ($user->isAdmin()) {
            $classes = Classroom::with('teacher', 'assignedTeacher')
                ->withCount(['students', 'assignments'])
                ->latest()
                ->get();
        } elseif ($user->isTeacher()) {
            // Skolotājs redz klases, kur viņš ir assigned_teacher_id VAI teacher_id
            $classes = Classroom::with('teacher', 'assignedTeacher')
                ->where('assigned_teacher_id', $user->id)
                ->orWhere('teacher_id', $user->id)
                ->withCount(['students', 'assignments'])
                ->latest()
                ->get();
            
            // Debug: ieraksta logā atrasto klašu skaitu
            Log::info('Classes found for teacher: ' . $classes->count());
        } else {
            $classes = $user->enrolledClasses()
                ->with('teacher', 'assignedTeacher')
                ->withCount(['students', 'assignments'])
                ->latest()
                ->get();
        }

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isTeacher() && !$user->isAdmin()) {
            abort(403, 'Only teachers and admins can create classes.');
        }
        
        $teachers = \App\Models\User::where('role', 'teacher')->get();
        return view('classes.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isTeacher() && !$user->isAdmin()) {
            abort(403, 'Only teachers and admins can create classes.');
        }

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string', 'max:500'],
            'color'               => ['nullable', 'string', 'max:20'],
            'assigned_teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        // Ja lietotājs ir skolotājs un nav norādīts assigned_teacher_id, piešķir sevi
        if ($user->isTeacher() && empty($data['assigned_teacher_id'])) {
            $data['assigned_teacher_id'] = $user->id;
        }
        
        // Ja lietotājs ir admin un nav norādīts assigned_teacher_id, liek NULL
        if ($user->isAdmin() && empty($data['assigned_teacher_id'])) {
            $data['assigned_teacher_id'] = null;
        }

        $classroom = Classroom::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'color' => $data['color'] ?? '#00e5ff',
            'teacher_id' => $user->id,
            'assigned_teacher_id' => $data['assigned_teacher_id'],
        ]);

        return redirect()->route('classes.show', $classroom)->with('success', 'Class created successfully!');
    }

    public function show(Classroom $classroom)
    {
        $user = Auth::user();
        
        // Pārbauda piekļuvi
        if (!$user->isAdmin() && 
            !($user->isTeacher() && ($classroom->assigned_teacher_id === $user->id || $classroom->teacher_id === $user->id)) &&
            !$classroom->students()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have access to this class.');
        }
        
        $classroom->load('students');
        $assignments = $classroom->assignments()->with(['files', 'submissions.user'])->get();
        return view('classes.show', compact('classroom', 'assignments'));
    }

    public function edit(Classroom $classroom)
    {
        $user = Auth::user();
        // Skolotājs var rediģēt tikai savu klasi
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id && $classroom->teacher_id !== $user->id) {
            abort(403, 'You can only edit your own classes.');
        }
        
        $teachers = \App\Models\User::where('role', 'teacher')->get();
        return view('classes.edit', compact('classroom', 'teachers'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $user = Auth::user();
        // FIX: Pārbauda gan assigned_teacher_id, gan teacher_id
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id && $classroom->teacher_id !== $user->id) {
            abort(403, 'You can only update your own classes.');
        }

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string', 'max:500'],
            'color'               => ['nullable', 'string', 'max:20'],
            'assigned_teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        $classroom->update($data);

        return redirect()->route('classes.show', $classroom)->with('success', 'Class updated successfully.');
    }

    public function destroy(Classroom $classroom)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admins can delete classes.');
        }
        
        $classroom->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
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

        return redirect()->route('classes.show', $classroom)->with('success', 'Successfully joined the class!');
    }
}