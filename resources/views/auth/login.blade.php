@extends('layouts.auth')
@section('title', 'Login — ClassNova')

@section('content')
<div class="auth-card">
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-sub">Sign in to your ClassNova account</p>

    @if($errors->any())
        <div class="form-error-banner">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="you@example.com"
                autocomplete="email"
                autofocus
            >
            @error('email')
                <span class="error-msg">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                placeholder="••••••••"
                autocomplete="current-password"
            >
            @error('password')
                <span class="error-msg">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-check">
            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn-primary btn-full">Sign In</button>
    </form>

    <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
</div>
@endsection