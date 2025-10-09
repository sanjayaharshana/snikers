@extends('admin.layout')

@section('title', 'Admin Login')
@section('page-title', '<i class="fa-solid fa-lock"></i> Admin Login')
@section('page-subtitle', 'Access the Snickers campaign admin panel')

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header {
        display: none;
    }

    .container {
        margin: 0;
        padding: 0;
        max-width: none;
    }

    .login-container {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    .logo {
        text-align: center;
        margin-bottom: 30px;
    }

    .logo h1 {
        color: #1e40af;
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .logo p {
        color: #64748b;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
        font-weight: bold;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .btn {
        width: 100%;
        background: #3b82f6;
        color: white;
        border: none;
        padding: 15px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: #1e40af;
        transform: translateY(-2px);
    }

    .error {
        color: #e74c3c;
        font-size: 14px;
        margin-top: 10px;
        text-align: center;
        background: #f8d7da;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #f5c6cb;
    }

    .back-link {
        text-align: center;
        margin-top: 20px;
    }

    .back-link a {
        color: #1e40af;
        text-decoration: none;
        font-size: 14px;
    }

    .back-link a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="logo">
        <h1>🍫 SNICKERS</h1>
        <p>Admin Panel</p>
    </div>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <div class="back-link">
        <a href="{{ route('snickers.campaign') }}">← Back to Campaign</a>
    </div>
</div>
@endsection
