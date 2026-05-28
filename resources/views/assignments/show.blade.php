@extends('layouts.app')
@section('title', $assignment->title . ' — ClassNova')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <a href="{{ route('classes.show', $assignment->classroom) }}" class="back-link">← {{ $assignment->classroom->name }}</a>
    </div>

    <div class="assignment-layout">

        <div class="assignment-main">

            <div class="assignment-card">
                <div class="assignment-card-header">
                    <div class="assignment-type-badge badge-{{ $assignment->type }}">
                        {{ ucfirst($assignment->type) }}
                    </div>
                    @if($assignment->due_date)
                        <div class="due-badge {{ \Carbon\Carbon::parse($assignment->due_date)->isPast() ? 'due-past' : '' }}">
                            Due {{ \Carbon\Carbon::parse($assignment->due_date)->format('D, M j') }}
                        </div>
                    @endif
                    @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'teacher' && auth()->user()->id === $assignment->classroom->assigned_teacher_id))
                        <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this assignment?')" style="margin-left:auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    @endif
                </div>
                <h1 class="assignment-title">{{ $assignment->title }}</h1>
                <p class="assignment-posted">Posted by {{ $assignment->classroom->assignedTeacher->name ?? $assignment->classroom->teacher->name }} · {{ $assignment->created_at->format('M j, Y') }}</p>

                @if($assignment->description)
                    <div class="assignment-body">{{ $assignment->description }}</div>
                @endif

                @if($assignment->files->where('user_id', $assignment->classroom->assigned_teacher_id ?? $assignment->classroom->teacher_id)->count())
                    <div class="file-list">
                        <h4>Attached Files</h4>
                        @foreach($assignment->files->where('user_id', $assignment->classroom->assigned_teacher_id ?? $assignment->classroom->teacher_id) as $file)
                            <a href="{{ asset('storage/' . $file->path) }}" class="file-item" target="_blank">
                                <span class="file-icon">◧</span>
                                <span class="file-name">{{ $file->original_name }}</span>
                                <span class="file-size">{{ number_format($file->size / 1024, 1) }} KB</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="comments-section">
                <h3 class="comments-title">Comments</h3>

                @foreach($assignment->comments()->with('user')->latest()->get() as $comment)
                    <div class="comment">
                        @if($comment->user->avatar && file_exists(storage_path('app/public/' . $comment->user->avatar)))
                            <img src="{{ asset('storage/' . $comment->user->avatar) }}" class="comment-avatar" alt="">
                        @else
                            <div class="comment-avatar-placeholder">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</div>
                        @endif
                        <div class="comment-body">
                            <div class="comment-head">
                                <span class="comment-author">{{ $comment->user->name }}</span>
                                <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="comment-text">{{ $comment->body }}</p>
                        </div>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('comments.store', $assignment) }}" class="comment-form">
                    @csrf
                    <div class="comment-input-row">
                        @if(auth()->user()->avatar && file_exists(storage_path('app/public/' . auth()->user()->avatar)))
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="comment-avatar" alt="">
                        @else
                            <div class="comment-avatar-placeholder">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        @endif
                        <input type="text" name="body" placeholder="Add a comment…" required>
                        <button type="submit" class="btn-primary">Post</button>
                    </div>
                </form>
            </div>

        </div>

        @if(auth()->user()->role === 'student')
        <aside class="assignment-sidebar">
            <div class="sidebar-card submission-card">
                <h3>Your Submission</h3>

                @php $submission = $assignment->submissions()->where('user_id', auth()->id())->first(); @endphp

                @if($submission)
                    <div class="submission-status status-submitted">Submitted</div>
                    @if($submission->grade !== null)
                        <div class="submission-grade">
                            <span class="grade-value">{{ $submission->grade }}</span>
                            <span class="grade-label">/ 10</span>
                        </div>
                        @if($submission->feedback)
                            <p class="grade-feedback">{{ $submission->feedback }}</p>
                        @endif
                    @endif
                    <div class="file-list">
                        @foreach($submission->files as $file)
                            <a href="{{ asset('storage/' . $file->path) }}" class="file-item" target="_blank">
                                <span class="file-icon">◧</span>
                                <span class="file-name">{{ $file->original_name }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="submission-status status-missing">Not submitted</div>
                @endif

                <form method="POST" action="{{ route('assignments.submit', $assignment) }}" enctype="multipart/form-data" class="submit-form">
                    @csrf
                    <div class="form-group">
                        <label for="files">Attach files</label>
                        <input type="file" id="files" name="files[]" multiple>
                    </div>
                    <button type="submit" class="btn-primary btn-full">
                        {{ $submission ? 'Resubmit' : 'Submit' }}
                    </button>
                </form>
            </div>
        </aside>
        @endif

        @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'teacher' && auth()->user()->id === $assignment->classroom->assigned_teacher_id))
        <aside class="assignment-sidebar">
            <div class="sidebar-card">
                <h3>Submissions <span class="count-badge">{{ $assignment->submissions->count() }}</span></h3>
                @foreach($assignment->submissions()->with(['user','files'])->get() as $sub)
                    <div class="submission-row">
                        <div class="submission-row-head">
                            <span class="sub-student">{{ $sub->user->name }}</span>
                            <span class="sub-date">{{ $sub->created_at->format('M j') }}</span>
                        </div>
                        @foreach($sub->files as $file)
                            <a href="{{ asset('storage/' . $file->path) }}" class="file-item small" target="_blank">
                                <span class="file-icon">◧</span>{{ $file->original_name }}
                            </a>
                        @endforeach
                        <form method="POST" action="{{ route('submissions.grade', $sub) }}" class="grade-form">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="grade" min="0" max="10" step="0.5" value="{{ $sub->grade }}" placeholder="Grade /10">
                            <input type="text" name="feedback" value="{{ $sub->feedback }}" placeholder="Feedback (optional)">
                            <button type="submit" class="btn-primary btn-sm">Save</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </aside>
        @endif

    </div>
</div>
@endsection