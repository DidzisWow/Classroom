<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        Comment::create([
            'assignment_id' => $assignment->id,
            'user_id'       => Auth::id(),
            'body'          => $request->body,
        ]);

        return back();
    }
}