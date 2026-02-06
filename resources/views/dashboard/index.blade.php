@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Dashboard</h5>
        <p class="mb-4">Welcome, {{ session('auth_user.name') }}</p>

        <div class="d-flex gap-2">
            <a href="/monitoring" class="btn btn-outline-primary">Go to Monitoring</a>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>
</div>
@endsection
