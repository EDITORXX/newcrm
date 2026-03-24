<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finance Manager - Base CRM')</title>
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        if (auth()->check() && !session('api_token')) {
            $__token = auth()->user()->createToken('web-session-token')->plainTextToken;
            session(['api_token' => $__token]);
        }
    @endphp
    <meta name="api-token" content="{{ session('api_token', '') }}">
    <meta name="user-id" content="{{ auth()->check() ? auth()->user()->id : '' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            width: 100%; 
            overflow-x: hidden;
        }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        
        .container { 
            max-width: 100%; 
            width: 100%; 
            padding: 12px;
        }
        .header {
            background: white;
            padding: 16px;
            border-radius: 12px; 
            margin-bottom: 16px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            display: flex; 
            justify-content: space-between;
            align-items: center;
        }
        .sidebar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 12px 16px;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #063A1C;
            color: white;
        }
        .content {
            flex: 1;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #063A1C;
            color: white;
        }
        .btn-primary:hover {
            background: #205A44;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="font-size: 24px; font-weight: 600; color: #063A1C;">Finance Manager</h1>
            <div style="display: flex; align-items: center; gap: 16px;">
                <span style="color: #6b7280;">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 14px;">Logout</button>
                </form>
            </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="sidebar" style="min-width: 200px;">
                <a href="{{ route('finance-manager.dashboard') }}" class="{{ request()->routeIs('finance-manager.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="{{ route('finance-manager.incentives') }}" class="{{ request()->routeIs('finance-manager.incentives') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave mr-2"></i>Incentives
                    <span id="pendingIncentivesBadge" style="float: right; background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">0</span>
                </a>
            </div>

            <div class="content" style="flex: 1; min-width: 300px;">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
