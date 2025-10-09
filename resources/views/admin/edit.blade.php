@extends('admin.layout')

@section('title', 'Edit Image')
@section('page-title', 'Edit Image')
@section('page-subtitle', 'Image ID: ' . $image->id)

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('admin.show', $image->id) }}">View Image</a>
    <span class="breadcrumb-separator">/</span>
    <span>Edit</span>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Edit Image #{{ $image->id }}</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.update', $image->id) }}">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" class="form-control" value="{{ old('phone_number', $image->phone_number) }}" required>
                @error('phone_number')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="image-preview">
                <div class="preview-card">
                    <img src="{{ Storage::url($image->original_image) }}" alt="Original">
                    <div class="preview-label">Original</div>
                </div>
                @if($image->sad_image)
                <div class="preview-card">
                    <img src="{{ Storage::url($image->sad_image) }}" alt="Sad">
                    <div class="preview-label">Sad Emotion</div>
                </div>
                @endif
                @if($image->happy_image)
                <div class="preview-card">
                    <img src="{{ Storage::url($image->happy_image) }}" alt="Happy">
                    <div class="preview-label">Happy Emotion</div>
                </div>
                @endif
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">Update Image</button>
                <a href="{{ route('admin.show', $image->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .error {
        color: #e74c3c;
        font-size: 14px;
        margin-top: 5px;
    }

    .image-preview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .preview-card {
        text-align: center;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
    }

    .preview-card img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .preview-label {
        font-weight: bold;
        color: #8B4513;
    }
</style>
@endpush
@endsection
