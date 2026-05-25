<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function grade(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'grade'    => ['required', 'numeric', 'min:0', 'max:10'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        // Only the classroom teacher or admin can grade
        $class = $submission->assignment->classroom;
        $user  = Auth::user();

        if (!$user->isAdmin() && $class->teacher_id !== $user->id) {
            abort(403);
        }

        $submission->update($data);

        return back()->with('success', 'Grade saved.');
    }
}