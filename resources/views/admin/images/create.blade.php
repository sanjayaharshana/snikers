@extends('admin.layout')

@section('title', 'Upload New Image')
@section('page-title', 'Upload New Image')
@section('page-subtitle', 'Add a new image to the campaign')

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('admin.images.index') }}">Images</a>
    <span class="breadcrumb-separator">/</span>
    <span>Upload</span>
</div>
@endsection

@section('content')
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-solid fa-upload"></i> Upload New Image</div>
        <div class="dashboard-card-subtitle">Add a new image for AI processing</div>
    </div>
    <div class="dashboard-card-body">
        <form method="POST" action="{{ route('admin.images.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="phone_number" class="form-label">Phone Number *</label>
                <input type="text" 
                       id="phone_number" 
                       name="phone_number" 
                       class="form-control @error('phone_number') is-invalid @enderror" 
                       value="{{ old('phone_number') }}" 
                       required 
                       placeholder="Enter phone number">
                @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="original_image" class="form-label">Image File *</label>
                <input type="file" 
                       id="original_image" 
                       name="original_image" 
                       class="form-control @error('original_image') is-invalid @enderror" 
                       accept="image/jpeg,image/png,image/jpg" 
                       required>
                <small class="form-text">Accepted formats: JPEG, PNG. Max size: 10MB</small>
                @error('original_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <div class="image-preview-container" id="imagePreview" style="display: none;">
                    <img id="previewImage" src="" alt="Preview" class="image-preview-large">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload Image</button>
                <a href="{{ route('admin.images.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #334155;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
    }

    .form-text {
        color: #64748b;
        font-size: 12px;
        margin-top: 5px;
    }

    .image-preview-container {
        text-align: center;
        margin-top: 20px;
        padding: 20px;
        border: 2px dashed #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .image-preview-large {
        max-width: 300px;
        max-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #1e40af;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #64748b;
        color: white;
    }

    .btn-secondary:hover {
        background: #475569;
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
document.getElementById('original_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
    }
});
</script>
@endpush
@endsection
