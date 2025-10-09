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
        <div style="background: url('{{url('framebox.png')}}');height: 400px;background-size: contain;background-position: center;background-repeat: no-repeat"></div>
        <div style="background: url('{{url('framebox.png')}}');height: 400px;background-size: contain;background-position: center;background-repeat: no-repeat"></div>

    </div>
@endsection
