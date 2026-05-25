@extends('layouts.app')
@section('title', 'Create Class — ClassNova')

@section('content')
<div class="page-wrap page-narrow">

    <div class="page-header">
        <a href="{{ route('classes.index') }}" class="back-link">← Classes</a>
    </div>

    <div class="form-card">
        <h1 class="form-card-title">Create a New Class</h1>
        <p class="form-card-sub">Students will join using the auto-generated code.</p>

        <form method="POST" action="{{ route('classes.store') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Class Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                    placeholder="e.g. Mathematics Grade 10"
                    autofocus
                >
                @error('name') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description <span class="label-opt">(optional)</span></label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="What will students learn?"
                >{{ old('description') }}</textarea>
                @error('description') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Accent Color</label>
                <div class="color-picker">
                    @foreach(['#00e5ff','#a78bfa','#34d399','#f59e0b','#f87171','#60a5fa'] as $color)
                        <label class="color-swatch">
                            <input type="radio" name="color" value="{{ $color }}" {{ old('color', '#00e5ff') === $color ? 'checked' : '' }}>
                            <span style="background: {{ $color }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('classes.index') }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">Create Class</button>
            </div>
        </form>
    </div>

</div>
@endsection