@extends('layouts.app')
@section('title', 'New Assignment — ClassNova')

@section('content')
<div class="page-wrap page-narrow">

    <div class="page-header">
        <a href="{{ route('classes.show', $class) }}" class="back-link">← {{ $class->name }}</a>
    </div>

    <div class="form-card">
        <h1 class="form-card-title">New Assignment</h1>

        <form method="POST" action="{{ route('assignments.store') }}" enctype="multipart/form-data" class="auth-form">
            @csrf
            <input type="hidden" name="classroom_id" value="{{ $class->id }}">

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" class="{{ $errors->has('type') ? 'is-invalid' : '' }}">
                        <option value="assignment" {{ old('type') === 'assignment' ? 'selected' : '' }}>Assignment</option>
                        <option value="announcement" {{ old('type') === 'announcement' ? 'selected' : '' }}>Announcement</option>
                    </select>
                    @error('type') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date <span class="label-opt">(optional)</span></label>
                    <input type="datetime-local" id="due_date" name="due_date" value="{{ old('due_date') }}">
                    @error('due_date') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="title">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    class="{{ $errors->has('title') ? 'is-invalid' : '' }}"
                    placeholder="Assignment title"
                    autofocus
                >
                @error('title') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Instructions <span class="label-opt">(optional)</span></label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    placeholder="Describe the task..."
                >{{ old('description') }}</textarea>
                @error('description') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="files">Attach Files <span class="label-opt">(optional)</span></label>
                <input type="file" id="files" name="files[]" multiple>
                @error('files.*') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('classes.show', $class) }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">Post Assignment</button>
            </div>
        </form>
    </div>

</div>
@endsection