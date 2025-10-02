<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Image - Snickers Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #8B4513, #A0522D);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: bold;
        }

        .nav-buttons a {
            background: #FFD700;
            color: #8B4513;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .nav-buttons a:hover {
            background: #FFA500;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .image-details {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }

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

        .phone-number {
            font-family: monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 16px;
            border: 1px solid #ddd;
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .image-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
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

        .download-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            background: #218838;
        }

        .back-link {
            margin-bottom: 20px;
        }

        .back-link a {
            color: #8B4513;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>🍫 SNICKERS Admin Panel</h1>
            </div>
            <div class="nav-buttons">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.logout') }}">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="back-link">
            <a href="{{ route('admin.dashboard') }}">← Back to Dashboard</a>
        </div>

        <div class="image-details">
            <h2>Image Details</h2>
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

        <div class="images-grid">
            <div class="image-card">
                <div class="image-header">Original Image</div>
                <div class="image-content">
                    <img src="{{ Storage::url($image->original_image) }}" alt="Original Image">
                    <br>
                    <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'original']) }}" class="download-btn">Download Original</a>
                </div>
            </div>

            <div class="image-card">
                <div class="image-header">Sad Emotion</div>
                <div class="image-content">
                    <img src="{{ Storage::url($image->sad_image) }}" alt="Sad Image">
                    <br>
                    <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'sad']) }}" class="download-btn">Download Sad</a>
                </div>
            </div>

            <div class="image-card">
                <div class="image-header">Happy Emotion</div>
                <div class="image-content">
                    <img src="{{ Storage::url($image->happy_image) }}" alt="Happy Image">
                    <br>
                    <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'happy']) }}" class="download-btn">Download Happy</a>
                </div>
            </div>

            @if($image->framed_image)
            <div class="image-card">
                <div class="image-header">Framed Combined Image</div>
                <div class="image-content">
                    <img src="{{ Storage::url($image->framed_image) }}" alt="Framed Combined Image">
                    <br>
                    <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'framed']) }}" class="download-btn">Download Framed</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
