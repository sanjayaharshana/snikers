@extends('admin.layout')

@section('title', 'Images Management')
@section('page-title', 'Images Management')
@section('page-subtitle', 'Manage campaign photos and AI processing')

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span>Images</span>
</div>
@endsection

@section('content')
<!-- Combined Filter, Search and Bulk Actions Bar -->
<div class="combined-controls-bar">
    <!-- Filters and Search -->
    <form method="GET" action="{{ route('admin.images.index') }}" class="filter-section">
        <div class="filter-group">
            <label class="filter-label">Status:</label>
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>Complete</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Date:</label>
            <select name="date" class="filter-select" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('date') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date') == 'month' ? 'selected' : '' }}>This Month</option>
            </select>
        </div>
        <input type="text" name="search" class="search-box" placeholder="Search by phone number..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <!-- Bulk Actions -->
    @if($images->count() > 0)
    <form method="POST" action="{{ route('admin.images.bulk-action') }}" id="bulkForm" class="bulk-section">
        @csrf
        <div class="filter-group">
            <label class="filter-label">Bulk Action:</label>
            <select name="action" class="filter-select" required>
                <option value="">Choose Action</option>
                <option value="generate_happy">Generate Happy Photos</option>
                <option value="generate_sad">Generate Sad Photos</option>
                <option value="delete">Delete Selected</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" onclick="return confirmBulkAction()">Execute</button>
        <button type="button" class="btn btn-info" onclick="selectAll()">Select All</button>
        <button type="button" class="btn btn-warning" onclick="selectNone()">Select None</button>
    </form>
    @endif

    <!-- Add New Button -->
    <div class="action-section">
        <a href="{{ route('admin.images.create') }}" class="btn btn-success">Add New Image</a>
    </div>
</div>

