@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Campaign Analytics Dashboard')
@section('page-subtitle', 'Real-time insights and management for Snickers campaign')

@section('content')
<!-- Key Metrics Overview -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-chart-column"></i> Campaign Overview</div>
            <div class="dashboard-card-subtitle">Total campaign engagement</div>
        </div>
        <div class="dashboard-card-body">
            <div class="metric-grid">
                <div class="metric-item">
                    <div class="metric-value">{{ $images->total() }}</div>
                    <div class="metric-label">Total Photos</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $images->where('created_at', '>=', now()->startOfDay())->count() }}</div>
                    <div class="metric-label">Today</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $images->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                    <div class="metric-label">This Week</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $images->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                    <div class="metric-label">This Month</div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-face-smile"></i> Emotion Processing</div>
            <div class="dashboard-card-subtitle">AI processing status</div>
        </div>
        <div class="dashboard-card-body">
            <div class="metric-grid">
                <div class="metric-item">
                    <div class="metric-value">{{ $images->whereNotNull('sad_image')->count() }}</div>
                    <div class="metric-label">Sad Processed</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $images->whereNotNull('happy_image')->count() }}</div>
                    <div class="metric-label">Happy Processed</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $images->whereNotNull('sad_image')->whereNotNull('happy_image')->count() }}</div>
                    <div class="metric-label">Complete</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $images->filter(function($image) { return is_null($image->sad_image) || is_null($image->happy_image); })->count() }}</div>
                    <div class="metric-label">Pending</div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-chart-line"></i> Growth Trends</div>
            <div class="dashboard-card-subtitle">Campaign performance</div>
        </div>
        <div class="dashboard-card-body">
            <div class="chart-container">
                📊 Chart visualization would go here
                <br><small>Integration with Chart.js or similar library</small>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-regular fa-bolt"></i> Recent Activity</div>
            <div class="dashboard-card-subtitle">Latest campaign interactions</div>
        </div>
        <div class="dashboard-card-body">
            <div class="activity-feed">
                @foreach($images->take(5) as $image)
                <div class="activity-item">
                    <div class="activity-icon"><i class="fa-solid fa-camera"></i></div>
                    <div class="activity-content">
                        <div class="activity-text">
                            New photo uploaded by {{ $image->phone_number }}
                            @if($image->sad_image && $image->happy_image)
                                <span class="status-badge status-success">Complete</span>
                            @elseif($image->sad_image || $image->happy_image)
                                <span class="status-badge status-warning">Processing</span>
                            @else
                                <span class="status-badge status-info">Queued</span>
                            @endif
                        </div>
                        <div class="activity-time">{{ $image->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title">🚀 Quick Actions</div>
        <div class="dashboard-card-subtitle">Common administrative tasks</div>
    </div>
    <div class="dashboard-card-body">
        <div class="quick-actions">
            <a href="{{ route('admin.dashboard') }}" class="quick-action-btn">
                <div class="quick-action-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <div class="quick-action-text">
                    <div class="quick-action-title">View Analytics</div>
                    <div class="quick-action-desc">Detailed campaign metrics</div>
                </div>
            </a>
            <a href="#" class="quick-action-btn" onclick="exportData()">
                <div class="quick-action-icon"><i class="fa-solid fa-file-export"></i></div>
                <div class="quick-action-text">
                    <div class="quick-action-title">Export Data</div>
                    <div class="quick-action-desc">Download campaign data</div>
                </div>
            </a>
            <a href="#" class="quick-action-btn" onclick="bulkProcess()">
                <div class="quick-action-icon"><i class="fa-solid fa-gears"></i></div>
                <div class="quick-action-text">
                    <div class="quick-action-title">Bulk Process</div>
                    <div class="quick-action-desc">Process multiple images</div>
                </div>
            </a>
            <a href="{{ route('admin.images.index') }}" class="quick-action-btn">
                <div class="quick-action-icon"><i class="fa-regular fa-images"></i></div>
                <div class="quick-action-text">
                    <div class="quick-action-title">Manage Images</div>
                    <div class="quick-action-desc">View and manage all images</div>
                </div>
            </a>
            <a href="#" class="quick-action-btn" onclick="systemStatus()">
                <div class="quick-action-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <div class="quick-action-text">
                    <div class="quick-action-title">System Status</div>
                    <div class="quick-action-desc">Check system health</div>
                </div>
            </a>
        </div>
    </div>
</div>


@push('scripts')
<script>
function exportData() {
    // Implement data export
    alert('Export functionality would be implemented here');
}

function bulkProcess() {
    // Implement bulk processing
    alert('Bulk processing functionality would be implemented here');
}

function systemStatus() {
    // Implement system status check
    alert('System status check would be implemented here');
}
</script>
@endpush
@endsection
