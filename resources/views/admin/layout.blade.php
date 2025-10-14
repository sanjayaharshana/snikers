<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Snickers Campaign</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #334155;
        }

        /* Font Awesome icon fixes */
        .fa, .fas, .far, .fab, .fal, .fad, .fat {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important;
            font-weight: 900;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .far {
            font-weight: 400;
        }

        .header {
            background: linear-gradient(135deg, #4b5563, #9ca3af);
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

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
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

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            color: #8B4513;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            font-size: 14px;
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
            display: inline-block;
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

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
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

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: #8B4513;
            color: white;
            padding: 20px;
        }

        .card-header h2 {
            font-size: 20px;
        }

        .card-body {
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

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            padding: 20px 0;
        }

        .pagination ul {
            display: flex !important;
            flex-direction: row !important;
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

        /* Force horizontal layout for Laravel pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .pagination-wrapper .pagination {
            display: flex !important;
            flex-direction: row !important;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .pagination-wrapper .pagination > li {
            display: inline-block !important;
            float: none !important;
            margin: 0 4px;
        }

        .pagination-wrapper .pagination > li > a,
        .pagination-wrapper .pagination > li > span {
            display: inline-block !important;
            float: none !important;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.2);
        }

        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .image-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .image-card-body {
            padding: 15px;
        }

        .image-card-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #8B4513;
        }

        .breadcrumb {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #8B4513;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 10px;
            color: #666;
        }

        /* Professional Dashboard Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .dashboard-card-header {
            background: linear-gradient(135deg, #4b5563, #9ca3af);
            color: white;
            padding: 20px;
            position: relative;
        }

        .dashboard-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            pointer-events: none;
        }

        .dashboard-card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .dashboard-card-subtitle {
            font-size: 14px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .dashboard-card-body {
            padding: 25px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .metric-item {
            text-align: center;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 8px;
            border-left: 4px solid #9ca3af;
        }

        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-container {
            height: 200px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-style: italic;
        }

        .activity-feed {
            max-height: 300px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #9ca3af;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            color: #334155;
            margin-bottom: 2px;
        }

        .activity-time {
            font-size: 12px;
            color: #64748b;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        .status-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .status-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            border-color: #9ca3af;
            background: #f3f4f6;
            transform: translateY(-1px);
        }

        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #9ca3af;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .quick-action-text {
            flex: 1;
        }

        .quick-action-title {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .quick-action-desc {
            font-size: 12px;
            color: #64748b;
        }

        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            font-size: 14px;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .export-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .export-btn:hover {
            background: #218838;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table-enhanced {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table-enhanced th {
            background: #f3f4f6;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-enhanced td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .table-enhanced tr:hover {
            background: #f3f4f6;
        }

        .image-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e2e8f0;
            transition: transform 0.3s ease;
        }

        .image-thumbnail:hover {
            transform: scale(1.1);
            border-color: #9ca3af;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #334155;
        }

        .empty-state-text {
            font-size: 14px;
            line-height: 1.5;
        }

        /* Sidebar Styles */
        .main-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: ghostwhite;
            color: #000000;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-section-title {
            padding: 0 20px 10px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
        }

        .nav-item {
            display: block;
            padding: 12px 20px;
            color: #000000;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
        }

        .nav-item:hover {
            background: #f3f4f6;
            border-left-color: #000000;
            color: #000000;
        }

        .nav-item.active {
            background: #f3f4f6;
            border-left-color: #000000;
            color: #000000;
        }

        .nav-item-icon {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
            display: inline-block;
        }

        .nav-item-text {
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item-badge {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            opacity: 0.6;
        }

        .content-area {
            flex: 1;
            margin-left: 250px;
            background: #f8fafc;
            min-height: 100vh;
        }

        .content-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .content-body {
            padding: 30px;
        }

        .mobile-menu-toggle {
            display: none;
            background: #4b5563;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .content-area {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .sidebar-overlay {
                display: block;
            }

            .content-header {
                padding: 15px 20px;
            }

            .content-body {
                padding: 20px;
            }
        }

        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: #9ca3af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 20px;
            color: white;
        }

        .user-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 12px;
            opacity: 0.8;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.3);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fa-solid fa-candy-cane"></i> SNICKERS</div>
                <div class="sidebar-subtitle">Admin Panel</div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-item-icon"><i class="fa-solid fa-chart-line"></i></span>
                        <span class="nav-item-text">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.images.index') }}" class="nav-item {{ request()->routeIs('admin.images.*') ? 'active' : '' }}">
                        <span class="nav-item-icon"><i class="fa-regular fa-images"></i></span>
                        <span class="nav-item-text">Generated Images</span>
                    </a>
                    <a href="{{ route('admin.queue-jobs') }}" class="nav-item {{ request()->routeIs('admin.queue-jobs') ? 'active' : '' }}">
                        <span class="nav-item-icon"><i class="fa-solid fa-tasks"></i></span>
                        <span class="nav-item-text">Queue Jobs</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <span class="nav-item-icon"><i class="fa-solid fa-gear"></i></span>
                        <span class="nav-item-text">Settings</span>
                    </a>
                    <a href="{{ route('snickers.campaign') }}" target="_blank" class="nav-item">
                        <span class="nav-item-icon"><i class="fa-solid fa-candy-cane"></i></span>
                        <span class="nav-item-text">Snickers Campaign</span>
                        <span class="nav-item-badge"><i class="fa-solid fa-external-link-alt"></i></span>
                    </a>
                </div>
            </nav>

            <div class="user-info">
                <div class="user-avatar"><i class="fa-regular fa-user"></i></div>
                <div class="user-name">Admin User</div>
                <div class="user-role">System Administrator</div>
                <a href="{{ route('admin.logout') }}" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="content-header">
                <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
                <div class="page-header">
                    <h1>@yield('page-title')</h1>
                    <p>@yield('page-subtitle')</p>
                </div>
            </div>

            <div class="content-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info">
                        {{ session('info') }}
                    </div>
                @endif

                @yield('breadcrumb')

                @yield('content')
            </div>
        </div>

        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    </div>

    @stack('scripts')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('open');
            overlay.style.display = 'none';
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });

        // Placeholder functions for navigation items
        function showPendingImages() {
            alert('Pending images filter would be implemented here');
        }

        function showCompletedImages() {
            alert('Completed images filter would be implemented here');
        }

        function showAnalytics() {
            alert('Analytics page would be implemented here');
        }

        function showReports() {
            alert('Reports page would be implemented here');
        }

        function showSystemStatus() {
            alert('System status page would be implemented here');
        }

        function showLogs() {
            alert('Activity logs page would be implemented here');
        }
    </script>
</body>
</html>
