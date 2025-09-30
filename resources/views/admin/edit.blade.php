<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Image - Snickers Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #8B4513;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #FFD700;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-right: 10px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
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

        <div class="form-container">
            <h2>Edit Image #{{ $image->id }}</h2>
            
            <form method="POST" action="{{ route('admin.update', $image->id) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $image->phone_number) }}" required>
                    @error('phone_number')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="image-preview">
                    <div class="preview-card">
                        <img src="{{ Storage::url($image->original_image) }}" alt="Original">
                        <div class="preview-label">Original</div>
                    </div>
                    <div class="preview-card">
                        <img src="{{ Storage::url($image->sad_image) }}" alt="Sad">
                        <div class="preview-label">Sad Emotion</div>
                    </div>
                    <div class="preview-card">
                        <img src="{{ Storage::url($image->happy_image) }}" alt="Happy">
                        <div class="preview-label">Happy Emotion</div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Update Image</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
