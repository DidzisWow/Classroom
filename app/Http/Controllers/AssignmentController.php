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

        return redirect()->route('assignments.show', $assignment)->with('success', 'Assignment posted successfully!');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['classroom.teacher', 'classroom.assignedTeacher', 'files', 'submissions.files', 'submissions.user']);
        
        // Pārbauda vai lietotājam ir piekļuve šim uzdevumam
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        if (!$user->isAdmin() && 
            !($user->isTeacher() && $classroom->assigned_teacher_id === $user->id) &&
            !$classroom->students()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have access to this assignment.');
        }
        
        return view('assignments.show', compact('assignment'));
    }

    public function destroy(Assignment $assignment)
    {
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id) {
            abort(403, 'You can only delete your own assignments.');
        }
        
        $assignment->delete();
        return redirect()->route('classes.show', $assignment->classroom)->with('success', 'Assignment deleted successfully.');
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        // Pārbauda vai students ir pierakstījies klasē
        if (!$classroom->students()->where('user_id', $user->id)->exists()) {
            abort(403, 'You are not enrolled in this class.');
        }
        
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

        return back()->with('success', 'Assignment submitted successfully!');
    }

    private function authorizeTeacher(Classroom $class): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && (!$user->isTeacher() || $class->assigned_teacher_id !== $user->id)) {
            abort(403, 'You do not have permission to manage this class.');
        }
    }
}