<!-- Images Table -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-regular fa-images"></i> Generated Images</div>
        <div class="dashboard-card-subtitle">Total: {{ $images->total() }} images</div>
    </div>
    <div class="dashboard-card-body">
        @if($images->count() > 0)
            <div class="table-responsive">
                <table class="table-enhanced">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllCheckboxes()">
                            </th>
                            <th>ID</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                            <th>Original</th>
                            <th>Sad</th>
                            <th>Happy</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($images as $image)
                            <tr>
                                <td>
                                    <input type="checkbox" name="images[]" value="{{ $image->id }}" class="image-checkbox">
                                </td>
                                <td><strong>#{{ $image->id }}</strong></td>
                                <td>
                                    <span class="phone-number">{{ $image->phone_number }}</span>
                                </td>
                                <td>
                                    @if($image->sad_image && $image->happy_image)
                                        <span class="status-badge status-success">Complete</span>
                                    @elseif($image->sad_image || $image->happy_image)
                                        <span class="status-badge status-warning">Processing</span>
                                    @else
                                        <span class="status-badge status-info">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($image->original_image)
                                        <div class="image-container">
                                            <img src="{{ Storage::url($image->original_image) }}" 
                                                 alt="Original" 
                                                 class="image-thumbnail"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="image-placeholder" style="display: none;">
                                                <i class="fa-solid fa-image"></i>
                                                <span>Broken</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="status-badge status-danger">Missing</span>
                                    @endif
                                </td>
                                <td>
                                    @if($image->sad_image)
                                        <div class="image-container">
                                            <img src="{{ Storage::url($image->sad_image) }}" 
                                                 alt="Sad" 
                                                 class="image-thumbnail"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="image-placeholder" style="display: none;">
                                                <i class="fa-solid fa-image"></i>
                                                <span>Broken</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="status-badge status-danger">Not Generated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($image->happy_image)
                                        <div class="image-container">
                                            <img src="{{ Storage::url($image->happy_image) }}" 
                                                 alt="Happy" 
                                                 class="image-thumbnail"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="image-placeholder" style="display: none;">
                                                <i class="fa-solid fa-image"></i>
                                                <span>Broken</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="status-badge status-danger">Not Generated</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $image->created_at->format('M d, Y') }}</div>
                                    <small style="color: #64748b;">{{ $image->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.images.show', $image->id) }}" class="btn btn-primary btn-sm">View</a>
                                        <a href="{{ route('admin.images.edit', $image->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        
                                        @if($image->original_image)
                                            <a href="{{ route('admin.images.download', ['image' => $image->id, 'type' => 'original']) }}" class="btn btn-info btn-sm">Download</a>
                                        @endif
                                        
                                        @if(!$image->sad_image && $image->original_image)
                                            <form method="POST" action="{{ route('admin.images.generate-sad', $image->id) }}" style="display: inline;" onsubmit="return confirm('Queue sad photo generation?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Generate Sad</button>
                                            </form>
                                        @endif
                                        
                                        @if(!$image->happy_image && $image->original_image)
                                            <form method="POST" action="{{ route('admin.images.generate-happy', $image->id) }}" style="display: inline;" onsubmit="return confirm('Queue happy photo generation?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Generate Happy</button>
                                            </form>
                                        @endif
                                        
                                        <form method="POST" action="{{ route('admin.images.destroy', $image->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div class="pagination">
                    {{ $images->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-camera"></i></div>
                <div class="empty-state-title">No Images Found</div>
                <div class="empty-state-text">
                    @if(request()->hasAny(['status', 'date', 'search']))
                        No images match your current filters. Try adjusting your search criteria.
                    @else
                        No images have been uploaded yet. <a href="{{ route('admin.images.create') }}">Upload the first image</a> to get started.
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    /* Combined Controls Bar */
    .combined-controls-bar {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-section {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
        min-width: 300px;
    }

    .bulk-section {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .action-section {
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-label {
        font-size: 14px;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        background: white;
        min-width: 120px;
    }

    .search-box {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        background: white;
        min-width: 200px;
    }

    .btn {
        padding: 8px 16px;
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

    .btn-secondary {
        background: #64748b;
        color: white;
    }

    .btn-secondary:hover {
        background: #475569;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #1e40af;
    }

    .btn-info {
        background: #06b6d4;
        color: white;
    }

    .btn-info:hover {
        background: #0891b2;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .combined-controls-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-section {
            min-width: auto;
            flex-wrap: wrap;
        }

        .bulk-section {
            justify-content: center;
        }
    }

    .image-container {
        position: relative;
        display: inline-block;
    }

    .image-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .image-placeholder {
        width: 60px;
        height: 60px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 12px;
    }

    .image-placeholder i {
        font-size: 16px;
        margin-bottom: 2px;
    }

    .image-placeholder span {
        font-size: 10px;
        font-weight: 500;
    }

    /* Pagination specific styles */
    .pagination {
        margin-top: 30px;
        padding: 20px 0;
        border-top: 1px solid #e2e8f0;
    }

    .pagination ul {
        display: flex !important;
        flex-direction: row !important;
        justify-content: center;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .pagination li {
        margin: 0;
        display: inline-block;
        float: none;
    }

    .pagination a, .pagination span {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        margin: 0;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: #64748b;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        min-width: 40px;
        height: 40px;
        transition: all 0.3s ease;
        background: white;
    }

    .pagination a:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #334155;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .pagination .active span {
        background: #4b5563;
        color: white;
        border-color: #4b5563;
        font-weight: 600;
    }

    .pagination .disabled span {
        background: #f8fafc;
        color: #cbd5e1;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }

    .pagination .page-link {
        background: white;
    }

    .pagination .page-item.active .page-link {
        background: #4b5563;
        border-color: #4b5563;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        background: #f8fafc;
        color: #cbd5e1;
        border-color: #e2e8f0;
    }
</style>
@endpush

@push('scripts')
<script>
function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.image-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
}

function selectNone() {
    const checkboxes = document.querySelectorAll('.image-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
}

function confirmBulkAction() {
    const selectedImages = document.querySelectorAll('.image-checkbox:checked');
    const action = document.querySelector('select[name="action"]').value;
    
    if (selectedImages.length === 0) {
        alert('Please select at least one image.');
        return false;
    }
    
    if (!action) {
        alert('Please select an action.');
        return false;
    }
    
    const actionText = action === 'delete' ? 'delete' : 'process';
    return confirm(`Are you sure you want to ${actionText} ${selectedImages.length} selected image(s)?`);
}

// Add hidden inputs for selected images
document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const selectedImages = document.querySelectorAll('.image-checkbox:checked');
    
    // Remove existing hidden inputs
    const existingInputs = document.querySelectorAll('input[name="images[]"]');
    existingInputs.forEach(input => {
        if (input.type === 'hidden') {
            input.remove();
        }
    });
    
    // Add hidden inputs for selected images
    selectedImages.forEach(checkbox => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'images[]';
        hiddenInput.value = checkbox.value;
        this.appendChild(hiddenInput);
    });
});
</script>
@endpush
@endsection
