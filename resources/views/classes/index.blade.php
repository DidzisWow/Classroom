@extends('layouts.app')
@section('title', 'Classes — ClassNova')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h1 class="page-title">Classes</h1>
            <p class="page-sub">All your enrolled and created classes</p>
        </div>
        <div class="page-actions">
            @if(auth()->user()->role === 'teacher' || auth()->user()->role === 'admin')
                <a href="{{ route('classes.create') }}" class="btn-primary">+ New Class</a>
            @else
                <button class="btn-secondary" onclick="document.getElementById('joinModal').classList.add('open')">Join Class</button>
            @endif
        </div>
    </div>

    @if($classes->isEmpty())
        <div class="empty-state full">
            <div class="empty-icon">⬡</div>
            <h3>No classes found</h3>
            @if(auth()->user()->role === 'teacher')
                <p>Create your first class to get started.</p>
                <a href="{{ route('classes.create') }}" class="btn-primary">Create Class</a>
            @else
                <p>Ask your teacher for a class invite code.</p>
            @endif
        </div>
    @else
        <div class="classes-grid">
            @foreach($classes as $class)
                <a href="{{ route('classes.show', $class) }}" class="class-tile">
                    <div class="class-tile-top" style="--accent: {{ $class->color ?? '#00e5ff' }}">
                        <div class="class-tile-icon">{{ strtoupper(substr($class->name, 0, 1)) }}</div>
                        <div class="class-tile-code">{{ $class->code }}</div>
                    </div>
                    <div class="class-tile-body">
                        <h3>{{ $class->name }}</h3>
                        <p>{{ $class->teacher->name ?? 'Unknown teacher' }}</p>
                        <div class="class-tile-meta">
                            <span>{{ $class->students_count }} students</span>
                            <span>{{ $class->assignments_count }} tasks</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</div>

{{-- Join Modal (students only) --}}
@if(auth()->user()->role === 'student')
<div class="modal-backdrop" id="joinModal">
    <div class="modal">
        <div class="modal-head">
            <h2>Join a Class</h2>
            <button class="modal-close" onclick="document.getElementById('joinModal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('classes.join') }}" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="code">Class Code</label>
                <input type="text" id="code" name="code" placeholder="e.g. 85XYQT" autocomplete="off" autofocus>
                @error('code') <span class="error-msg">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="btn-primary btn-full">Join</button>
        </form>
    </div>
</div>
@endif

@endsection