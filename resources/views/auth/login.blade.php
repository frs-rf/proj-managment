@extends('layouts.auth')

@section('content')
<div class="auth-card">
    <div class="auth-logo">
        <i class="bi bi-kanban"></i> PM Tracker
    </div>
    
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@company.com">

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-muted text-decoration-none small" href="{{ route('password.request') }}">
                        Forgot?
                    </a>
                @endif
            </div>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label text-muted" for="remember">
                Keep me signed in
            </label>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                Sign In
            </button>
        </div>
    </form>
</div>
@endsection
