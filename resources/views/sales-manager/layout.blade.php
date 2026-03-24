<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Assistant Sales Manager - CRM Pro')</title>
    
    <!-- Cache Control - Prevent Browser Caching -->
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
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}">
    <meta name="firebase-config" content="{{ json_encode(config('firebase.web')) }}">
    <meta name="firebase-vapid-key" content="{{ config('firebase.vapid_key') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            width: 100%; 
            overflow-x: hidden;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        body { font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        
        /* Mobile First - Base Styles */
        .container { 
            max-width: 100%; 
            width: 100%; 
            padding: 12px;
            margin-left: 0;
            margin-right: 0;
        }
        .header {
            background: white;
            padding: 16px;
            border-radius: 12px; 
            margin-bottom: 16px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            display: flex; 
            flex-direction: column;
            gap: 12px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .header-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        .header-actions-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Mobile Header - Single Line */
        .header-title-mobile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            font-size: 18px !important;
            font-weight: 600 !important;
            margin: 0;
        }
        
        .header-page-title-desktop {
            flex: 1;
            font-size: 18px;
            font-weight: 600;
            color: #063A1C;
        }
        
        .header-user-info-mobile {
            display: none; /* Hidden by default, shown on mobile */
            flex-direction: column;
            gap: 2px;
            flex: 1;
            min-width: 0;
        }
        
        .header-user-name-mobile {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #063A1C;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .header-user-role-mobile {
            display: block;
            font-size: 11px;
            font-weight: 400;
            color: #6b7280;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .header-user-name-desktop {
            display: inline; /* Shown on desktop */
        }
        
        /* Mobile: Single line header with user name and time */
        @media (max-width: 767px) {
            .header {
                padding: 10px 12px;
                margin-bottom: 12px;
                flex-direction: row;
                align-items: center;
                gap: 8px;
            }
            
            .header-top {
                flex: 1;
                min-width: 0;
            }
            
            .header-title-mobile {
                font-size: 16px !important;
                line-height: 1.3;
                width: 100%;
                display: flex;
                align-items: center;
            }
            
            /* Hide page title on mobile */
            .header-page-title-desktop {
                display: none !important;
            }
            
            /* Show user info on mobile */
            .header-user-info-mobile {
                display: flex;
                flex: 1;
                min-width: 0;
            }
            
            .header-user-name-mobile {
                font-size: 14px;
                font-weight: 600;
                color: #063A1C;
            }
            
            .header-user-role-mobile {
                font-size: 11px;
                font-weight: 400;
                color: #6b7280;
            }
            
            .header-actions {
                flex: 0 0 auto;
                width: auto;
            }
            
            .header-actions-row {
                flex-direction: column;
                gap: 4px;
                align-items: flex-end;
            }
            
            #datetimeClock {
                min-width: 100px;
                padding: 6px 10px;
                font-size: 11px;
            }
            
            #clockTime {
                font-size: 12px;
            }
            
            #clockDate {
                font-size: 9px;
            }
            
            .header-user-name-desktop {
                display: none !important;
            }
        }
        .btn { 
            padding: 10px 16px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 500; 
            transition: all 0.3s; 
            white-space: nowrap;
        }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        
        /* Sidebar Styles - Always Icon-Only (64px) - Desktop Only */
        #sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 64px !important; /* Always icon-only */
            background: white;
            border-right: 1px solid #e0e0e0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            transform: translateX(0);
        }
        
        /* Mobile Footer Navigation - Hidden by default */
        #mobileFooterNav {
            display: none;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Force icon-only - remove expanded state */
        #sidebar.sidebar-expanded {
            width: 64px !important; /* Always icon-only */
        }
        
        /* Remove hidden state - always visible */
        #sidebar.sidebar-hidden {
            width: 64px !important; /* Always icon-only, never hidden */
            transform: translateX(0);
        }
        
        /* Sidebar Link Styles - Always Icon-Only */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
            justify-content: center !important; /* Always center icons */
        }
        
        .sidebar-link i {
            font-size: 18px;
            width: 20px;
            text-align: center;
            margin-right: 0 !important; /* No margin, always centered */
        }
        
        /* Always hide text labels */
        .sidebar-link span {
            display: none !important; /* Permanently hidden */
            font-size: 14px;
        }
        
        .sidebar-link:hover {
            background: #F7F6F3 !important;
            color: #205A44 !important;
        }
        
        .sidebar-link.active {
            background: #F7F6F3 !important;
            color: #205A44 !important;
            font-weight: 500 !important;
        }
        
        /* Desktop hover labels for icon-only nav */
        @media (min-width: 768px) {
            #sidebar .sidebar-link {
                position: relative;
            }
            
            #sidebar .sidebar-link::after {
                content: attr(data-label);
                position: absolute;
                left: 72px;
                top: 50%;
                transform: translateY(-50%) translateX(-6px);
                background: #1f2937;
                color: #fff;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.15s ease, transform 0.15s ease;
                z-index: 1001;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            }
            
            #sidebar .sidebar-link:hover::after {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
        }
        
        /* Sidebar Logo Area - Always Hidden in Icon-Only Mode */
        #sidebar > div:first-child {
            padding: 20px 12px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        /* Always hide logo text */
        #sidebar > div:first-child h2 {
            display: none !important; /* Permanently hidden */
        }
        
        #sidebar > div:first-child p {
            display: none !important; /* Permanently hidden */
        }
        
        /* Sidebar Navigation */
        #sidebar nav {
            padding: 0 12px;
        }
        
        /* Sidebar Toggle Button - Hidden (not needed for icon-only mode) */
        .sidebar-toggle {
            display: none !important; /* Hide toggle button - sidebar always icon-only */
        }
        
        /* Main Content - Mobile First: Full width */
        #mainContent {
            margin-left: 0;
            min-height: 100vh;
            width: 100%;
            background: #F7F6F3;
        }
        
        /* Desktop: 64px margin for icon-only sidebar */
        @media (min-width: 768px) {
            body #mainContent,
            html body #mainContent,
            div#mainContent {
                margin-left: 64px !important;
                width: calc(100% - 64px) !important;
            }
        }
        
        /* Clock Widget Responsive */
        #datetimeClock {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 12px;
            color: #063A1C;
            min-width: 140px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        #clockTime {
            font-size: 14px;
            color: #205A44;
        }
        
        #clockDate {
            font-size: 10px;
            color: #B3B5B4;
            margin-top: 2px;
        }
        
        /* Tablet Styles */
        @media (min-width: 768px) {
            .container { padding: 20px; }
            .header { 
                flex-direction: row; 
                padding: 20px;
                margin-bottom: 20px;
            }
            .header-actions {
                flex-direction: row;
                width: auto;
                align-items: center;
            }
            .header-actions-row {
                flex-wrap: nowrap;
            }
            .btn { 
                padding: 12px 24px; 
                font-size: 16px;
                width: auto;
            }
            .header form {
                width: auto;
            }
            /* Always icon-only (64px) on desktop/tablet */
            #sidebar {
                width: 64px !important;
                display: block !important;
            }
            #sidebar.sidebar-expanded {
                width: 64px !important;
            }
            body #mainContent,
            html body #mainContent,
            div#mainContent {
                margin-left: 64px !important;
                width: calc(100% - 64px) !important;
                padding-bottom: 0 !important; /* No footer padding on desktop */
            }
            /* Hide mobile footer on desktop */
            #mobileFooterNav {
                display: none !important;
            }
            .sidebar-toggle {
                top: 50%;
                left: 52px; /* Position on right edge of 64px sidebar */
                transform: translateY(-50%);
                border-radius: 50%;
            }
            #datetimeClock {
                font-size: 14px;
                min-width: 160px;
            }
            #clockTime {
                font-size: 16px;
            }
            #clockDate {
                font-size: 11px;
            }
            .sidebar-overlay {
                display: none !important;
            }
        }
        
        /* Desktop Styles */
        @media (min-width: 1024px) {
            .container { padding: 20px; }
        }
        
        /* Mobile Specific - Hide Sidebar, Show Footer */
        @media (max-width: 767px) {
            /* Hide sidebar on mobile - completely remove from layout */
            #sidebar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                position: absolute !important;
                left: -9999px !important;
                visibility: hidden !important;
            }
            
            /* Hide sidebar overlay on mobile */
            .sidebar-overlay {
                display: none !important;
            }
            
            /* Hide toggle button on mobile */
            .sidebar-toggle {
                display: none !important;
            }
            
            /* Main content full width on mobile - override inline styles with maximum specificity */
            body #mainContent,
            html body #mainContent,
            div#mainContent {
                margin-left: 0 !important;
                width: 100% !important;
                padding-bottom: 70px !important; /* Space for footer */
                padding-left: 0 !important;
                padding-right: 0 !important;
                left: 0 !important;
                transform: none !important;
            }
            
            /* Remove left padding/margin to eliminate blank sidebar area */
            body .container,
            html body .container,
            div.container,
            #mainContent .container {
                padding-left: 12px !important;
                padding-right: 12px !important;
                margin-left: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                left: 0 !important;
            }
            
            /* Ensure no left spacing from any source */
            body {
                margin-left: 0 !important;
                padding-left: 0 !important;
                overflow-x: hidden;
            }
            
            html {
                margin-left: 0 !important;
                padding-left: 0 !important;
                overflow-x: hidden;
            }
            
            /* Remove any left margin from header or content sections */
            .header,
            .tasks-container,
            .bg-white,
            .header-top,
            .header-actions {
                margin-left: 0 !important;
                padding-left: 12px !important;
            }
            
            /* Ensure all content starts from left edge */
            #mainContent > * {
                margin-left: 0 !important;
            }
            
            /* Remove any transform or positioning that might cause offset */
            #mainContent {
                transform: none !important;
                left: 0 !important;
            }
            
            /* Footer Navigation for Mobile */
            #mobileFooterNav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                background: white;
                border-top: 1px solid #e0e0e0;
                box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
                z-index: 1000;
                padding: 8px 0;
                justify-content: space-around;
                align-items: center;
                height: 60px;
            }
            
            .footer-nav-link {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                color: #666;
                padding: 6px 4px;
                border-radius: 8px;
                transition: all 0.3s;
                flex: 1;
                max-width: 60px;
            }
            
            .footer-nav-link i {
                font-size: 18px;
                margin-bottom: 2px;
            }
            
            .footer-nav-link span {
                font-size: 9px;
                color: #666;
                text-align: center;
                line-height: 1.2;
            }
            
            .footer-nav-link:hover,
            .footer-nav-link.active {
                background: #F7F6F3;
                color: #205A44;
            }
            
            .footer-nav-link.active {
                color: #205A44;
            }
            
            .footer-nav-link.active span {
                color: #205A44;
            }
        }
        
        /* Desktop - Show Sidebar, Hide Footer */
        @media (min-width: 768px) {
            #mobileFooterNav {
                display: none !important;
            }
            
            #sidebar {
                display: block !important;
            }
        }
        
        /* Prevent sidebar flash on page load - only for desktop */
        @media (min-width: 768px) {
            html.sidebar-mobile-collapsed #sidebar {
                width: 64px !important;
            }
            
            html.sidebar-mobile-collapsed body #mainContent,
            html.sidebar-mobile-collapsed html body #mainContent,
            html.sidebar-mobile-collapsed div#mainContent {
                margin-left: 64px !important;
                width: calc(100% - 64px) !important;
            }
        }
        :root {
            --asm-bg: #f3f2ef;
            --asm-surface: #ffffff;
            --asm-surface-soft: #faf8f4;
            --asm-border: #e4e0d7;
            --asm-text: #18201b;
            --asm-muted: #667068;
            --asm-brand: #0d6b4f;
            --asm-shadow: 0 18px 48px rgba(16, 24, 20, 0.08);
        }
        body.asm-shell {
            background: radial-gradient(circle at top right, #f8f6f1 0%, var(--asm-bg) 48%, #ece9e1 100%);
            color: var(--asm-text);
        }
        body.asm-shell::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at top right, rgba(13,107,79,0.08), transparent 32%);
        }
        body.asm-shell .container {
            padding: 24px;
        }
        body.asm-shell .header {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(18px);
            padding: 18px 22px;
            border-radius: 20px;
            margin-bottom: 22px;
            border: 1px solid rgba(228, 224, 215, 0.9);
            box-shadow: 0 12px 34px rgba(17, 24, 20, 0.07);
        }
        body.asm-shell #sidebar,
        body.asm-shell #sidebar.sidebar-expanded,
        body.asm-shell #sidebar.sidebar-hidden {
            width: 232px !important;
            background: linear-gradient(180deg, #0a1f18 0%, #102d24 100%);
            border-right: 1px solid rgba(255,255,255,0.06);
            box-shadow: 10px 0 30px rgba(0,0,0,0.14);
            transform: translateX(0);
        }
        body.asm-shell #sidebar > div:first-child {
            padding: 20px 18px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 10px;
            text-align: left;
        }
        body.asm-shell #sidebar > div:first-child h2,
        body.asm-shell #sidebar > div:first-child p {
            display: block !important;
        }
        body.asm-shell #sidebar > div:first-child h2 {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 22px;
            color: #fff;
        }
        body.asm-shell #sidebar > div:first-child p {
            margin-top: 5px;
            font-size: 11px;
            color: rgba(255,255,255,0.42);
        }
        body.asm-shell #sidebar nav {
            padding: 10px;
        }
        body.asm-shell .sidebar-link {
            justify-content: flex-start !important;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 6px;
            border-radius: 12px;
            color: rgba(255,255,255,0.68);
        }
        body.asm-shell .sidebar-link span {
            display: inline !important;
            font-size: 13px;
            font-weight: 500;
        }
        body.asm-shell .sidebar-link i {
            font-size: 15px;
            width: 18px;
        }
        body.asm-shell .sidebar-link:hover {
            background: rgba(255,255,255,0.08) !important;
            color: #fff !important;
        }
        body.asm-shell .sidebar-link.active {
            background: rgba(13,107,79,0.32) !important;
            color: #fff !important;
            box-shadow: inset 0 0 0 1px rgba(159,225,203,0.18);
        }
        body.asm-shell #mainContent,
        body.asm-shell div#mainContent,
        body.asm-shell html body #mainContent {
            background: transparent !important;
        }
        @media (min-width: 768px) {
            body.asm-shell #mainContent,
            body.asm-shell div#mainContent,
            body.asm-shell html body #mainContent {
                margin-left: 232px !important;
                width: calc(100% - 232px) !important;
            }
        }
        body.asm-shell #datetimeClock {
            background: linear-gradient(135deg, #0d6b4f 0%, #0f5b45 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 10px 14px;
            min-width: 132px;
            box-shadow: 0 10px 24px rgba(13,107,79,0.22);
            font-family: 'Outfit', sans-serif;
        }
        body.asm-shell #clockTime,
        body.asm-shell #clockDate {
            color: #fff;
        }
        .asm-brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0d6b4f 0%, #9fe1cb 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #0a1f18;
            font-weight: 800;
            font-size: 14px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .asm-header-title {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .asm-header-title .eyebrow {
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--asm-muted);
            font-weight: 600;
        }
        .asm-header-title .title {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--asm-text);
            line-height: 1.1;
        }
        .asm-user-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--asm-surface-soft);
            border: 1px solid var(--asm-border);
            color: var(--asm-text);
            font-size: 13px;
            font-weight: 500;
        }
        .asm-user-chip .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6b4f 0%, #7dcbb0 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        @media (max-width: 767px) {
            body.asm-shell .container {
                padding: 12px;
            }
            body.asm-shell .header {
                padding: 14px;
                border-radius: 16px;
            }
            .asm-header-title .title {
                font-size: 18px;
                font-family: 'Outfit', sans-serif;
            }
            .asm-user-chip {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="asm-shell">
    <script>
        // Mobile detection and initial layout - run immediately to override inline styles
        (function() {
            function fixMobileLayout() {
                const isMobile = window.innerWidth < 768;
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                
                if (isMobile) {
                    // Mobile: Hide sidebar and remove left margin from mainContent
                    if (sidebar) {
                        sidebar.style.display = 'none';
                        sidebar.style.width = '0';
                    }
                    // Override inline style on mainContent using setProperty with important
                    if (mainContent) {
                        mainContent.style.setProperty('margin-left', '0', 'important');
                        mainContent.style.setProperty('width', '100%', 'important');
                        mainContent.style.setProperty('padding-left', '0', 'important');
                        mainContent.style.setProperty('padding-right', '0', 'important');
                        mainContent.style.setProperty('left', '0', 'important');
                    }
                } else {
                    // Desktop: Icon-only sidebar
                    if (sidebar) {
                        sidebar.classList.remove('sidebar-expanded', 'sidebar-hidden');
                        sidebar.style.width = '232px';
                        sidebar.style.display = 'block';
                    }
                    if (mainContent) {
                        mainContent.style.setProperty('margin-left', '232px', 'important');
                        mainContent.style.setProperty('width', 'calc(100% - 232px)', 'important');
                    }
                }
            }
            
            // Run immediately
            fixMobileLayout();
            
            // Also run when DOM is ready (in case elements load later)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fixMobileLayout);
            }
            
            // Run on window load as well
            window.addEventListener('load', fixMobileLayout);
        })();
    </script>
    
    <!-- Sidebar Overlay (Mobile Only) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle" class="sidebar-toggle" title="Toggle Sidebar" style="display: none;">
        <i class="fas fa-chevron-left sidebar-toggle-icon" id="sidebarToggleIcon"></i>
    </button>
    
    <!-- Sidebar -->
    <aside id="sidebar">
        <div>
            <h2><span class="asm-brand-mark">C</span>CRM Pro</h2>
            <p>{{ auth()->user()->getDisplayRoleName() ?? 'Assistant Sales Manager' }}</p>
        </div>
        <nav>
            <a href="{{ route('sales-manager.dashboard') }}" class="sidebar-link {{ request()->routeIs('sales-manager.dashboard') ? 'active' : '' }}" data-label="Dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('sales-manager.tasks') }}" class="sidebar-link {{ request()->routeIs('sales-manager.tasks*') ? 'active' : '' }}" data-label="Tasks">
                <i class="fas fa-tasks"></i>
                <span>Tasks</span>
            </a>
            <a href="{{ route('sales-manager.prospects') }}" class="sidebar-link {{ request()->routeIs('sales-manager.prospects') ? 'active' : '' }}" data-label="Prospects">
                <i class="fas fa-star"></i>
                <span>Prospects</span>
            </a>
            <a href="{{ route('sales-manager.leads') }}" class="sidebar-link {{ request()->routeIs('sales-manager.leads') || (request()->routeIs('leads.show') && auth()->check() && (auth()->user()->isSalesManager() || auth()->user()->isAssistantSalesManager())) ? 'active' : '' }}" data-label="Leads">
                <i class="fas fa-user-friends"></i>
                <span>Leads</span>
            </a>
            <a href="{{ route('sales-manager.meetings') }}" class="sidebar-link {{ request()->routeIs('sales-manager.meetings*') ? 'active' : '' }}" data-label="Meetings">
                <i class="fas fa-handshake"></i>
                <span>Meetings</span>
            </a>
            <a href="{{ route('sales-manager.site-visits') }}" class="sidebar-link {{ request()->routeIs('sales-manager.site-visits*') ? 'active' : '' }}" data-label="Site Visits">
                <i class="fas fa-map-marker-alt"></i>
                <span>Site Visits</span>
            </a>
            <a href="{{ route('sales-manager.team') }}" class="sidebar-link {{ request()->routeIs('sales-manager.team') ? 'active' : '' }}" data-label="My Team">
                <i class="fas fa-users"></i>
                <span>My Team</span>
            </a>
            <a href="{{ route('sales-manager.profile') }}" class="sidebar-link {{ request()->routeIs('sales-manager.profile') ? 'active' : '' }}" data-label="Profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div id="mainContent" class="main-content-wrapper" style="min-height: 100vh; background: #F7F6F3;">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="header-top">
                    <h1 class="header-title-mobile asm-header-title" style="font-size: 24px; font-weight: 700; color: #063A1C;">
                        <span class="eyebrow">{{ auth()->user()->getDisplayRoleName() ?? 'Assistant Sales Manager' }} Workspace</span>
                        <span class="header-page-title-desktop title">@yield('page-title', 'Dashboard')</span>
                        <div class="header-user-info-mobile">
                            <span class="header-user-name-mobile">{{ auth()->user()->name }}</span>
                            <span class="header-user-role-mobile">{{ auth()->user()->getDisplayRoleName() ?? 'User' }}</span>
                        </div>
                    </h1>
                </div>
                <div class="header-actions">
                    <div class="header-actions-row">
                        <!-- Date/Time Clock -->
                        <div id="datetimeClock">
                            <div id="clockTime">--:--:--</div>
                            <div id="clockDate">-- -- ----</div>
                        </div>
                        <span class="asm-user-chip header-user-name-desktop"><span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>{{ auth()->user()->name }}</span>
                        <button type="button" id="btnEnablePush" hidden aria-hidden="true" style="display: none !important; font-size: 12px; padding: 6px 10px; background: #205A44; color: #fff; border: none; border-radius: 8px; cursor: pointer; white-space: nowrap;">Enable push notifications</button>
                        <span id="pushStatus" hidden aria-hidden="true" style="display: none !important; font-size: 12px; color: #059669;"></span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            @yield('content')
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    <nav id="mobileFooterNav">
        <a href="{{ route('sales-manager.dashboard') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('sales-manager.tasks') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.tasks*') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('sales-manager.prospects') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.prospects') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Prospects</span>
        </a>
        <a href="{{ route('sales-manager.leads') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.leads') || (request()->routeIs('leads.show') && auth()->check() && (auth()->user()->isSalesManager() || auth()->user()->isAssistantSalesManager())) ? 'active' : '' }}">
            <i class="fas fa-user-friends"></i>
            <span>Leads</span>
        </a>
        <a href="{{ route('sales-manager.meetings') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.meetings*') ? 'active' : '' }}">
            <i class="fas fa-handshake"></i>
            <span>Meetings</span>
        </a>
        <a href="{{ route('sales-manager.site-visits') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.site-visits*') ? 'active' : '' }}">
            <i class="fas fa-map-marker-alt"></i>
            <span>Visits</span>
        </a>
        <a href="{{ route('sales-manager.team') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.team') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Team</span>
        </a>
        <a href="{{ route('sales-manager.profile') }}" class="footer-nav-link {{ request()->routeIs('sales-manager.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Custom Notification System -->
    <div id="notificationOverlay" class="fixed inset-0 z-[9999] pointer-events-none flex items-center justify-center" style="display: none;">
        <div id="customNotification" class="bg-white rounded-lg shadow-2xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-0" style="pointer-events: auto;">
            <div class="flex items-center justify-center mb-4">
                <div class="tick-icon w-16 h-16 rounded-full flex items-center justify-center bg-green-100">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
            </div>
            <p id="notificationMessage" class="text-center text-gray-800 font-medium text-lg"></p>
        </div>
    </div>

    <style>
        #customNotification.show {
            animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }
        
        #customNotification.hide {
            animation: popOut 0.3s ease-in forwards;
        }
        
        @keyframes popIn {
            0% {
                transform: scale(0) translateY(-20px);
                opacity: 0;
            }
            50% {
                transform: scale(1.05) translateY(0);
            }
            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes popOut {
            0% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
            100% {
                transform: scale(0.8) translateY(-20px);
                opacity: 0;
            }
        }
        
        .tick-icon {
            animation: tickAnimation 0.6s ease-in-out;
        }
        
        @keyframes tickAnimation {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
        
        #customNotification.error .tick-icon {
            background: #fee2e2;
        }
        
        #customNotification.error .tick-icon i {
            color: #dc2626;
        }
        
        #customNotification.error .tick-icon i:before {
            content: '\f00d';
        }
        
        #customNotification.warning .tick-icon {
            background: #fef3c7;
        }
        
        #customNotification.warning .tick-icon i {
            color: #d97706;
        }
        
        #customNotification.warning .tick-icon i:before {
            content: '\f071';
        }
    </style>

    <script>
        function showNotification(message, type = 'success', duration = 3000) {
            const overlay = document.getElementById('notificationOverlay');
            const notification = document.getElementById('customNotification');
            const messageEl = document.getElementById('notificationMessage');
            const tickIcon = notification.querySelector('.tick-icon');
            
            // Remove previous type classes
            notification.classList.remove('success', 'error', 'warning');
            
            // Set message and type
            messageEl.textContent = message;
            notification.classList.add(type);
            
            // Show overlay and notification
            overlay.style.display = 'flex';
            notification.style.transform = 'scale(0)';
            
            // Trigger animation
            setTimeout(() => {
                notification.classList.remove('hide');
                notification.classList.add('show');
            }, 10);
            
            // Hide after duration
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                    notification.classList.remove('hide');
                }, 300);
            }, duration);
        }
        
        // Auto-close on click
        document.getElementById('customNotification')?.addEventListener('click', function() {
            const overlay = document.getElementById('notificationOverlay');
            const notification = document.getElementById('customNotification');
            notification.classList.remove('show');
            notification.classList.add('hide');
            
            setTimeout(() => {
                overlay.style.display = 'none';
                notification.classList.remove('hide');
            }, 300);
        });
    </script>

    @stack('scripts')
    <!-- FCM Push: Firebase Cloud Messaging for notifications -->
    <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js"></script>
    <script>
    (function() {
        var configMeta = document.querySelector('meta[name="firebase-config"]');
        var vapidMeta = document.querySelector('meta[name="firebase-vapid-key"]');
        if (!configMeta || !vapidMeta) return;
        var firebaseConfig;
        try { firebaseConfig = JSON.parse(configMeta.content); } catch(e) { return; }
        if (!firebaseConfig || !firebaseConfig.api_key) return;

        var btn = document.getElementById('btnEnablePush');
        var statusEl = document.getElementById('pushStatus');
        var vapidKey = vapidMeta.content;

        var fbConfig = {
            apiKey: firebaseConfig.api_key,
            authDomain: firebaseConfig.auth_domain,
            projectId: firebaseConfig.project_id,
            storageBucket: firebaseConfig.storage_bucket,
            messagingSenderId: firebaseConfig.messaging_sender_id,
            appId: firebaseConfig.app_id
        };

        firebase.initializeApp(fbConfig);
        var messaging = firebase.messaging();

        function getAuthToken() {
            var meta = document.querySelector('meta[name="api-token"]');
            if (meta && meta.content) return meta.content;
            try { return localStorage.getItem('telecaller_token') || localStorage.getItem('auth_token') || ''; } catch(e) { return ''; }
        }

        function sendFcmTokenToServer(fcmToken) {
            var authToken = getAuthToken();
            if (!authToken) { if (statusEl) statusEl.textContent = 'Login again and try.'; return; }
            var url = (typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : (window.location.origin + '/api')) + '/fcm-subscription';
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + authToken },
                body: JSON.stringify({ fcm_token: fcmToken, device_type: 'web' })
            }).then(function(r) {
                if (statusEl) statusEl.textContent = r.ok ? 'Push enabled.' : 'Failed (' + r.status + ')';
            }).catch(function() { if (statusEl) statusEl.textContent = 'Request failed.'; });
        }

        function initFcm() {
            if (statusEl) statusEl.textContent = 'Registering...';
            navigator.serviceWorker.register('/fcm-sw.js').then(function(reg) {
                messaging.getToken({ vapidKey: vapidKey, serviceWorkerRegistration: reg }).then(function(token) {
                    if (token) sendFcmTokenToServer(token);
                    else if (statusEl) statusEl.textContent = 'No FCM token received.';
                }).catch(function(e) { if (statusEl) statusEl.textContent = 'Token error: ' + (e.message || ''); });
            }).catch(function() { if (statusEl) statusEl.textContent = 'SW error.'; });
        }

        messaging.onMessage(function(payload) {
            var n = payload.notification || payload.data || {};
            if (typeof showLeadAssignedPopup === 'function') {
                showLeadAssignedPopup({ title: n.title, message: n.body });
            }
        });

        function runEnablePush() {
            if (Notification.permission === 'granted') { initFcm(); }
            else if (Notification.permission === 'default') {
                if (statusEl) statusEl.textContent = 'Allow in browser prompt...';
                Notification.requestPermission().then(function(p) { if (p === 'granted') initFcm(); else if (statusEl) statusEl.textContent = 'Denied.'; });
            } else { if (statusEl) statusEl.textContent = 'Notifications blocked. Enable in browser settings.'; }
        }

        if (btn) { btn.style.display = 'inline-block'; btn.onclick = runEnablePush; }
        if (Notification.permission === 'granted') { initFcm(); }
        else if (Notification.permission === 'default') {
            Notification.requestPermission().then(function(p) { if (p === 'granted') initFcm(); });
        }
    })();
    </script>
    <!-- Chatbot Assistant Widget -->
    @include('components.chatbot-widget')
    
    <!-- Chatbot Assistant Script -->
    <script src="{{ asset('js/chatbot-assistant.js') }}"></script>
    
    <!-- Live Clock Functionality -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeElement = document.getElementById('clockTime');
            const dateElement = document.getElementById('clockDate');
            
            if (timeElement && dateElement) {
                // Format time: HH:MM:SS
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                timeElement.textContent = `${hours}:${minutes}:${seconds}`;
                
                // Format date: DD MMM YYYY
                const date = now.toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                dateElement.textContent = date;
            }
        }
        
        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);
        
        // Sidebar and main content setup
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            function updateLayout() {
                const isMobile = window.innerWidth <= 767;
                
                if (sidebar) {
                    if (isMobile) {
                        // Mobile: Hide sidebar completely
                        sidebar.style.display = 'none';
                        sidebar.style.width = '0';
                    } else {
                        // Desktop: Icon-only (64px)
                        sidebar.classList.remove('sidebar-expanded', 'sidebar-hidden');
                        sidebar.style.width = '232px';
                        sidebar.style.display = 'block';
                    }
                }
                
                if (mainContent) {
                    if (isMobile) {
                        // Mobile: Full width, no left margin - override inline style
                        mainContent.style.setProperty('margin-left', '0', 'important');
                        mainContent.style.setProperty('width', '100%', 'important');
                        mainContent.style.setProperty('padding-left', '0', 'important');
                        mainContent.style.setProperty('padding-right', '0', 'important');
                        mainContent.style.setProperty('left', '0', 'important');
                    } else {
                        // Desktop: 64px margin for icon-only sidebar
                        mainContent.style.setProperty('margin-left', '232px', 'important');
                        mainContent.style.setProperty('width', 'calc(100% - 232px)', 'important');
                    }
                }
            }
            
            // Initial setup
            updateLayout();
            
            // Update on window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(updateLayout, 100);
            });
        });
        
        // Do NOT unregister Service Worker on production – PWA push notifications need it.
        // (Unregister only on localhost for development cache-disable if needed.)
        
        // Clear browser cache on page load (development mode)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname.includes('local')) {
            // Force reload without cache
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        }
    </script>
    @auth
    <!-- Lead assigned modal with ringtone -->
    <style>
        @keyframes bellRing { 0%,100%{transform:rotate(0)} 15%{transform:rotate(14deg)} 30%{transform:rotate(-14deg)} 45%{transform:rotate(10deg)} 60%{transform:rotate(-10deg)} 75%{transform:rotate(4deg)} 90%{transform:rotate(-4deg)} }
        @keyframes pulseGlow { 0%,100%{box-shadow:0 0 0 0 rgba(34,197,94,.4)} 50%{box-shadow:0 0 0 16px rgba(34,197,94,0)} }
        #lead-assigned-overlay:not(.hidden) #lead-ring-bell { animation: bellRing .8s ease-in-out infinite; }
        #lead-assigned-overlay:not(.hidden) #lead-assigned-modal { animation: pulseGlow 2s ease-in-out infinite; }
    </style>
    <div id="lead-assigned-overlay" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4 hidden" aria-hidden="true">
        <div id="lead-assigned-modal" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 relative" role="dialog" aria-labelledby="lead-assigned-title">
            <button type="button" id="lead-assigned-close-x" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl leading-none" aria-label="Close">&times;</button>
            <div class="flex justify-center mb-3">
                <span id="lead-ring-bell" style="font-size:2.5rem;display:inline-block;">&#128276;</span>
            </div>
            <h2 id="lead-assigned-title" class="text-xl font-bold text-gray-900 mb-2 text-center">New lead assigned</h2>
            <p id="lead-assigned-message" class="text-gray-600 mb-6 text-center">You have a new lead assigned. View leads to see details and call.</p>
            <div id="lead-ringtone-timer" class="text-center text-sm text-gray-400 mb-4 hidden">Ringing... <span id="lead-ringtone-countdown">30</span>s</div>
            <div class="flex flex-wrap gap-3 justify-center">
                <a id="lead-assigned-view-btn" href="{{ route('leads.index') }}" class="px-4 py-2 rounded-lg font-semibold text-white bg-[#063A1C] hover:bg-[#205A44] transition">View leads</a>
                <a id="lead-assigned-call-btn" href="#" class="px-4 py-2 rounded-lg font-semibold bg-green-600 text-white hover:bg-green-700 transition hidden">Call</a>
                <button type="button" id="lead-assigned-cancel-btn" class="px-4 py-2 rounded-lg font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Cancel</button>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var overlay = document.getElementById('lead-assigned-overlay');
        var titleEl = document.getElementById('lead-assigned-title');
        var messageEl = document.getElementById('lead-assigned-message');
        var viewBtn = document.getElementById('lead-assigned-view-btn');
        var callBtn = document.getElementById('lead-assigned-call-btn');
        var timerEl = document.getElementById('lead-ringtone-timer');
        var countdownEl = document.getElementById('lead-ringtone-countdown');
        var viewUrlDefault = viewBtn ? viewBtn.getAttribute('href') : '';

        var leadRingtone = null;
        var ringtoneTimeout = null;
        var countdownInterval = null;

        function stopRingtone() {
            try {
                if (leadRingtone) { leadRingtone.pause(); leadRingtone.currentTime = 0; }
                clearTimeout(ringtoneTimeout);
                clearInterval(countdownInterval);
                if (timerEl) timerEl.classList.add('hidden');
            } catch(e) {}
        }

        function startRingtone() {
            stopRingtone();
            try {
                leadRingtone = new Audio('/sounds/lead-ringtone.mp3');
                leadRingtone.loop = true;
                leadRingtone.volume = 1.0;
                leadRingtone.play().catch(function(e) { console.warn('Ringtone autoplay blocked:', e); });

                var seconds = 30;
                if (countdownEl) countdownEl.textContent = seconds;
                if (timerEl) timerEl.classList.remove('hidden');
                countdownInterval = setInterval(function() {
                    seconds--;
                    if (countdownEl) countdownEl.textContent = seconds;
                    if (seconds <= 0) clearInterval(countdownInterval);
                }, 1000);

                ringtoneTimeout = setTimeout(function() { stopRingtone(); }, 30000);
            } catch(e) { console.warn('Ringtone error:', e); }
        }

        document.addEventListener('click', function() {
            if (!window._audioUnlocked) {
                try {
                    var s = new Audio('/sounds/lead-ringtone.mp3');
                    s.volume = 0;
                    s.play().then(function() { s.pause(); window._audioUnlocked = true; }).catch(function(){});
                } catch(e) {}
            }
        }, { once: true });

        window.showLeadAssignedPopup = function(options) {
            var o = options || {};
            if (titleEl) titleEl.textContent = o.title || 'New lead assigned';
            if (messageEl) messageEl.textContent = o.message || 'You have a new lead assigned. View leads to see details and call.';
            if (viewUrlDefault && viewBtn) viewBtn.href = o.viewUrl || viewUrlDefault;
            if (callBtn) {
                if (o.leadPhone) {
                    callBtn.href = 'tel:' + (o.leadPhone + '').replace(/\D/g, '');
                    callBtn.classList.remove('hidden');
                } else { callBtn.classList.add('hidden'); }
            }
            if (overlay) overlay.classList.remove('hidden');
            startRingtone();
        };
        window.closeLeadAssignedModal = function() {
            if (overlay) overlay.classList.add('hidden');
            stopRingtone();
        };
        if (document.getElementById('lead-assigned-close-x')) document.getElementById('lead-assigned-close-x').addEventListener('click', closeLeadAssignedModal);
        if (document.getElementById('lead-assigned-cancel-btn')) document.getElementById('lead-assigned-cancel-btn').addEventListener('click', closeLeadAssignedModal);
        if (overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) closeLeadAssignedModal(); });
        var uid = document.querySelector('meta[name="user-id"]') && document.querySelector('meta[name="user-id"]').getAttribute('content');
        var pk = document.querySelector('meta[name="pusher-key"]') && document.querySelector('meta[name="pusher-key"]').getAttribute('content');
        if (uid && pk && typeof Pusher !== 'undefined') {
            try {
                var pusher = new Pusher(pk, { cluster: (document.querySelector('meta[name="pusher-cluster"]') && document.querySelector('meta[name="pusher-cluster"]').getAttribute('content')) || 'mt1', encrypted: true, authEndpoint: '/broadcasting/auth' });
                pusher.subscribe('private-user.' + uid).bind('lead.assigned', function(data) {
                    var lead = data.lead || {};
                    showLeadAssignedPopup({ title: 'New lead assigned', message: 'You have 1 new lead assigned: ' + (lead.name || 'Lead') + '. View leads to see details and call.', viewUrl: viewUrlDefault, leadPhone: lead.phone || '', leadName: lead.name || 'Lead' });
                });
            } catch (e) { console.warn('Pusher lead-assigned:', e); }
        }
    })();
    </script>
    @endauth

    <!-- Include Meeting Post-Call Popup Component -->
    @include('components.meeting-post-call-popup')
    
    <!-- Include Meeting Section Modals (for reschedule, complete, mark dead) -->
    @if(request()->routeIs('sales-manager.meetings') || request()->routeIs('sales-manager.tasks'))
    <!-- Reschedule Meeting Modal -->
    <div id="rescheduleMeetingModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <h3 id="rescheduleModalTitle" style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Reschedule</h3>
            <input type="hidden" id="rescheduleModalType" value="meeting">
            <input type="hidden" id="rescheduleModalId" value="">
            
            <div class="form-group">
                <label>New Scheduled Date & Time <span style="color: #ef4444;">*</span></label>
                <input type="datetime-local" id="rescheduleScheduledAt" required
                    class="form-group input" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                <small style="color: #6b7280;">Select a future date and time</small>
            </div>

            <div class="form-group">
                <label>Reason for Rescheduling <span style="color: #ef4444;">*</span></label>
                <textarea id="rescheduleReason" rows="4" placeholder="Enter reason for rescheduling..." required
                    class="form-group textarea" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeRescheduleMeetingModal()">Cancel</button>
                <button type="button" class="btn" style="background: #f59e0b; color: white;" onclick="submitRescheduleMeeting()">Reschedule</button>
            </div>
        </div>
    </div>

    <!-- Complete Meeting Modal -->
    <div id="completeMeetingModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Complete Meeting</h3>
            <p style="color: #ef4444; margin-bottom: 16px;"><strong>Proof photos are required to complete the meeting.</strong></p>
            
            <div class="form-group">
                <label>Proof Photos <span style="color: #ef4444;">*</span></label>
                <input type="file" id="proofPhotosInput" multiple accept="image/*" onchange="handleProofPhotosChange(event)" required>
                <div id="proofPhotosPreview" style="display: flex; flex-wrap: wrap; margin-top: 10px;"></div>
                <small style="color: #6b7280;">Upload at least one photo as proof. Max 5MB per image.</small>
            </div>

            <div class="form-group">
                <label>Feedback</label>
                <textarea id="meetingFeedback" rows="3" placeholder="Meeting feedback..."></textarea>
            </div>

            <div class="form-group">
                <label>Rating</label>
                <select id="meetingRating">
                    <option value="">Select rating</option>
                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea id="meetingNotes" rows="3" placeholder="Additional notes..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeCompleteMeetingModal()">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitCompleteMeeting()">Submit</button>
            </div>
        </div>
    </div>

    <!-- Mark as Dead Modal -->
    <div id="markDeadModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Mark as Dead</h3>
            <p style="color: #ef4444; margin-bottom: 16px;">This will mark the meeting and associated lead as dead. This action cannot be undone.</p>
            
            <div class="form-group">
                <label>Reason <span style="color: #ef4444;">*</span></label>
                <textarea id="deadReason" rows="4" placeholder="Enter reason for marking as dead..." required></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeMarkDeadModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitMarkDead()">Mark as Dead</button>
            </div>
        </div>
    </div>
    
    <script>
    // Make meeting modal functions available globally if not already defined
    if (typeof showRescheduleMeetingModal === 'undefined') {
        function showRescheduleMeetingModal(id) {
            if (typeof currentMeetingId !== 'undefined') {
                window.currentMeetingId = id;
            }
            // Get meeting details
            apiCall(`/meetings/${id}`).then(meeting => {
                if (meeting && meeting.id) {
                    const scheduledDate = new Date(meeting.scheduled_at);
                    const minDateTime = new Date();
                    minDateTime.setDate(minDateTime.getDate() + 1);
                    minDateTime.setHours(0, 0, 0, 0);
                    
                    document.getElementById('rescheduleScheduledAt').value = '';
                    document.getElementById('rescheduleReason').value = '';
                    document.getElementById('rescheduleModalTitle').textContent = 'Reschedule Meeting';
                    document.getElementById('rescheduleModalType').value = 'meeting';
                    document.getElementById('rescheduleModalId').value = id;
                    document.getElementById('rescheduleScheduledAt').min = minDateTime.toISOString().slice(0, 16);
                    document.getElementById('rescheduleMeetingModal').classList.add('show');
                }
            });
        }
    }
    
    if (typeof closeRescheduleMeetingModal === 'undefined') {
        function closeRescheduleMeetingModal() {
            document.getElementById('rescheduleMeetingModal').classList.remove('show');
            document.getElementById('rescheduleScheduledAt').value = '';
            document.getElementById('rescheduleReason').value = '';
            if (typeof currentMeetingId !== 'undefined') {
                window.currentMeetingId = null;
            }
        }
    }
    
    if (typeof submitRescheduleMeeting === 'undefined') {
        async function submitRescheduleMeeting() {
            const type = document.getElementById('rescheduleModalType').value;
            const id = document.getElementById('rescheduleModalId').value;
            const scheduledAt = document.getElementById('rescheduleScheduledAt').value;
            const reason = document.getElementById('rescheduleReason').value.trim();

            if (!scheduledAt) {
                alert('Please select a new scheduled date and time');
                return;
            }

            if (!reason) {
                alert('Please provide a reason for rescheduling');
                return;
            }

            try {
                const result = await apiCall(`/${type === 'meeting' ? 'meetings' : 'site-visits'}/${id}/reschedule`, {
                    method: 'POST',
                    body: JSON.stringify({
                        scheduled_at: scheduledAt,
                        reason: reason,
                    }),
                });

                if (result && result.success) {
                    // Complete calling task if pending
                    if (window.pendingTaskCompletion) {
                        await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                        window.pendingTaskCompletion = null;
                    }
                    if (typeof showNotification === 'function') {
                        showNotification(result.message || 'Rescheduled successfully! Verification required.', 'success', 3000);
                    } else {
                        alert(result.message || 'Rescheduled successfully! Verification required.');
                    }
                    closeRescheduleMeetingModal();
                    if (typeof loadMeetings === 'function') {
                        loadMeetings();
                    }
                    if (typeof loadTasks === 'function') {
                        loadTasks();
                    }
                } else {
                    alert(result.message || 'Failed to reschedule');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            }
        }
    }
    
    if (typeof showCompleteMeetingModal === 'undefined') {
        function showCompleteMeetingModal(id) {
            if (typeof currentMeetingId !== 'undefined') {
                window.currentMeetingId = id;
            }
            document.getElementById('completeMeetingModal').classList.add('show');
        }
    }
    
    if (typeof closeCompleteMeetingModal === 'undefined') {
        function closeCompleteMeetingModal() {
            document.getElementById('completeMeetingModal').classList.remove('show');
            const photosInput = document.getElementById('proofPhotosInput');
            if (photosInput) {
                photosInput.value = '';
            }
            const preview = document.getElementById('proofPhotosPreview');
            if (preview) {
                preview.innerHTML = '';
            }
            if (typeof currentMeetingId !== 'undefined') {
                window.currentMeetingId = null;
            }
        }
    }
    
    if (typeof handleProofPhotosChange === 'undefined') {
        function handleProofPhotosChange(event) {
            const files = event.target.files;
            const preview = document.getElementById('proofPhotosPreview');
            if (!preview) return;
            preview.innerHTML = '';
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.style.margin = '5px';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        }
    }
    
    if (typeof submitCompleteMeeting === 'undefined') {
        async function submitCompleteMeeting() {
            const meetingId = typeof currentMeetingId !== 'undefined' ? window.currentMeetingId : null;
            if (!meetingId) return;

            const formData = new FormData();
            const photosInput = document.getElementById('proofPhotosInput');
            
            if (!photosInput || !photosInput.files || photosInput.files.length === 0) {
                alert('Please upload at least one proof photo');
                return;
            }

            for (let i = 0; i < photosInput.files.length; i++) {
                formData.append('proof_photos[]', photosInput.files[i]);
            }

            const feedback = document.getElementById('meetingFeedback')?.value;
            const rating = document.getElementById('meetingRating')?.value;
            const notes = document.getElementById('meetingNotes')?.value;

            if (feedback) formData.append('feedback', feedback);
            if (rating) formData.append('rating', rating);
            if (notes) formData.append('meeting_notes', notes);

            try {
                const token = typeof getToken === 'function' ? getToken() : (window.API_TOKEN || document.querySelector('meta[name="api-token"]')?.content);
                const apiBase = window.API_BASE_URL || '/api';
                const response = await fetch(`${apiBase}/meetings/${meetingId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (result && result.success) {
                    // Complete calling task if pending
                    if (window.pendingTaskCompletion) {
                        await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                        window.pendingTaskCompletion = null;
                    }
                    alert('Meeting completed with proof photos! Awaiting verification.');
                    closeCompleteMeetingModal();
                    if (typeof loadMeetings === 'function') {
                        loadMeetings();
                    }
                    if (typeof loadTasks === 'function') {
                        loadTasks();
                    }
                } else {
                    alert(result.message || 'Failed to complete meeting');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            }
        }
    }
    
    if (typeof showMarkDeadModal === 'undefined') {
        function showMarkDeadModal(type, id) {
            if (typeof currentMeetingId !== 'undefined') {
                window.currentMeetingId = id;
            }
            const deadReasonInput = document.getElementById('deadReason');
            if (deadReasonInput) {
                deadReasonInput.value = '';
            }
            document.getElementById('markDeadModal').classList.add('show');
        }
    }
    
    if (typeof closeMarkDeadModal === 'undefined') {
        function closeMarkDeadModal() {
            document.getElementById('markDeadModal').classList.remove('show');
            const deadReasonInput = document.getElementById('deadReason');
            if (deadReasonInput) {
                deadReasonInput.value = '';
            }
            if (typeof currentMeetingId !== 'undefined') {
                window.currentMeetingId = null;
            }
        }
    }
    
    if (typeof submitMarkDead === 'undefined') {
        async function submitMarkDead() {
            const meetingId = typeof currentMeetingId !== 'undefined' ? window.currentMeetingId : null;
            if (!meetingId) return;

            const reason = document.getElementById('deadReason')?.value.trim();
            if (!reason) {
                alert('Please provide a reason for marking as dead');
                return;
            }

            const result = await apiCall(`/meetings/${meetingId}/mark-dead`, {
                method: 'POST',
                body: JSON.stringify({ reason }),
            });

            if (result && result.success) {
                // Complete calling task if pending
                if (window.pendingTaskCompletion) {
                    await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                    window.pendingTaskCompletion = null;
                }
                alert('Meeting marked as dead successfully');
                closeMarkDeadModal();
                if (typeof loadMeetings === 'function') {
                    loadMeetings();
                }
                if (typeof loadTasks === 'function') {
                    loadTasks();
                }
            } else {
                alert(result.message || 'Failed to mark as dead');
            }
        }
    }
    
    if (typeof cancelMeeting === 'undefined') {
        async function cancelMeeting(id) {
            if (!confirm('Cancel this meeting?')) return;

            const result = await apiCall(`/meetings/${id}/cancel`, {
                method: 'POST',
            });

            if (result && result.success) {
                // Complete calling task if pending
                if (window.pendingTaskCompletion) {
                    await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                    window.pendingTaskCompletion = null;
                }
                alert('Meeting cancelled');
                if (typeof loadMeetings === 'function') {
                    loadMeetings();
                }
                if (typeof loadTasks === 'function') {
                    loadTasks();
                }
            } else {
                alert(result.message || 'Failed to cancel meeting');
            }
        }
    }
    
    if (typeof completeCallingTask === 'undefined') {
        async function completeCallingTask(taskId, taskType) {
            if (!taskId) return true;
            
            const apiToken = window.API_TOKEN || document.querySelector('meta[name="api-token"]')?.content;
            const apiBase = window.API_BASE_URL || '/api';
            
            if (!apiToken) {
                console.warn('API token not found, skipping task completion');
                return false;
            }
            
            try {
                let endpoint;
                if (taskType === 'Task') {
                    endpoint = `${apiBase}/sales-manager/tasks/${taskId}/complete`;
                } else {
                    endpoint = `${apiBase}/telecaller/tasks/${taskId}/complete`;
                }
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${apiToken}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    return true;
                } else {
                    const result = await response.json();
                    console.error('Failed to complete task:', result.message || 'Unknown error');
                    return false;
                }
            } catch (error) {
                console.error('Error completing task:', error);
                return false;
            }
        }
    }
    </script>
    @endif
</body>
</html>
