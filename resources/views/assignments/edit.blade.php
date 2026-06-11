@extends('layouts.app')
@section('title', 'Edit ' . $assignment->title . ' — ClassNova')

@section('content')
<div class="page-wrap page-narrow">

    <div class="page-header">
        <a href="{{ route('assignments.show', $assignment) }}" class="back-link">← {{ $assignment->title }}</a>
    </div>

    <div class="form-card">
        <h1 class="form-card-title">Edit {{ ucfirst($assignment->type) }}</h1>
        <p class="form-card-sub">Update your {{ $assignment->type }}</p>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Hidden file delete forms OUTSIDE the main form (HTML doesn't allow nested forms) --}}
        @if($assignment->files->count())
            @foreach($assignment->files as $file)
                <form
                    id="delete-file-form-{{ $file->id }}"
                    method="POST"
                    action="{{ route('assignments.delete-file', ['assignment' => $assignment->id, 'file' => $file->id]) }}"
                    style="display: none;"
                >
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif

        <form id="main-update-form" method="POST" action="{{ route('assignments.update', $assignment) }}" enctype="multipart/form-data" class="auth-form">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Post Type</label>
                    <select id="type" name="type" class="{{ $errors->has('type') ? 'is-invalid' : '' }}">
                        <option value="assignment" {{ old('type', $assignment->type) === 'assignment' ? 'selected' : '' }}>📝 Assignment (requires submission)</option>
                        <option value="announcement" {{ old('type', $assignment->type) === 'announcement' ? 'selected' : '' }}>📢 Announcement (no submission)</option>
                    </select>
                    @error('type') <span class="error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" id="dueDateGroup">
                    <label for="due_date">Due Date <span class="label-opt">(for assignments)</span></label>
                    <input type="datetime-local" id="due_date" name="due_date" value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d\TH:i') : '') }}">
                    @error('due_date') <span class="error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="title">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $assignment->title) }}"
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
                >{{ old('description', $assignment->description) }}</textarea>
                @error('description') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            {{-- Existing Files --}}
            @if($assignment->files->count())
                <div class="form-group">
                    <label>Current Attachments</label>
                    <div class="file-list">
                        @foreach($assignment->files as $file)
                            <div class="file-item">
                                <span class="file-icon">◧</span>
                                <a href="{{ asset('storage/' . $file->path) }}" class="file-name" target="_blank">{{ $file->original_name }}</a>
                                <span class="file-size">{{ number_format($file->size / 1024, 1) }} KB</span>
                                <button
                                    type="button"
                                    class="btn-danger btn-sm"
                                    onclick="if(confirm('Are you sure you want to delete this file?')) document.getElementById('delete-file-form-{{ $file->id }}').submit();"
                                >Remove</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="new_files">Add New Files <span class="label-opt">(optional, max 20MB per file)</span></label>
                <input type="file" id="new_files" name="new_files[]" multiple accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip">
                <small style="color: var(--text-3); font-size: 0.7rem;">You can attach multiple files: PDF, DOC, TXT, JPG, PNG, ZIP</small>
                @error('new_files.*') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('assignments.show', $assignment) }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">Update {{ ucfirst($assignment->type) }}</button>
            </div>
        </form>
    </div>

</div>

<script>
    const typeSelect = document.getElementById('type');
    const dueDateGroup = document.getElementById('dueDateGroup');
    
    function toggleDueDate() {
        if (typeSelect && dueDateGroup) {
            if (typeSelect.value === 'announcement') {
                dueDateGroup.style.display = 'none';
            } else {
                dueDateGroup.style.display = 'block';
            }
        }
    }
    
    if (typeSelect) {
        typeSelect.addEventListener('change', toggleDueDate);
        toggleDueDate();
    }
</script>

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
        padding: 4px 12px;
        font-size: 0.75rem;
        background: rgba(248, 113, 113, 0.15);
        border: 1px solid rgba(248, 113, 113, 0.3);
        border-radius: 6px;
        cursor: pointer;
    }
    
    .btn-danger.btn-sm:hover {
        background: rgba(248, 113, 113, 0.3);
    }
    
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: rgba(52, 211, 153, 0.1);
        border: 1px solid rgba(52, 211, 153, 0.25);
        color: #34d399;
    }
    
    .alert-error {
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.25);
        color: #f87171;
    }
</style>

@endsection