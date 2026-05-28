@extends('layouts.app')
@section('title', $classroom->name . ' — ClassNova')

@section('content')
<div class="page-wrap">

    <div class="class-hero" style="--accent: {{ $classroom->color ?? '#00e5ff' }}">
        <div class="class-hero-inner">
            <a href="{{ route('classes.index') }}" class="back-link">← Classes</a>
            <h1 class="class-hero-title">{{ $classroom->name }}</h1>
            <p class="class-hero-teacher">{{ $classroom->teacher->name ?? 'Unknown teacher' }}</p>
            <div class="class-hero-meta">
                <span class="class-code-badge">Code: <strong>{{ $classroom->code }}</strong></span>
                <span>{{ $classroom->students->count() }} students</span>
            </div>
        </div>
        @if((auth()->user()->role === 'teacher' && auth()->user()->id === $classroom->teacher_id) || auth()->user()->role === 'admin')
            <div class="class-hero-actions">
                <a href="{{ route('assignments.create', ['class_id' => $classroom->id]) }}" class="btn-primary">+ Add Assignment</a>
                <a href="{{ route('classes.edit', $classroom) }}" class="btn-secondary">Edit Class</a>
                @if(auth()->user()->role === 'admin')
                    <form method="POST" action="{{ route('classes.destroy', $classroom) }}" onsubmit="return confirm('Delete this class? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete Class</button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="class-layout">

        <div class="class-feed">
            @if($assignments->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">◌</div>
                    <p>No assignments or posts yet.</p>
                </div>
            @else
                @foreach($assignments as $item)
                    <div class="feed-item feed-{{ $item->type }}">
                        <div class="feed-item-icon">
                            {{ $item->type === 'assignment' ? '◧' : '◈' }}
                        </div>
                        <div class="feed-item-body">
                            <div class="feed-item-head">
                                <a href="{{ route('assignments.show', $item) }}" class="feed-item-title">{{ $item->title }}</a>
                                <span class="feed-item-date">{{ $item->created_at->format('M j') }}</span>
                            </div>
                            @if($item->description)
                                <p class="feed-item-desc">{{ Str::limit($item->description, 120) }}</p>
                            @endif
                            <div class="feed-item-meta">
                                @if($item->due_date)
                                    <span class="due-badge {{ \Carbon\Carbon::parse($item->due_date)->isPast() ? 'due-past' : '' }}">
                                        Due {{ \Carbon\Carbon::parse($item->due_date)->format('M j, Y') }}
                                    </span>
                                @endif
                                @if($item->files->count())
                                    <span class="files-badge">{{ $item->files->count() }} file(s)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <aside class="class-sidebar">
            <div class="sidebar-card">
                <h3>Students <span class="count-badge">{{ $classroom->students->count() }}</span></h3>
                <ul class="student-list">
                    @foreach($classroom->students->take(8) as $student)
                        <li class="student-item">
                            @if($student->avatar)
                                <img src="{{ asset('storage/' . $student->avatar) }}" class="student-avatar" alt="">
                            @else
                                <div class="student-avatar-placeholder">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                            @endif
                            <span>{{ $student->name }}</span>
                        </li>
                    @endforeach
                    @if($classroom->students->count() > 8)
                        <li class="student-more">+{{ $classroom->students->count() - 8 }} more</li>
                    @endif
                </ul>
            </div>
        </aside>

    </div>
</div>
@endsection