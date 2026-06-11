<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function grade(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'grade'    => ['required', 'numeric', 'min:0', 'max:10'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $class = $submission->assignment->classroom;
        $user  = Auth::user();

        if (!$user->isAdmin() && $class->assigned_teacher_id !== $user->id) {
            abort(403, 'Only the class teacher can grade submissions.');
        }

        $submission->update($data);

        return back()->with('success', 'Grade saved successfully!');
    }

    public function deleteFile(Submission $submission, SubmissionFile $file)
    {
        $user = Auth::user();
        
        if ($submission->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'You can only delete your own files.');
        }
        
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        
        $file->delete();
        
        return back()->with('success', 'File deleted successfully!');
    }
}