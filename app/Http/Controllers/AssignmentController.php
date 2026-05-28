<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentFile;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function create(Request $request)
    {
        $class = Classroom::findOrFail($request->query('class_id'));
        $this->authorizeTeacher($class);
        return view('assignments.create', compact('class'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'type'         => ['required', 'in:assignment,announcement'],
            'due_date'     => ['nullable', 'date'],
            'files.*'      => ['nullable', 'file', 'max:20480'],
        ]);

        $class = Classroom::findOrFail($data['classroom_id']);
        $this->authorizeTeacher($class);

        $assignment = Assignment::create($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/' . $assignment->id, 'public');
                AssignmentFile::create([
                    'assignment_id' => $assignment->id,
                    'user_id'       => Auth::id(),
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('assignments.show', $assignment)->with('success', 'Assignment posted!');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['classroom.teacher', 'files', 'submissions.files', 'submissions.user']);
        return view('assignments.show', compact('assignment'));
    }

    public function destroy(Assignment $assignment)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $assignment->classroom->teacher_id !== $user->id) abort(403);
        $assignment->delete();
        return redirect()->route('classes.show', $assignment->classroom)->with('success', 'Assignment deleted.');
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $request->validate([
            'files'   => ['required', 'array'],
            'files.*' => ['file', 'max:20480'],
        ]);

        $submission = Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => Auth::id()],
            ['updated_at' => now()]
        );

        foreach ($request->file('files') as $file) {
            $path = $file->store('submissions/' . $submission->id, 'public');
            SubmissionFile::create([
                'submission_id' => $submission->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Submitted successfully!');
    }

    private function authorizeTeacher(Classroom $class): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && (!$user->isTeacher() || $class->teacher_id !== $user->id)) {
            abort(403);
        }
    }
}