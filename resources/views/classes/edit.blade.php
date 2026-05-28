@extends('layouts.app')
@section('title', 'Edit Class — ClassNova')

@section('content')
<div class="page-wrap page-narrow">

    <div class="page-header">
        <a href="{{ route('classes.show', $classroom) }}" class="back-link">← {{ $classroom->name }}</a>
    </div>

    <div class="form-card">
        <h1 class="form-card-title">Edit Class</h1>

        <form method="POST" action="{{ route('classes.update', $classroom) }}" class="auth-form">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name">Class Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $classroom->name) }}"
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                    autofocus
                >
                @error('name') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description <span class="label-opt">(optional)</span></label>
                <textarea id="description" name="description" rows="3">{{ old('description', $classroom->description) }}</textarea>
                @error('description') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="assigned_teacher_id">Assign Teacher</label>
                <select id="assigned_teacher_id" name="assigned_teacher_id">
                    <option value="">— No teacher assigned —</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('assigned_teacher_id', $classroom->assigned_teacher_id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_teacher_id') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Accent Color</label>
                <div class="color-picker">
                    @foreach(['#00e5ff','#a78bfa','#34d399','#f59e0b','#f87171','#60a5fa'] as $color)
                        <label class="color-swatch">
                            <input type="radio" name="color" value="{{ $color }}" {{ old('color', $classroom->color) === $color ? 'checked' : '' }}>
                            <span style="background: {{ $color }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label>Class Code</label>
                <div class="class-code-badge" style="display:inline-block">{{ $classroom->code }}</div>
                <p style="font-size:0.75rem;color:var(--text-3);margin-top:4px">Share this code with students so they can join.</p>
            </div>

            <div class="form-actions">
                <a href="{{ route('classes.show', $classroom) }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

</div>
@endsection