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
                        <div style="display: flex; gap: 8px; margin-left: auto;">
                            <a href="{{ route('assignments.edit', $assignment) }}" class="btn-secondary btn-sm">✎ Edit</a>
                            <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this assignment?')" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
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
                            <div class="file-item">
                                <span class="file-icon">◧</span>
                                <a href="{{ asset('storage/' . $file->path) }}" class="file-name" target="_blank">{{ $file->original_name }}</a>
                                <span class="file-size">{{ number_format($file->size / 1024, 1) }} KB</span>
                            </div>
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
                    <div class="submission-status status-submitted">Submitted ({{ $submission->updated_at->format('M j, H:i') }})</div>
                    
                    @if($submission->grade !== null)
                        <div class="submission-grade">
                            <span class="grade-value">{{ $submission->grade }}</span>
                            <span class="grade-label">/ 10</span>
                        </div>
                        @if($submission->feedback)
                            <p class="grade-feedback">{{ $submission->feedback }}</p>
                        @endif
                    @endif
                    
                    @if($submission->files->count())
                        <div class="file-list">
                            <h4>Your Files</h4>
                            @foreach($submission->files as $file)
                                <div class="file-item">
                                    <span class="file-icon">◧</span>
                                    <a href="{{ asset('storage/' . $file->path) }}" class="file-name" target="_blank">{{ $file->original_name }}</a>
                                    <span class="file-size">{{ number_format($file->size / 1024, 1) }} KB</span>
                                    <form method="POST" action="{{ route('submissions.delete-file', [$submission, $file]) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Delete this file?')">✕</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="submission-status status-missing">Not submitted</div>
                @endif

                <form method="POST" action="{{ route('assignments.submit', $assignment) }}" enctype="multipart/form-data" class="submit-form">
                    @csrf
                    <div class="form-group">
                        <label for="files">Attach files</label>
                        <input type="file" id="files" name="files[]" multiple accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip">
                        <small style="color: var(--text-3); font-size: 0.7rem;">Max 20MB per file</small>
                    </div>
                    <button type="submit" class="btn-primary btn-full">
                        {{ $submission ? 'Update Submission' : 'Submit' }}
                    </button>
                </form>
            </div>
        </aside>
        @endif

        @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'teacher' && auth()->user()->id === $assignment->classroom->assigned_teacher_id))
        <aside class="assignment-sidebar">
            <div class="sidebar-card">
                <h3>Submissions <span class="count-badge">{{ $assignment->submissions->count() }}</span></h3>
                
                @if($assignment->submissions->isEmpty())
                    <p style="color: var(--text-3); text-align: center; padding: 20px;">No submissions yet</p>
                @else
                    @foreach($assignment->submissions()->with(['user','files'])->get() as $sub)
                        <div class="submission-row">
                            <div class="submission-row-head">
                                <span class="sub-student">{{ $sub->user->name }}</span>
                                <span class="sub-date">{{ $sub->created_at->format('M j, H:i') }}</span>
                            </div>
                            
                            @if($sub->files->count())
                                @foreach($sub->files as $file)
                                    <a href="{{ asset('storage/' . $file->path) }}" class="file-item small" target="_blank">
                                        <span class="file-icon">◧</span>{{ $file->original_name }}
                                    </a>
                                @endforeach
                            @endif
                            
                            <form method="POST" action="{{ route('submissions.grade', $sub) }}" class="grade-form">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="grade" min="0" max="10" step="0.5" value="{{ $sub->grade }}" placeholder="Grade /10">
                                <input type="text" name="feedback" value="{{ $sub->feedback }}" placeholder="Feedback (optional)">
                                <button type="submit" class="btn-primary btn-sm">Save</button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        </aside>
        @endif

    </div>
</div>

<style>
    .file-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: var(--bg-3);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.85rem;
        margin-bottom: 6px;
        transition: all var(--transition);
    }
    
    .file-item .file-name {
        flex: 1;
        color: var(--text);
        text-decoration: none;
    }
    
    .file-item .file-name:hover {
        color: var(--accent);
    }
    
    .file-item .file-size {
        font-size: 0.75rem;
        color: var(--text-3);
    }
    
    .btn-danger.btn-sm {
        padding: 2px 8px;
        font-size: 0.75rem;
        background: rgba(248, 113, 113, 0.15);
        border: 1px solid rgba(248, 113, 113, 0.3);
    }
    
    .btn-danger.btn-sm:hover {
        background: rgba(248, 113, 113, 0.3);
    }
    
    .btn-secondary.btn-sm {
        padding: 5px 12px;
        font-size: 0.8rem;
    }
    
    .submission-row {
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }
    
    .submission-row:last-child {
        border-bottom: none;
    }
    
    .submission-row-head {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .sub-student {
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .sub-date {
        font-size: 0.75rem;
        color: var(--text-3);
    }
    
    .grade-form {
        display: flex;
        gap: 6px;
        margin-top: 8px;
        flex-wrap: wrap;
    }
    
    .grade-form input {
        flex: 1;
        min-width: 80px;
        background: var(--bg-3);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 6px 10px;
        color: var(--text);
        font-size: 0.8rem;
        outline: none;
    }
    
    .grade-form input:focus {
        border-color: var(--accent);
    }
</style>

@endsection