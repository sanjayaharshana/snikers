@extends('admin.layout')

@section('title', 'View Image')
@section('page-title', 'View Image Details')
@section('page-subtitle', 'Image ID: ' . $image->id)

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span>View Image</span>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Image Details</h2>
    </div>
    <div class="card-body">
        <div class="detail-row">
            <div class="detail-label">ID:</div>
            <div class="detail-value">{{ $image->id }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Phone Number:</div>
            <div class="detail-value">
                <span class="phone-number">{{ $image->phone_number }}</span>
            </div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Created At:</div>
            <div class="detail-value">{{ $image->created_at->format('M d, Y H:i:s') }}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Updated At:</div>
            <div class="detail-value">{{ $image->updated_at->format('M d, Y H:i:s') }}</div>
        </div>
    </div>
</div>

<div class="image-gallery">
    <div class="image-card">
        <div class="image-header">Original Image</div>
        <div class="image-content">
            <img src="{{ Storage::url($image->original_image) }}" alt="Original Image">
            <br>
            <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'original']) }}" class="btn btn-success">Download Original</a>
        </div>
    </div>

    @if($image->sad_image)
    <div class="image-card">
        <div class="image-header">Sad Emotion</div>
        <div class="image-content">
            <img src="{{ Storage::url($image->sad_image) }}" alt="Sad Image">
            <br>
            <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'sad']) }}" class="btn btn-success">Download Sad</a>
        </div>
    </div>
    @endif

    @if($image->happy_image)
    <div class="image-card">
        <div class="image-header">Happy Emotion</div>
        <div class="image-content">
            <img src="{{ Storage::url($image->happy_image) }}" alt="Happy Image">
            <br>
            <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'happy']) }}" class="btn btn-success">Download Happy</a>
        </div>
    </div>
    @else
    <div class="image-card">
        <div class="image-header">Happy Emotion</div>
        <div class="image-content">
            <p>Happy image not generated yet</p>
            <form method="POST" action="{{ route('admin.generate-happy', $image->id) }}" onsubmit="return confirm('Queue happy photo generation for this record?')">
                @csrf
                <button type="submit" class="btn btn-success">Generate Happy</button>
            </form>
        </div>
    </div>
    @endif

    @if($image->framed_image)
    <div class="image-card">
        <div class="image-header">Framed Combined Image</div>
        <div class="image-content">
            <img src="{{ Storage::url($image->framed_image) }}" alt="Framed Combined Image">
            <br>
            <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'framed']) }}" class="btn btn-success">Download Framed</a>
        </div>
    </div>
    @elseif($image->sad_image && $image->happy_image)
    <div class="image-card">
        <div class="image-header">Framed Combined Image</div>
        <div class="image-content">
            <p>Generate framed image with both emotions</p>
            <a href="{{ route('admin.framedImage', $image->id) }}" class="btn btn-primary">View Framed Image</a>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .detail-row {
        display: flex;
        margin-bottom: 20px;
        align-items: center;
    }

    .detail-label {
        font-weight: bold;
        width: 150px;
        color: #8B4513;
    }

    .detail-value {
        flex: 1;
    }

    .image-header {
        background: #8B4513;
        color: white;
        padding: 15px;
        text-align: center;
        font-weight: bold;
    }

    .image-content {
        padding: 20px;
        text-align: center;
    }

    .image-content img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-bottom: 15px;
    }
</style>
@endpush
@endsection
