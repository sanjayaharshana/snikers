@extends('admin.layout')

@section('title', 'Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure AI providers and processing mode')

@section('breadcrumb')
<div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="breadcrumb-separator">/</span>
    <span>Settings</span>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="dashboard-card" style="padding: 20px;">
    @csrf
    <div class="dashboard-card-header">
        <div class="dashboard-card-title"><i class="fa-solid fa-gear"></i> Configuration</div>
        <div class="dashboard-card-subtitle">Toggle AI providers and queue/direct processing</div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="switch-label">AI Mode (Real API Calls)</label>
            <label class="switch">
                <input type="checkbox" name="ai_mode" value="1" {{ $data['ai_mode'] ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
            <div class="help-text">Disable to run in dummy mode with no API costs</div>
        </div>

        <div class="form-group">
            <label class="switch-label">Use AILabTools API</label>
            <label class="switch">
                <input type="checkbox" name="use_ailabtools" value="1" {{ $data['use_ailabtools'] ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
        </div>

        <div class="form-group">
            <label class="switch-label">Use Google Gemini API</label>
            <label class="switch">
                <input type="checkbox" name="use_gemini" value="1" {{ $data['use_gemini'] ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
            <div class="help-text">Ensure API key is set in .env</div>
        </div>

        <div class="form-group">
            <label class="switch-label">Direct API Mode (No Queue)</label>
            <label class="switch">
                <input type="checkbox" name="direct_api" value="1" {{ $data['direct_api'] ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
            <div class="help-text">Disable to process with background queue jobs</div>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Save Settings</button>
    </div>
</form>

<style>
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
.form-group { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px; }
.switch { position: relative; display: inline-block; width: 52px; height: 28px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #888; transition: .2s; border-radius: 28px; }
.slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; }
input:checked + .slider { background-color: #FFD700; }
input:checked + .slider:before { transform: translateX(24px); }
.switch-label { display: block; margin-bottom: 8px; font-weight: 600; }
.help-text { font-size: 12px; color: #bbb; margin-top: 6px; }
</style>
@endsection


