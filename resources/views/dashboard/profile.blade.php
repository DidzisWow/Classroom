@extends('layouts.app')
@section('title', 'Profile — ClassNova')

@section('content')
<div class="page-wrap page-narrow">

    <div class="page-header">
        <h1 class="page-title">Profile</h1>
    </div>

    <div class="form-card">
        <div class="profile-avatar-section">
            @if(auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="profile-avatar" alt="">
            @else
                <div class="profile-avatar-placeholder">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="auth-form">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', auth()->user()->name) }}"
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                >
                @error('name') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', auth()->user()->email) }}"
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                >
                @error('email') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="avatar">Profile Picture <span class="label-opt">(optional)</span></label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
                @error('avatar') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <hr class="form-divider">

            <div class="form-group">
                <label for="current_password">Current Password <span class="label-opt">(to change password)</span></label>
                <input type="password" id="current_password" name="current_password" placeholder="••••••••">
                @error('current_password') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="••••••••">
                @error('new_password') <span class="error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="••••••••">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

</div>
@endsection