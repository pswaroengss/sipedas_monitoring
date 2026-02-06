@extends('layouts.app')

@section('title', 'Login')

@push('head')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<script>document.body.classList.add('auth-page');</script>
<div class="auth-wrap d-flex align-items-center justify-content-center">
    <div class="col-11 col-sm-8 col-md-5 col-lg-4">
        <div class="card auth-card">
            <div class="card-body p-4">
                <div class="text-center">
                    <div class="auth-logo mb-3" aria-hidden="true">
                        <svg viewBox="0 0 64 64" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <ellipse class="db-fill" cx="32" cy="14" rx="16" ry="6"></ellipse>
                            <path class="db" d="M16 14v24c0 3.3 7.2 6 16 6s16-2.7 16-6V14"></path>
                            <ellipse class="db" cx="32" cy="26" rx="16" ry="6"></ellipse>
                            <ellipse class="db" cx="32" cy="38" rx="16" ry="6"></ellipse>
                            <path class="pulse" d="M16 52h8l3-6 4 10 4-10 3 6h12"></path>
                        </svg>
                    </div>
                    <h4 class="auth-title mb-1">SIPEDAS</h4>
                    <div class="text-dark fw-semibold">Monitoring Console</div>
                    <div class="text-muted small">Operations Intelligence Console</div>
                </div>
                <div class="auth-divider"></div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="/login">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-login" id="btnLogin">
                        <span class="label">Login</span>
                        <span class="loading" aria-hidden="true">
                            <span class="pacman"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('form[action="/login"]');
        var btn = document.getElementById('btnLogin');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.classList.add('is-loading');
                btn.setAttribute('disabled', 'disabled');
            });
        }
    });
</script>
@endsection
