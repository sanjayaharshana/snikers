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

        <div style="border: 3px solid #000; display: flex; height: 720px;">
            <div style="flex: 1;position: relative;">
                <div id="left-container" style="height: -webkit-fill-available;background: url('{{ Storage::url($image->sad_image) }}') no-repeat -160px -100px;background-size: cover">
                    <div id="exportable-image-left" style="background: url('{{url('framebox_left.png')}}');height: 100%;background-size: contain;background-position: center;background-repeat: no-repeat"></div>
                </div>
            </div>
            <div style="flex: 1; position: relative;">
                <div id="right-container" style="height: -webkit-fill-available;background: url('{{ Storage::url($image->sad_image) }}') no-repeat -160px -100px;background-size: cover">
                    <div id="exportable-image-right" style="background: url('{{url('framebox_right.png')}}');height: 100%;background-size: contain;background-position: center;background-repeat: no-repeat"></div>
                </div>
            </div>
        </div>

        <div style="display: flex; margin-top: 20px;">
            <div style="flex: 1; text-align: center; padding-right: 10px;">
                <button id="export-left-btn" class="btn btn-primary">Export Left as JPEG</button>
            </div>
            <div style="flex: 1; text-align: center; padding-left: 10px;">
                <button id="export-right-btn" class="btn btn-primary">Export Right as JPEG</button>
            </div>
        </div>
    </div>

    <script>
        function exportImage(containerId, frameId, filename) {
            const container = document.getElementById(containerId);
            const frame = document.getElementById(frameId);

            // Create a canvas element
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Set canvas dimensions to match the container
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;

            // Get background image URLs
            const containerStyles = window.getComputedStyle(container);
            const frameStyles = window.getComputedStyle(frame);

            const containerBgMatch = containerStyles.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
            const frameBgMatch = frameStyles.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);

            if (containerBgMatch && frameBgMatch) {
                const containerImageUrl = containerBgMatch[1];
                const frameImageUrl = frameBgMatch[1];

                let loadedImages = 0;
                const totalImages = 2;

                const containerImg = new Image();
                const frameImg = new Image();

                containerImg.crossOrigin = 'anonymous';
                frameImg.crossOrigin = 'anonymous';

                function onImageLoad() {
                    loadedImages++;
                    if (loadedImages === totalImages) {
                        // Draw container image first (background)
                        ctx.drawImage(containerImg, 0, 0, canvas.width, canvas.height);

                        // Draw frame image on top
                        ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);

                        // Convert canvas to JPEG and download
                        canvas.toBlob(function(blob) {
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = filename + '-' + Date.now() + '.jpg';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        }, 'image/jpeg', 0.9);
                    }
                }

                containerImg.onload = onImageLoad;
                frameImg.onload = onImageLoad;

                containerImg.onerror = function() {
                    alert('Error loading background image. Please try again.');
                };

                frameImg.onerror = function() {
                    alert('Error loading frame image. Please try again.');
                };

                containerImg.src = containerImageUrl;
                frameImg.src = frameImageUrl;
            } else {
                alert('No background images found to export.');
            }
        }

        // Left export button
        document.getElementById('export-left-btn').addEventListener('click', function() {
            exportImage('left-container', 'exportable-image-left', 'framed-image-left');
        });

        // Right export button
        document.getElementById('export-right-btn').addEventListener('click', function() {
            exportImage('right-container', 'exportable-image-right', 'framed-image-right');
        });
    </script>
@endsection
