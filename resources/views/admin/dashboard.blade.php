<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Snickers Campaign</title>
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

        .logout-btn {
            background: #FFD700;
            color: #8B4513;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #FFA500;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: #8B4513;
            color: white;
            padding: 20px;
        }

        .table-header h2 {
            font-size: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .image-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            margin: 2px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 5px;
        }

        .pagination .active {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .phone-number {
            font-family: monospace;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>🍫 SNICKERS Admin Panel</h1>
            </div>
            <a href="{{ route('admin.logout') }}" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">{{ $images->total() }}</div>
                <div class="stat-label">Total Photos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $images->where('created_at', '>=', now()->startOfDay())->count() }}</div>
                <div class="stat-label">Today's Photos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $images->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $images->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Generated Images</h2>
            </div>

            @if($images->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Phone Number</th>
                            <th>Original</th>
                            <th>Sad</th>
                            <th>Happy</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($images as $image)
                            <tr>
                                <td>{{ $image->id }}</td>
                                <td>
                                    <span class="phone-number">{{ $image->phone_number }}</span>
                                </td>
                                <td>
                                    <img src="{{ Storage::url($image->original_image) }}" alt="Original" class="image-preview">
                                </td>
                                <td>
                                    <img src="{{ Storage::url($image->sad_image) }}" alt="Sad" class="image-preview">
                                </td>
                                <td>
                                    <img src="{{ Storage::url($image->happy_image) }}" alt="Happy" class="image-preview">
                                </td>
                                <td>{{ $image->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.show', $image->id) }}" class="btn btn-primary">View</a>
                                    <a href="{{ route('admin.edit', $image->id) }}" class="btn btn-warning">Edit</a>
                                    <a href="{{ route('admin.download', ['id' => $image->id, 'type' => 'original']) }}" class="btn btn-success">Download</a>
                                    <form method="POST" action="{{ route('admin.destroy', $image->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="pagination">
                    {{ $images->links() }}
                </div>
            @else
                <div class="no-data">
                    <h3>No images found</h3>
                    <p>No photos have been generated yet.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
