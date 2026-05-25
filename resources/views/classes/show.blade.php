@extends('layouts.app')
@section('title', $class->name . ' — ClassNova')

@section('content')
<div class="page-wrap">

    <div class="class-hero" style="--accent: {{ $class->color ?? '#00e5ff' }}">
        <div class="class-hero-inner">
            <a href="{{ route('classes.index') }}" class="back-link">← Classes</a>
            <h1 class="class-hero-title">{{ $class->name }}</h1>
            <p class="class-hero-teacher">{{ $class->teacher->name ?? 'Unknown teacher' }}</p>
            <div class="class-hero-meta">
                <span class="class-code-badge">Code: <strong>{{ $class->code }}</strong></span>
                <span>{{ $class->students->count() }} students</span>
            </div>
        </div>
        @if(auth()->user()->role === 'teacher' && auth()->user()->id === $class->teacher_id || auth()->user()->role === 'admin')
            <div class="class-hero-actions">
                <a href="{{ route('assignments.create', ['class_id' => $class->id]) }}" class="btn-primary">+ Add Assignment</a>
                <a href="{{ route('classes.edit', $class) }}" class="btn-secondary">Edit Class</a>
            </div>
        @endif
    </div>

    <div class="class-layout">

        {{-- Assignments/Posts --}}
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

        {{-- Sidebar --}}
        <aside class="class-sidebar">
            <div class="sidebar-card">
                <h3>Students <span class="count-badge">{{ $class->students->count() }}</span></h3>
                <ul class="student-list">
                    @foreach($class->students->take(8) as $student)
                        <li class="student-item">
                            @if($student->avatar)
                                <img src="{{ asset('storage/' . $student->avatar) }}" class="student-avatar" alt="">
                            @else
                                <div class="student-avatar-placeholder">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                            @endif
                            <span>{{ $student->name }}</span>
                        </li>
                    @endforeach
                    @if($class->students->count() > 8)
                        <li class="student-more">+{{ $class->students->count() - 8 }} more</li>
                    @endif
                </ul>
            </div>
        </aside>

    </div>
</div>
@endsection