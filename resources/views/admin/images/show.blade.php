@extends('admin.layout')

@section('title', 'View Image Details')
@section('page-title', 'View Image Details')
@section('page-subtitle', 'Image ID: ' . $image->id)

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('admin.images.index') }}">Images</a>
    <span class="breadcrumb-separator">/</span>
    <span>View #{{ $image->id }}</span>
</div>
@endsection

@section('content')
<div class="dashboard-grid">
    <!-- Image Details -->
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-regular fa-rectangle-list"></i> Image Information</div>
            <div class="dashboard-card-subtitle">Basic details and metadata</div>
        </div>
        <div class="dashboard-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Image ID:</label>
                    <span>#{{ $image->id }}</span>
                </div>
                <div class="info-item">
                    <label>Phone Number:</label>
                    <span class="phone-number">{{ $image->phone_number }}</span>
                </div>
                <div class="info-item">
                    <label>Status:</label>
                    <span>
                        @if($image->sad_image && $image->happy_image)
                            <span class="status-badge status-success">Complete</span>
                        @elseif($image->sad_image || $image->happy_image)
                            <span class="status-badge status-warning">Processing</span>
                        @else
                            <span class="status-badge status-info">Pending</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <label>Created:</label>
                    <span>{{ $image->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <label>Updated:</label>
                    <span>{{ $image->updated_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Status -->
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-gear"></i> Processing Status</div>
            <div class="dashboard-card-subtitle">AI processing information</div>
        </div>
        <div class="dashboard-card-body">
            @php
                $emotionData = json_decode($image->emotion_data, true) ?? [];
            @endphp
            <div class="info-grid">
                <div class="info-item">
                    <label>Sad Processed:</label>
                    <span>
                        @if($image->sad_image)
                            <span class="status-badge status-success">Yes</span>
                        @else
                            <span class="status-badge status-danger">No</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <label>Happy Processed:</label>
                    <span>
                        @if($image->happy_image)
                            <span class="status-badge status-success">Yes</span>
                        @else
                            <span class="status-badge status-danger">No</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <label>Campaign Complete:</label>
                    <span>
                        @if($emotionData['campaign_completed'] ?? false)
                            <span class="status-badge status-success">Yes</span>
                        @else
                            <span class="status-badge status-warning">No</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <label>Job Status:</label>
                    <span class="status-badge status-{{ $emotionData['job_status'] ?? 'pending' }}">
                        {{ ucfirst($emotionData['job_status'] ?? 'pending') }}
                    </span>
                </div>
                @if(isset($emotionData['job_updated_at']))
                <div class="info-item">
                    <label>Last Updated:</label>
                    <span>{{ \Carbon\Carbon::parse($emotionData['job_updated_at'])->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Image Gallery -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-regular fa-images"></i> Image Gallery</div>
        <div class="dashboard-card-subtitle">View all generated images</div>
    </div>
    <div class="dashboard-card-body">
        <div class="image-gallery">
            <!-- Original Image -->
            <div class="image-card">
                <div class="image-card-header">
                    <h4>Original Image</h4>
                    @if($image->original_image)
                        <a href="{{ route('admin.images.download', ['image' => $image->id, 'type' => 'original']) }}" class="btn btn-info btn-sm">Download</a>
                    @endif
                </div>
                <div class="image-card-body">
                    @if($image->original_image)
                        <div class="gallery-image-container">
                            <img src="{{ Storage::url($image->original_image) }}" 
                                 alt="Original" 
                                 class="gallery-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="gallery-image-placeholder" style="display: none;">
                                <i class="fa-solid fa-image"></i>
                                <span>Broken Image</span>
                            </div>
                        </div>
                    @else
                        <div class="no-image">No original image</div>
                    @endif
                </div>
            </div>

            <!-- Sad Image -->
            <div class="image-card">
                <div class="image-card-header">
                    <h4>Sad Image</h4>
                    <div class="image-actions">
                        @if($image->sad_image)
                            <a href="{{ route('admin.images.download', ['image' => $image->id, 'type' => 'sad']) }}" class="btn btn-info btn-sm">Download</a>
                        @elseif($image->original_image)
                            <form method="POST" action="{{ route('admin.images.generate-sad', $image->id) }}" style="display: inline;" onsubmit="return confirm('Queue sad photo generation?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Generate Sad</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="image-card-body">
                    @if($image->sad_image)
                        <div class="gallery-image-container">
                            <img src="{{ Storage::url($image->sad_image) }}" 
                                 alt="Sad" 
                                 class="gallery-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="gallery-image-placeholder" style="display: none;">
                                <i class="fa-solid fa-image"></i>
                                <span>Broken Image</span>
                            </div>
                        </div>
                    @else
                        <div class="no-image">Not generated yet</div>
                    @endif
                </div>
            </div>

            <!-- Happy Image -->
            <div class="image-card">
                <div class="image-card-header">
                    <h4>Happy Image</h4>
                    <div class="image-actions">
                        @if($image->happy_image)
                            <a href="{{ route('admin.images.download', ['image' => $image->id, 'type' => 'happy']) }}" class="btn btn-info btn-sm">Download</a>
                        @elseif($image->original_image)
                            <form method="POST" action="{{ route('admin.images.generate-happy', $image->id) }}" style="display: inline;" onsubmit="return confirm('Queue happy photo generation?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Generate Happy</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="image-card-body">
                    @if($image->happy_image)
                        <div class="gallery-image-container">
                            <img src="{{ Storage::url($image->happy_image) }}" 
                                 alt="Happy" 
                                 class="gallery-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="gallery-image-placeholder" style="display: none;">
                                <i class="fa-solid fa-image"></i>
                                <span>Broken Image</span>
                            </div>
                        </div>
                    @else
                        <div class="no-image">Not generated yet</div>
                    @endif
                </div>
            </div>

            <!-- Framed Image -->
            @if($image->framed_image)
            <div class="image-card">
                <div class="image-card-header">
                    <h4>Framed Image</h4>
                    <a href="{{ route('admin.images.download', ['image' => $image->id, 'type' => 'framed']) }}" class="btn btn-info btn-sm">Download</a>
                </div>
                <div class="image-card-body">
                    <div class="gallery-image-container">
                        <img src="{{ Storage::url($image->framed_image) }}" 
                             alt="Framed" 
                             class="gallery-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="gallery-image-placeholder" style="display: none;">
                            <i class="fa-solid fa-image"></i>
                            <span>Broken Image</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Actions -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-solid fa-screwdriver-wrench"></i> Actions</div>
        <div class="dashboard-card-subtitle">Manage this image</div>
    </div>
    <div class="dashboard-card-body">
        <div class="action-buttons">
            <a href="{{ route('admin.images.edit', $image->id) }}" class="btn btn-warning">Edit Image</a>
            <a href="{{ route('admin.images.index') }}" class="btn btn-secondary">Back to List</a>
            <form method="POST" action="{{ route('admin.images.destroy', $image->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete Image</button>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-item label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item span {
        font-size: 14px;
        color: #334155;
        font-weight: 500;
    }

    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .image-card {
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .image-card-header {
        padding: 15px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .image-card-header h4 {
        margin: 0;
        font-size: 16px;
        color: #334155;
        font-weight: 600;
    }

    .image-card-body {
        padding: 20px;
        text-align: center;
    }

    .gallery-image-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .gallery-image {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .gallery-image-placeholder {
        width: 100%;
        height: 200px;
        background: #f1f5f9;
        border: 2px dashed #e2e8f0;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 14px;
    }

    .gallery-image-placeholder i {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .gallery-image-placeholder span {
        font-size: 12px;
        font-weight: 500;
    }

    .no-image {
        padding: 40px 20px;
        color: #64748b;
        font-style: italic;
        background: #f8fafc;
        border-radius: 8px;
        border: 2px dashed #e2e8f0;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-warning { background: #f59e0b; color: white; }
    .btn-warning:hover { background: #d97706; }
    .btn-secondary { background: #64748b; color: white; }
    .btn-secondary:hover { background: #475569; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; }
    .btn-info { background: #06b6d4; color: white; }
    .btn-info:hover { background: #0891b2; }
    .btn-success { background: #10b981; color: white; }
    .btn-success:hover { background: #059669; }
</style>
@endpush
@endsection
