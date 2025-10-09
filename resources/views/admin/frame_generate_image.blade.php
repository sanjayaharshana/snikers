@extends('admin.layout')

@section('title', 'View Image')
@section('page-title', 'View Image Details')
@section('page-subtitle', 'Image ID: ' . $image->id)

@section('breadcrumb')
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span>View Image</span>
        <span class="breadcrumb-separator">/</span>
        <span>Generated Images</span>
    </div>
@endsection

@section('content')
    <div class="card">

        <div id="exportable-card" style="border: 3px solid #000; display: flex; height: 720px;">
            <div style="flex: 1;position: relative;">
                <div id="left-container" style="height: -webkit-fill-available;background: url('{{ Storage::url($image->sad_image) }}') no-repeat;background-size: 100% 100%;background-position: center">
                    <div id="exportable-image-left" style="background: url('{{url('framebox_left.png')}}');height: 100%;background-size: contain;background-position: center;background-repeat: no-repeat"></div>
                </div>
            </div>
            <div style="flex: 1; position: relative;">
                <div id="right-container" style="height: -webkit-fill-available;background: url('{{ Storage::url($image->happy_image) }}') no-repeat;background-size: 100% 100%;background-position: center">
                    <div id="exportable-image-right" style="background: url('{{url('framebox_right.png')}}');height: 100%;background-size: contain;background-position: center;background-repeat: no-repeat"></div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <button id="export-full-btn" class="btn btn-primary">Export Full Card as PNG</button>
        </div>
    </div>

    <script>
        document.getElementById('export-full-btn').addEventListener('click', function() {
            const card = document.getElementById('exportable-card');
            const leftContainer = document.getElementById('left-container');
            const rightContainer = document.getElementById('right-container');
            const leftFrame = document.getElementById('exportable-image-left');
            const rightFrame = document.getElementById('exportable-image-right');

            // Create a canvas element
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Set canvas dimensions to match the card
            canvas.width = card.offsetWidth;
            canvas.height = card.offsetHeight;

            // Get background image URLs
            const leftContainerStyles = window.getComputedStyle(leftContainer);
            const rightContainerStyles = window.getComputedStyle(rightContainer);
            const leftFrameStyles = window.getComputedStyle(leftFrame);
            const rightFrameStyles = window.getComputedStyle(rightFrame);

            const leftContainerBgMatch = leftContainerStyles.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
            const rightContainerBgMatch = rightContainerStyles.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
            const leftFrameBgMatch = leftFrameStyles.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
            const rightFrameBgMatch = rightFrameStyles.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);

            if (leftContainerBgMatch && rightContainerBgMatch && leftFrameBgMatch && rightFrameBgMatch) {
                const leftContainerImageUrl = leftContainerBgMatch[1];
                const rightContainerImageUrl = rightContainerBgMatch[1];
                const leftFrameImageUrl = leftFrameBgMatch[1];
                const rightFrameImageUrl = rightFrameBgMatch[1];

                let loadedImages = 0;
                const totalImages = 4;

                const leftContainerImg = new Image();
                const rightContainerImg = new Image();
                const leftFrameImg = new Image();
                const rightFrameImg = new Image();

                leftContainerImg.crossOrigin = 'anonymous';
                rightContainerImg.crossOrigin = 'anonymous';
                leftFrameImg.crossOrigin = 'anonymous';
                rightFrameImg.crossOrigin = 'anonymous';

                function onImageLoad() {
                    loadedImages++;
                    if (loadedImages === totalImages) {
                        const halfWidth = canvas.width / 2;

                        console.log(canvas.height/2);

                        // Draw left side background
                        ctx.drawImage(leftContainerImg, 0, 0, halfWidth, 600);

                        // Draw right side background
                        ctx.drawImage(rightContainerImg, halfWidth, 0, halfWidth, 600);

                        // Draw left frame on top
                        ctx.drawImage(leftFrameImg, 0, 0, halfWidth, canvas.height);

                        // Draw right frame on top
                        ctx.drawImage(rightFrameImg, halfWidth, 0, halfWidth, canvas.height);


                        // Convert canvas to PNG and download
                        canvas.toBlob(function(blob) {
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'full-card-' + Date.now() + '.png';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        }, 'image/png');
                    }
                }

                leftContainerImg.onload = onImageLoad;
                rightContainerImg.onload = onImageLoad;
                leftFrameImg.onload = onImageLoad;
                rightFrameImg.onload = onImageLoad;

                leftContainerImg.onerror = function() {
                    alert('Error loading left background image. Please try again.');
                };

                rightContainerImg.onerror = function() {
                    alert('Error loading right background image. Please try again.');
                };

                leftFrameImg.onerror = function() {
                    alert('Error loading left frame image. Please try again.');
                };

                rightFrameImg.onerror = function() {
                    alert('Error loading right frame image. Please try again.');
                };

                leftContainerImg.src = leftContainerImageUrl;
                rightContainerImg.src = rightContainerImageUrl;
                leftFrameImg.src = leftFrameImageUrl;
                rightFrameImg.src = rightFrameImageUrl;
            } else {
                alert('No background images found to export.');
            }
        });
    </script>
@endsection
