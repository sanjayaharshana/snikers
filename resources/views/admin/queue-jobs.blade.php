@extends('admin.layout')

@section('title', 'Queue Jobs')
@section('page-title', 'Queue Jobs Management')
@section('page-subtitle', 'Monitor and manage background job processing')

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span>Queue Jobs</span>
</div>
@endsection

@section('content')
<!-- Queue Statistics -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-clock"></i> Pending Jobs</div>
            <div class="dashboard-card-subtitle">Jobs waiting to be processed</div>
        </div>
        <div class="dashboard-card-body">
            <div class="metric-value">{{ $stats['pending_jobs'] }}</div>
            <div class="metric-label">Jobs in Queue</div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-times-circle"></i> Failed Jobs</div>
            <div class="dashboard-card-subtitle">Jobs that failed to process</div>
        </div>
        <div class="dashboard-card-body">
            <div class="metric-value">{{ $stats['failed_jobs'] }}</div>
            <div class="metric-label">Failed Jobs</div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div class="dashboard-card-title"><i class="fa-solid fa-check-circle"></i> Processed Jobs</div>
            <div class="dashboard-card-subtitle">Successfully completed jobs</div>
        </div>
        <div class="dashboard-card-body">
            <div class="metric-value">{{ $stats['total_processed'] }}</div>
            <div class="metric-label">Completed Jobs</div>
        </div>
    </div>
</div>

<!-- Queue Actions -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-solid fa-cogs"></i> Queue Actions</div>
        <div class="dashboard-card-subtitle">Manage the job queue</div>
    </div>
    <div class="dashboard-card-body">
        <div class="action-buttons">
            <form method="POST" action="{{ route('admin.clear-queue') }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear all pending jobs? This action cannot be undone.')">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash"></i> Clear All Pending Jobs
                </button>
            </form>
            <a href="{{ route('admin.queue-jobs') }}" class="btn btn-info">
                <i class="fa-solid fa-refresh"></i> Refresh
            </a>
        </div>
    </div>
</div>

<!-- Pending Jobs -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-solid fa-list"></i> Pending Jobs</div>
        <div class="dashboard-card-subtitle">Jobs waiting to be processed</div>
    </div>
    <div class="dashboard-card-body">
        @if($jobs->count() > 0)
            <div class="table-responsive">
                <table class="table-enhanced">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Queue</th>
                            <th>Payload</th>
                            <th>Attempts</th>
                            <th>Created At</th>
                            <th>Available At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                            <tr>
                                <td><strong>#{{ $job->id }}</strong></td>
                                <td>
                                    <span class="status-badge status-info">{{ $job->queue }}</span>
                                </td>
                                <td>
                                    @php
                                        $payload = json_decode($job->payload, true);
                                        $jobClass = $payload['displayName'] ?? 'Unknown Job';
                                    @endphp
                                    <span class="job-class">{{ $jobClass }}</span>
                                </td>
                                <td>
                                    <span class="attempts">{{ $job->attempts }}</span>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($job->created_at)->format('M d, Y H:i') }}</div>
                                    <small style="color: #64748b;">{{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($job->available_at)->format('M d, Y H:i') }}</div>
                                    <small style="color: #64748b;">{{ \Carbon\Carbon::parse($job->available_at)->diffForHumans() }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div class="pagination">
                    {{ $jobs->links() }}
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-check-circle"></i></div>
                <div class="empty-state-title">No Pending Jobs</div>
                <div class="empty-state-text">All jobs have been processed successfully. The queue is empty.</div>
            </div>
        @endif
    </div>
</div>

<!-- Failed Jobs -->
<div class="dashboard-card">
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-solid fa-exclamation-triangle"></i> Failed Jobs</div>
        <div class="dashboard-card-subtitle">Jobs that failed to process</div>
    </div>
    <div class="dashboard-card-body">
        @if($failedJobs->count() > 0)
            <div class="table-responsive">
                <table class="table-enhanced">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Queue</th>
                            <th>Payload</th>
                            <th>Exception</th>
                            <th>Failed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failedJobs as $job)
                            <tr>
                                <td><strong>#{{ $job->id }}</strong></td>
                                <td>
                                    <span class="status-badge status-danger">{{ $job->queue }}</span>
                                </td>
                                <td>
                                    @php
                                        $payload = json_decode($job->payload, true);
                                        $jobClass = $payload['displayName'] ?? 'Unknown Job';
                                    @endphp
                                    <span class="job-class">{{ $jobClass }}</span>
                                </td>
                                <td>
                                    <div class="exception-preview">
                                        {{ Str::limit($job->exception, 100) }}
                                    </div>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($job->failed_at)->format('M d, Y H:i') }}</div>
                                    <small style="color: #64748b;">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" action="{{ route('admin.retry-job', $job->id) }}" style="display: inline;" onsubmit="return confirm('Retry this failed job?')">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-redo"></i> Retry
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.delete-job', $job->id) }}" style="display: inline;" onsubmit="return confirm('Delete this failed job permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
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
                    {{ $failedJobs->links() }}
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-check-circle"></i></div>
                <div class="empty-state-title">No Failed Jobs</div>
                <div class="empty-state-text">All jobs have been processed successfully. No failed jobs found.</div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .metric-value {
        font-size: 32px;
        font-weight: bold;
        color: #334155;
        text-align: center;
        margin-bottom: 8px;
    }

    .metric-label {
        font-size: 14px;
        color: #64748b;
        text-align: center;
        font-weight: 500;
    }

    .job-class {
        font-family: monospace;
        font-size: 12px;
        background: #f8fafc;
        padding: 4px 8px;
        border-radius: 4px;
        color: #475569;
    }

    .attempts {
        font-weight: bold;
        color: #f59e0b;
    }

    .exception-preview {
        font-family: monospace;
        font-size: 11px;
        color: #dc2626;
        background: #fef2f2;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #fecaca;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
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
        gap: 6px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-info {
        background: #06b6d4;
        color: white;
    }

    .btn-info:hover {
        background: #0891b2;
    }
</style>
@endpush
@endsection
