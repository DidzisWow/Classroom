<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentFile;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        if (!$user->isAdmin() && 
            !($user->isTeacher() && $classroom->assigned_teacher_id === $user->id) &&
            !$classroom->students()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have access to this assignment.');
        }
        
        return view('assignments.show', compact('assignment'));
    }

    public function edit(Assignment $assignment)
    {
        $assignment->load(['classroom', 'files']);
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id) {
            abort(403, 'You can only edit your own assignments.');
        }
        
        return view('assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $assignment->load('classroom');
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id) {
            abort(403, 'You can only update your own assignments.');
        }

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'type'         => ['required', 'in:assignment,announcement'],
            'due_date'     => ['nullable', 'date'],
        ]);

        $assignment->update($data);

        // Add new files
        if ($request->hasFile('new_files')) {
            foreach ($request->file('new_files') as $file) {
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

        return redirect()->route('assignments.show', $assignment)->with('success', 'Assignment updated successfully!');
    }

    /**
     * FIX: Delete a single file from an assignment (without deleting the assignment)
     */
    public function deleteFile(Assignment $assignment, AssignmentFile $file)
    {
        $assignment->load('classroom');
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        // Check if file belongs to this assignment
        if ($file->assignment_id !== $assignment->id) {
            abort(404, 'File does not belong to this assignment.');
        }
        
        // Check if user can delete file
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id) {
            abort(403, 'Only the class teacher can delete files.');
        }
        
        // Delete physical file
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        
        $file->delete();
        
        return redirect()->back()->with('success', 'File deleted successfully!');
    }

    /**
     * FIX: Delete entire assignment (deletes all files first)
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->load(['classroom', 'files', 'submissions.files']);
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
        if (!$user->isAdmin() && $classroom->assigned_teacher_id !== $user->id) {
            abort(403, 'You can only delete your own assignments.');
        }
        
        // Delete all assignment files from storage and database
        foreach ($assignment->files as $file) {
            if (Storage::disk('public')->exists($file->path)) {
                Storage::disk('public')->delete($file->path);
            }
            $file->delete();
        }
        
        // Delete all submission files from storage and database
        foreach ($assignment->submissions as $submission) {
            foreach ($submission->files as $file) {
                if (Storage::disk('public')->exists($file->path)) {
                    Storage::disk('public')->delete($file->path);
                }
                $file->delete();
            }
        }
        
        $assignment->delete();
        return redirect()->route('classes.show', $assignment->classroom)->with('success', 'Assignment deleted successfully.');
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $classroom = $assignment->classroom;
        
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