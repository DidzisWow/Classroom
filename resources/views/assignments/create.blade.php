@extends('layouts.app')
@section('title', 'New Assignment — ClassNova')

@section('content')
<div class="page-wrap page-narrow">

    <div class="page-header">
        <a href="{{ route('classes.show', $class) }}" class="back-link">← {{ $class->name }}</a>
    </div>

    <div class="form-card">
        <h1 class="form-card-title">Create New Post</h1>
        <p class="form-card-sub">Create an assignment or announcement for your students</p>

        <form method="POST" action="{{ route('assignments.store') }}" enctype="multipart/form-data" class="auth-form">
            @csrf
            <input type="hidden" name="classroom_id" value="{{ $class->id }}">

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Post Type</label>
                    <select id="type" name="type" class="{{ $errors->has('type') ? 'is-invalid' : '' }}">
                        <option value="assignment" {{ old('type') === 'assignment' ? 'selected' : '' }}>📝 Assignment (requires submission)</option>
                        <option value="announcement" {{ old('type') === 'announcement' ? 'selected' : '' }}>📢 Announcement (no submission)</option>
                    </select>
                    @error('type') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" id="dueDateGroup">
                    <label for="due_date">Due Date <span class="label-opt">(for assignments)</span></label>
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
                    placeholder="Enter title..."
                    autofocus
                >
                @error('title') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description / Instructions</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Describe the task or provide instructions..."
                >{{ old('description') }}</textarea>
                @error('description') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="files">Attach Files <span class="label-opt">(optional, max 20MB per file)</span></label>
                <input type="file" id="files" name="files[]" multiple accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip">
                <small style="color: var(--text-3); font-size: 0.7rem;">You can attach multiple files: PDF, DOC, TXT, JPG, PNG, ZIP</small>
                @error('files.*') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('classes.show', $class) }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">Publish Post</button>
            </div>
        </form>
    </div>

</div>

<script>
    // Show/hide due date field based on post type
    const typeSelect = document.getElementById('type');
    const dueDateGroup = document.getElementById('dueDateGroup');
    
    function toggleDueDate() {
        if (typeSelect.value === 'announcement') {
            dueDateGroup.style.display = 'none';
        } else {
            dueDateGroup.style.display = 'block';
        }
    }
    
    typeSelect.addEventListener('change', toggleDueDate);
    toggleDueDate();
</script>

@endsection