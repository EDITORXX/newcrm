<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Base CRM')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // Ensure token exists in session — create once if missing (e.g. old sessions)
        if (auth()->check()) {
            $__existingToken = session('api_token');
            $__tokenValid = false;
            if ($__existingToken) {
                $__tokenId = explode('|', $__existingToken)[0];
                $__tokenValid = \Laravel\Sanctum\PersonalAccessToken::find($__tokenId) !== null;
            }
            if (!$__tokenValid) {
                // Purana invalid token revoke karo
                if ($__existingToken) {
                    $__tokenId = explode('|', $__existingToken)[0];
                    \Laravel\Sanctum\PersonalAccessToken::find($__tokenId)?->delete();
                }
                $__token = auth()->user()->createToken('web-session-token')->plainTextToken;
                session(['api_token' => $__token]);
            }
        }
    @endphp
    <meta name="api-token" content="{{ session('api_token', '') }}">
    <meta name="user-id" content="{{ auth()->check() ? auth()->user()->id : '' }}">
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}">
    <meta name="firebase-config" content="{{ json_encode(config('firebase.web')) }}">
    <meta name="firebase-vapid-key" content="{{ config('firebase.vapid_key') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'primary-dark': 'var(--text-color)',
                        'primary': 'var(--text-color)',
                        'secondary': 'var(--link-color)',
                        'brand-bg': '#F7F6F3',
                        'brand-border': '#E5DED4',
                        'text-muted': '#B3B5B4',
                    },
                },
            },
        }
    </script>
    
    <!-- Additional scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: {{ primary_color() }};
            --secondary-color: {{ secondary_color() }};
            --accent-color: {{ accent_color() }};
            --gradient-start: {{ gradient_start_color() }};
            --gradient-end: {{ gradient_end_color() }};
            --text-color: {{ text_color() }};
            --link-color: {{ link_color() }};
            --background-color: {{ background_color() }};
            --text-primary: {{ text_color() }};
            --text-secondary: {{ link_color() }};
            --text-muted: #B3B5B4;
            --border-color: {{ primary_color() }};
            --avatar-bg: {{ gradient_start_color() }};
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        /* Prevent transition flash on page load */
        body.no-transition *, body.no-transition *::before, body.no-transition *::after { transition: none !important; animation: none !important; }
        /* Transitions only active after page is ready */
        body.sidebar-ready #sidebar { transition: width 0.3s ease-in-out, transform 0.3s ease-in-out; }
        body.sidebar-ready #mainContent { transition: margin-left 0.3s ease-in-out; }
        /* Pre-render: set correct sidebar width on <html> BEFORE body exists */
        html.pre-nav-text #sidebar { width: 256px !important; min-width: 256px !important; }
        html.pre-nav-text #mainContent { margin-left: 256px !important; }
        html.pre-nav-icons #sidebar { width: 64px !important; max-width: 64px !important; }
        html.pre-nav-icons #mainContent { margin-left: 64px !important; }
        .container { max-width: 100%; margin: 0 auto; padding: 20px; width: 100%; box-sizing: border-box; }
        .header { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s; }
        
        /* Branded Button Classes */
        .btn-brand-primary, .btn-brand-gradient {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            @else
                background-color: var(--primary-color);
            @endif
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-brand-primary:hover, .btn-brand-gradient:hover {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color));
            @else
                background-color: var(--secondary-color);
            @endif
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn-brand-secondary {
            background-color: var(--secondary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-brand-secondary:hover {
            background-color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Legacy button classes - now use branding */
        .btn-primary, .btn-success, .btn-secondary, .btn-warning {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            @else
                background-color: var(--primary-color) !important;
            @endif
            color: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover, .btn-success:hover, .btn-secondary:hover, .btn-warning:hover {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            @else
                background-color: var(--secondary-color) !important;
            @endif
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Dynamic gradient button class - uses CSS variables */
        .btn-gradient-dynamic {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            color: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-gradient-dynamic:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Override all gradient buttons to use CSS variables - High specificity */
        body a.bg-gradient-to-r,
        body button.bg-gradient-to-r,
        body a[class*="from-[#063A1C]"],
        body button[class*="from-[#063A1C]"],
        body a[class*="from-[#205A44]"],
        body button[class*="from-[#205A44]"] {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            border: none !important;
        }
        body a.bg-gradient-to-r:hover,
        body button.bg-gradient-to-r:hover,
        body a[class*="from-[#063A1C]"]:hover,
        body button[class*="from-[#063A1C]"]:hover,
        body a[class*="from-[#205A44]"]:hover,
        body button[class*="from-[#205A44]"]:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
        }
        
        /* Override hover classes */
        body a[class*="hover:from-[#205A44]"]:hover,
        body button[class*="hover:from-[#205A44]"]:hover,
        body a[class*="hover:to-[#15803d]"]:hover,
        body button[class*="hover:to-[#15803d]"]:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
        }
        
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        
        /* Branding Utility Classes */
        .text-brand-primary {
            color: var(--text-color) !important;
        }
        .text-brand-secondary {
            color: var(--link-color) !important;
        }
        .text-brand-muted {
            color: var(--text-muted) !important;
        }
        .border-brand {
            border-color: var(--primary-color) !important;
        }
        .border-brand-secondary {
            border-color: var(--secondary-color) !important;
        }
        .bg-brand-avatar {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            @else
                background-color: var(--primary-color) !important;
            @endif
        }
        
        /* Global CSS Override Rules - Force hardcoded Tailwind classes to use CSS variables */
        /* Override hardcoded text colors */
        [class*="text-[#063A1C]"], [class*="text-[#205A44]"] {
            color: var(--text-color) !important;
        }
        
        /* Override hardcoded border colors */
        [class*="border-[#063A1C]"], [class*="border-[#205A44]"] {
            border-color: var(--primary-color) !important;
        }
        
        /* Override hardcoded background colors for avatars and brand elements */
        [class*="bg-[#063A1C]"], [class*="bg-[#205A44]"] {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
            @else
                background-color: var(--primary-color) !important;
            @endif
        }
        
        /* Override hardcoded gradient classes for cards */
        [class*="from-[#205A44]"], [class*="from-[#063A1C]"], 
        [class*="to-[#063A1C]"], [class*="to-[#205A44]"],
        [class*="bg-gradient-to-br"][class*="from-[#205A44]"],
        [class*="bg-gradient-to-br"][class*="from-[#063A1C]"] {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
        }
        
        /* Override hover states for hardcoded backgrounds */
        [class*="hover:bg-[#205A44]"], [class*="hover:bg-[#063A1C]"], [class*="hover:bg-[#15803d]"] {
            @if(use_gradient())
                background: linear-gradient(135deg, var(--gradient-end), var(--accent-color)) !important;
            @else
                background-color: var(--secondary-color) !important;
            @endif
        }
        
        /* Override focus ring colors */
        [class*="focus:ring-[#205A44]"], [class*="focus:ring-[#063A1C]"] {
            --tw-ring-color: var(--primary-color) !important;
        }
        
        /* Override focus border colors */
        [class*="focus:border-[#205A44]"], [class*="focus:border-[#063A1C]"] {
            border-color: var(--primary-color) !important;
        }
        
        /* Override hover text colors */
        [class*="hover:text-[#205A44]"], [class*="hover:text-[#063A1C]"] {
            color: var(--link-color) !important;
        }
        
        /* Override ring colors (for focus states) */
        [class*="ring-[#063A1C]"], [class*="ring-[#205A44]"] {
            --tw-ring-color: var(--primary-color) !important;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }
        
        /* Sidebar width: controlled here only (no Tailwind w-64) to prevent overlap with main content */
        #sidebar {
            width: 64px;
            min-width: 64px;
            max-width: 64px;
        }
        /* Sidebar nav mode
           - Default is icon mode (body.nav-icons)
           - Toggle to text mode (body.nav-text) using localStorage
        */
        body.nav-icons #sidebar {
            width: 64px !important;
            min-width: 64px !important;
            max-width: 64px !important;
        }
        
        body.nav-icons #sidebar nav {
            padding: 0 12px !important;
        }
        
        body.nav-icons #sidebar h2,
        body.nav-icons #sidebar p {
            display: none !important;
        }
        
        body.nav-icons #sidebar .sidebar-link {
            justify-content: center;
            padding: 12px !important;
            font-size: 0 !important;
        }
        
        body.nav-icons #sidebar .sidebar-link i {
            margin-right: 0 !important;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        body.nav-icons #leadsMenuIcon,
        body.nav-icons #projectsMenuIcon {
            display: none !important;
        }
        
        body.nav-icons #leadsSubMenu,
        body.nav-icons #projectsSubMenu {
            padding-left: 0 !important;
        }

        /* Keep main content aligned with sidebar width - prevent sidebar overlap */
        #mainContent {
            margin-left: 256px;
            transition: margin-left 0.3s ease-in-out;
        }
        body.nav-icons #mainContent {
            margin-left: 64px !important;
        }
        body.nav-text #sidebar {
            width: 256px !important;
            min-width: 256px !important;
            max-width: 256px !important;
        }
        body.nav-text #mainContent {
            margin-left: 256px !important;
        }
        body.nav-text #mainContent,
        body.nav-text div#mainContent,
        html body.nav-text #mainContent {
            margin-left: 256px !important;
        }
        body.nav-text #mainContent, html.pre-nav-text #mainContent {
            margin-left: 256px !important;
        }
        /* Failsafe: also support mode via sidebar classes */
        #sidebar.sidebar-icons {
            width: 64px !important;
            min-width: 64px !important;
            max-width: 64px !important;
        }
        #sidebar.sidebar-icons nav {
            padding: 0 12px !important;
        }
        #sidebar.sidebar-icons h2,
        #sidebar.sidebar-icons p {
            display: none !important;
        }
        #sidebar.sidebar-icons .sidebar-link {
            justify-content: center !important;
            padding: 12px !important;
            font-size: 0 !important;
        }
        #sidebar.sidebar-icons .sidebar-link i {
            margin-right: 0 !important;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        #sidebar.sidebar-icons #leadsMenuIcon,
        #sidebar.sidebar-icons #projectsMenuIcon {
            display: none !important;
        }
        #sidebar.sidebar-text {
            width: 256px !important;
            min-width: 256px !important;
            max-width: 256px !important;
        }
        #sidebar.sidebar-text nav {
            padding: 0 20px !important;
        }
        #sidebar.sidebar-text h2,
        #sidebar.sidebar-text p {
            display: block !important;
        }
        #sidebar.sidebar-text .sidebar-link {
            justify-content: flex-start !important;
            padding: 12px 16px !important;
            font-size: 14px !important;
        }
        #sidebar.sidebar-text .sidebar-link i {
            margin-right: 10px !important;
            font-size: 14px !important;
            width: 20px !important;
        }
        #sidebar.sidebar-text #leadsMenuIcon,
        #sidebar.sidebar-text #projectsMenuIcon {
            display: inline-block !important;
        }
        /* Final mode guards: body mode always wins, prevents mixed icon/text state */
        body.nav-text #sidebar {
            width: 256px !important;
            min-width: 256px !important;
            max-width: 256px !important;
        }
        body.nav-text #sidebar nav {
            padding: 0 20px !important;
        }
        body.nav-text #sidebar h2,
        body.nav-text #sidebar p {
            display: block !important;
        }
        body.nav-text #sidebar .sidebar-link {
            justify-content: flex-start !important;
            padding: 12px 16px !important;
            font-size: 14px !important;
        }
        body.nav-text #sidebar .sidebar-link i {
            margin-right: 10px !important;
            font-size: 14px !important;
            width: 20px !important;
            text-align: left !important;
        }
        body.nav-text #leadsMenuIcon,
        body.nav-text #projectsMenuIcon {
            display: inline-block !important;
        }
        body.nav-icons #sidebar {
            width: 64px !important;
            min-width: 64px !important;
            max-width: 64px !important;
        }
        body.sidebar-hidden #mainContent {
            margin-left: 0 !important;
        }
        .sidebar-link:hover {
            background: #F7F6F3 !important;
            color: var(--primary-color) !important;
        }
        .sidebar-link.active {
            background: #F7F6F3 !important;
            color: var(--primary-color) !important;
            font-weight: 500 !important;
        }
        .sidebar-toggle {
            position: fixed;
            top: 26px;
            left: calc(var(--nav-width) - 16px);
            z-index: 55;
            background: #ffffff;
            color: var(--primary-color);
            border: 1px solid rgba(6, 58, 28, 0.12);
            border-radius: 999px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
            transition: all 0.3s;
        }
        .sidebar-toggle:hover {
            background: #f7faf8;
            transform: translateY(-1px);
        }
        
        /* Custom CSS from branding settings */
        {!! branded_css() !!}
        aside.sidebar-hidden {
            transform: translateX(-100%);
        }
        aside.sidebar-hidden ~ div {
            margin-left: 0 !important;
        }
        .sidebar-toggle-icon {
            font-size: 12px;
            transition: transform 0.3s;
        }
        body.sidebar-hidden #sidebar {
            transform: translateX(calc(-1 * var(--nav-width)));
            pointer-events: none;
        }
        body.sidebar-hidden .sidebar-toggle {
            position: fixed;
            top: 28px;
            left: 12px;
            right: auto;
        }
        .layout-admin #sidebarToggle,
        .layout-crm #sidebarToggle {
            display: inline-flex;
        }
        @media (max-width: 768px) {
            .container { margin-left: 0; padding: 10px; }
            aside.sidebar-hidden ~ div {
                margin-left: 0 !important;
            }
            .sidebar-toggle {
                display: none !important;
            }
        }
        
        /* Mobile: Hide Sidebar, Show Bottom Nav */
        @media (max-width: 767px) {
            #sidebar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                position: absolute !important;
                left: -9999px !important;
                visibility: hidden !important;
            }
            #sidebarToggle {
                display: none !important;
            }
            #navModeToggle {
                display: none !important;
            }
            #mainContent {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100vw !important;
                min-width: 0 !important;
                overflow-x: hidden !important;
                padding-bottom: 70px !important;
            }
            #mainContent .container {
                max-width: 100% !important;
                width: 100% !important;
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
            #mobileFooterNav {
                display: flex !important;
            }
            /* ADMIN mobile: compact header */
            .layout-admin .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 12px !important;
            }
            .layout-admin .header > div:first-child h1 {
                font-size: 20px !important;
            }
            .layout-admin .header > div:first-child p {
                font-size: 12px !important;
            }
            .layout-admin .header > div:last-child {
                width: 100%;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-start;
            }
            /* Hide clock, username, logout, navToggle in admin header on mobile */
            .layout-admin .header #datetimeClock,
            .layout-admin .header #navModeToggle,
            .layout-admin .header .header-logout-form {
                display: none !important;
            }
            .layout-admin .header span[style*="color: #B3B5B4"] {
                display: none !important;
            }
            /* Make action buttons smaller on mobile */
            .layout-admin .header .btn {
                padding: 7px 12px !important;
                font-size: 13px !important;
            }

            /* CRM: hide header Logout on mobile (same as telecaller; use Profile/Logout in footer) */
            .layout-crm .header .header-logout-form {
                display: none !important;
            }
            /* CRM phone: compact header – hide title and label, only clock + date range */
            .layout-crm .header {
                flex-wrap: wrap;
                align-items: center;
                padding: 10px 12px !important;
                min-height: 50px;
            }
            .layout-crm .header > div:first-child {
                padding: 0;
                margin: 0;
                margin-top: 0 !important;
                flex: 1 1 auto;
                min-width: 0;
            }
            .layout-crm .header h1 {
                display: none !important;
            }
            .layout-crm .header .form-label[for="date-range-filter"],
            .layout-crm .header label[for="date-range-filter"] {
                display: none !important;
            }
            .layout-crm .header [style*="margin-top: 8px"] {
                margin-top: 0 !important;
            }
            .layout-crm .header #date-range-filter {
                max-width: 120px;
                padding: 4px 8px;
                font-size: 13px;
                height: 36px;
            }
            .layout-crm .header #datetimeClock {
                padding: 6px 10px;
                min-width: 120px;
            }
            .layout-crm .header #datetimeClock #clockTime {
                font-size: 14px;
            }
            .layout-crm .header #datetimeClock #clockDate {
                font-size: 10px;
            }
        }
        
        /* Mobile Bottom Navigation Bar */
        #mobileFooterNav {
            display: none;
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
        /* Admin mobile nav: 5 visible (20% each), 4 on scroll; smooth swipe, hidden scrollbar, scroll hint */
        #mobileFooterNav.admin-mobile-nav {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            justify-content: flex-start;
        }
        #mobileFooterNav.admin-mobile-nav::-webkit-scrollbar {
            display: none;
        }
        #mobileFooterNav.admin-mobile-nav .footer-nav-link {
            flex: 0 0 20%;
            min-width: 20%;
            max-width: 20%;
            scroll-snap-align: start;
        }
        #mobileFooterNav.admin-mobile-nav {
            scroll-snap-type: x mandatory;
        }
        /* CRM mobile nav: same scroll UX as admin (5 visible at 20%, rest on scroll) */
        #mobileFooterNav.crm-mobile-nav {
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            justify-content: flex-start;
            scroll-snap-type: x mandatory;
        }
        #mobileFooterNav.crm-mobile-nav::-webkit-scrollbar {
            display: none;
        }
        #mobileFooterNav.crm-mobile-nav .footer-nav-link {
            flex: 0 0 20%;
            min-width: 20%;
            max-width: 20%;
            scroll-snap-align: start;
        }
        /* Scroll hint: gradient overlay on right edge so user knows more items on scroll */
        .admin-mobile-nav-scroll-hint {
            position: fixed;
            bottom: 0;
            right: 0;
            width: 32px;
            height: 60px;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.95) 70%);
            pointer-events: none;
            z-index: 1001;
        }
        @media (min-width: 768px) {
            .admin-mobile-nav-scroll-hint { display: none !important; }
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
            max-width: 72px;
            min-height: 44px;
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
            color: var(--text-color);
        }
        .footer-nav-link.active span {
            color: var(--text-color);
        }
        @media (min-width: 768px) {
            #mobileFooterNav {
                display: none !important;
            }
        }

        /* Sidebar Tooltip Styles */
        .sidebar-tooltip {
            position: fixed;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
            font-size: 14px;
            color: #333;
            white-space: nowrap;
            font-weight: 500;
        }
        
        .sidebar-tooltip.show {
            opacity: 1;
        }
        
        .tooltip-arrow {
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-right: 6px solid white;
        }

        :root {
            --nav-shell: #0A1F18;
            --nav-shell-border: rgba(255,255,255,.08);
            --nav-text: rgba(255,255,255,.72);
            --nav-text-muted: rgba(255,255,255,.35);
            --nav-pill: rgba(255,255,255,.06);
            --nav-active: rgba(11,107,79,.32);
            --nav-active-text: #ffffff;
            --nav-accent: #5DCAA5;
            --nav-width: 248px;
        }

        html.pre-nav-text #sidebar,
        body #sidebar {
            width: var(--nav-width) !important;
            min-width: var(--nav-width) !important;
            max-width: var(--nav-width) !important;
            background: var(--nav-shell) !important;
            border-right: 1px solid var(--nav-shell-border) !important;
            box-shadow: none !important;
            color: #fff;
            overflow: hidden !important;
        }

        html.pre-nav-text #mainContent,
        body #mainContent {
            margin-left: var(--nav-width) !important;
        }

        body.nav-icons #sidebar,
        body.nav-text #sidebar,
        #sidebar.sidebar-icons,
        #sidebar.sidebar-text {
            width: var(--nav-width) !important;
            min-width: var(--nav-width) !important;
            max-width: var(--nav-width) !important;
        }

        body.nav-icons #mainContent,
        body.nav-text #mainContent {
            margin-left: var(--nav-width) !important;
        }

        body.sidebar-hidden #mainContent,
        .layout-admin.sidebar-hidden #mainContent {
            margin-left: 0 !important;
        }

        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .app-sidebar {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 45;
        }

        .sidebar-brand {
            position: relative;
            padding: 20px 18px 14px;
            border-bottom: 1px solid var(--nav-shell-border);
        }

        .layout-admin .sidebar-brand {
            padding-right: 54px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Fraunces', 'Poppins', serif;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .sidebar-logo-mark {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gradient-start), #5DCAA5);
            color: #0A1F18;
            font-size: 13px;
            font-weight: 800;
        }

        .sidebar-role {
            margin-top: 6px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--nav-text-muted);
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 10px 8px 14px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .nav-section-label {
            padding: 12px 10px 6px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .11em;
            text-transform: uppercase;
            color: rgba(255,255,255,.24);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px !important;
            margin-bottom: 2px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--nav-text) !important;
            font-size: 12px !important;
            font-weight: 500;
            background: transparent;
            transition: all .18s ease;
            min-height: 40px;
        }

        .sidebar-link i {
            width: 16px !important;
            margin-right: 0 !important;
            text-align: center;
            color: rgba(255,255,255,.48);
            font-size: 14px !important;
        }

        .sidebar-link:hover {
            background: var(--nav-pill) !important;
            color: rgba(255,255,255,.92) !important;
        }

        .sidebar-link:hover i,
        .sidebar-link.active i,
        .sidebar-link.sidebar-parent-open i:first-child {
            color: var(--nav-accent);
        }

        .sidebar-link.active,
        .sidebar-link.sidebar-parent-open {
            background: var(--nav-active) !important;
            color: var(--nav-active-text) !important;
            font-weight: 600 !important;
        }

        .sidebar-submenu {
            margin: 2px 0 8px;
            padding-left: 10px !important;
        }

        .sidebar-submenu .sidebar-link {
            padding: 8px 12px !important;
            min-height: 36px;
            font-size: 11.5px !important;
            color: rgba(255,255,255,.62) !important;
        }

        .sidebar-submenu .sidebar-link i {
            font-size: 12px !important;
        }

        .sidebar-link .menu-chevron {
            margin-left: auto;
            color: rgba(255,255,255,.36);
            font-size: 11px !important;
            transition: transform .18s ease;
        }

        .sidebar-link .menu-badge {
            margin-left: auto;
            padding: 2px 7px;
            border-radius: 999px;
            background: var(--gradient-start);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
        }

        .sidebar-footer {
            padding: 14px 16px 16px;
            border-top: 1px solid var(--nav-shell-border);
            background: rgba(255,255,255,.02);
        }

        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .sidebar-user-name {
            font-size: 12px;
            font-weight: 600;
            color: #fff;
        }

        .sidebar-user-role {
            margin-top: 2px;
            font-size: 10px;
            color: var(--nav-text-muted);
        }

        .main-header {
            background: rgba(255,255,255,.82);
            backdrop-filter: blur(14px);
            border: 1px solid #E2E1DC;
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            padding: 18px 20px;
        }

        .mobile-nav-toggle {
            display: none;
            width: 38px;
            height: 38px;
            border: 1px solid #E2E1DC;
            border-radius: 12px;
            background: #fff;
            color: #16161A;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .mobile-nav-toggle i {
            font-size: 15px;
        }

        #sidebarOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.42);
            z-index: 40;
        }

        #mobileFooterNav {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(14px);
            border-top: 1px solid #E2E1DC;
            box-shadow: 0 -6px 20px rgba(0,0,0,.08);
            padding: 8px 6px;
            height: 66px;
        }

        .footer-nav-link {
            border-radius: 12px;
            color: #6A6A63;
            max-width: none;
        }

        .footer-nav-link i {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .footer-nav-link span {
            font-size: 9px;
            font-weight: 600;
            color: inherit;
        }

        .footer-nav-link:hover,
        .footer-nav-link.active {
            background: #F0EFEC;
            color: #0B6B4F;
        }

        @media (max-width: 767px) {
            html.pre-nav-text #mainContent,
            body #mainContent,
            body.nav-icons #mainContent,
            body.nav-text #mainContent {
                margin-left: 0 !important;
            }

            body.sidebar-hidden #sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-hidden #mainContent {
                margin-left: 0 !important;
            }

            #sidebar {
                display: flex !important;
                left: 0 !important;
                visibility: visible !important;
                width: min(86vw, 308px) !important;
                min-width: min(86vw, 308px) !important;
                max-width: min(86vw, 308px) !important;
                transform: translateX(-100%);
                transition: transform .25s ease;
            }

            body.mobile-sidebar-open #sidebar {
                transform: translateX(0);
            }

            #sidebarOverlay {
                display: block;
                opacity: 0;
                pointer-events: none;
                transition: opacity .2s ease;
            }

            body.mobile-sidebar-open #sidebarOverlay {
                opacity: 1;
                pointer-events: auto;
            }

            .mobile-nav-toggle {
                display: inline-flex;
            }

            .main-header {
                padding: 14px 16px;
                border-radius: 16px;
            }

            .layout-admin .header,
            .layout-crm .header {
                gap: 10px;
                align-items: flex-start;
            }

            .layout-admin .header > div:last-child,
            .layout-crm .header > div:last-child {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }
        
    </style>
    
    <style>
        .layout-crm .page-shell {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .layout-crm .crm-hero {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            border: 1px solid rgba(6, 58, 28, 0.1);
            background:
                radial-gradient(circle at top left, rgba(191, 230, 216, 0.9), transparent 38%),
                radial-gradient(circle at top right, rgba(237, 245, 225, 0.95), transparent 42%),
                linear-gradient(135deg, #ffffff 0%, #f7fbf8 60%, #eef7f2 100%);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
            padding: 28px;
        }
        .layout-crm .crm-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.9fr);
            gap: 24px;
            align-items: end;
        }
        .layout-crm .crm-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(16, 122, 84, 0.11);
            color: #0f7a54;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .layout-crm .crm-hero-title {
            margin-top: 18px;
            color: #14213d;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 1.03;
            letter-spacing: -0.04em;
        }
        .layout-crm .crm-hero-title strong {
            color: #0f7a54;
        }
        .layout-crm .crm-hero-copy {
            margin-top: 16px;
            max-width: 760px;
            color: #5f6c7b;
            font-size: 16px;
            line-height: 1.8;
        }
        .layout-crm .crm-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .layout-crm .crm-mini-card {
            border-radius: 24px;
            border: 1px solid rgba(6, 58, 28, 0.1);
            background: rgba(255, 255, 255, 0.88);
            padding: 20px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }
        .layout-crm .crm-mini-label {
            color: #7f8b96;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .layout-crm .crm-mini-value {
            margin-top: 10px;
            color: #0f7a54;
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 800;
            line-height: 1;
        }
        .layout-crm .crm-mini-copy {
            margin-top: 8px;
            color: #5f6c7b;
            font-size: 13px;
            line-height: 1.5;
        }
        .layout-crm .crm-surface {
            border-radius: 28px;
            border: 1px solid rgba(6, 58, 28, 0.1);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
            padding: 24px;
        }
        .layout-crm .crm-surface + .crm-surface {
            margin-top: 24px;
        }
        .layout-crm .crm-surface-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .layout-crm .crm-section-title {
            color: #14213d;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .layout-crm .crm-section-copy {
            margin-top: 6px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }
        .layout-crm .crm-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eff5f1;
            color: #4b5563;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .layout-crm .crm-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }
        .layout-crm .crm-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }
        .layout-crm .crm-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }
        .layout-crm .crm-stat-card {
            border-radius: 24px;
            border: 1px solid rgba(6, 58, 28, 0.1);
            background: linear-gradient(180deg, rgba(255,255,255,1), rgba(246,249,247,1));
            padding: 20px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        }
        .layout-crm .crm-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .layout-crm .crm-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: #fff;
            box-shadow: 0 10px 18px rgba(6, 58, 28, 0.18);
        }
        .layout-crm .crm-stat-value {
            margin-top: 18px;
            color: #14213d;
            font-size: clamp(1.8rem, 2.8vw, 2.45rem);
            font-weight: 800;
            line-height: 1;
        }
        .layout-crm .crm-stat-label {
            margin-top: 8px;
            color: #7f8b96;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .layout-crm .crm-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .layout-crm .crm-control,
        .layout-crm .crm-control-group input,
        .layout-crm .crm-control-group select,
        .layout-crm .crm-control-group textarea,
        .layout-crm .crm-form-control {
            min-height: 46px;
            border-radius: 16px;
            border: 1px solid #d7e0d9;
            background: #fff;
            color: #18202c;
            padding: 0 15px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        .layout-crm .crm-control:focus,
        .layout-crm .crm-control-group input:focus,
        .layout-crm .crm-control-group select:focus,
        .layout-crm .crm-control-group textarea:focus,
        .layout-crm .crm-form-control:focus {
            outline: none;
            border-color: rgba(15, 122, 84, 0.45);
            box-shadow: 0 0 0 4px rgba(15, 122, 84, 0.12);
        }
        .layout-crm .crm-control-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .layout-crm .crm-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .layout-crm .crm-field label {
            display: block;
            margin-bottom: 8px;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }
        .layout-crm .crm-table-shell {
            border-radius: 24px;
            border: 1px solid rgba(6, 58, 28, 0.09);
            overflow: hidden;
            background: #fbfdfb;
        }
        .layout-crm .crm-table-shell table {
            width: 100%;
            border-collapse: collapse;
        }
        .layout-crm .crm-table-shell th {
            background: #f3f7f4;
            color: #667085;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .layout-crm .crm-table-shell th,
        .layout-crm .crm-table-shell td {
            padding: 14px 16px;
            border-bottom: 1px solid #ebf1ed;
            vertical-align: top;
        }
        .layout-crm .crm-table-shell tr:last-child td {
            border-bottom: none;
        }
        .layout-crm .crm-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 180px;
            color: #8b95a1;
            text-align: center;
        }
        .layout-crm .crm-empty i {
            font-size: 28px;
            color: #b7c0cb;
        }
        .layout-crm .crm-note {
            border-radius: 22px;
            border: 1px solid rgba(25, 118, 210, 0.12);
            background: linear-gradient(135deg, #eef7ff, #f8fbff);
            padding: 18px 20px;
            color: #365475;
        }
        .layout-crm .crm-note-warning {
            border-color: rgba(245, 158, 11, 0.18);
            background: linear-gradient(135deg, #fff7e6, #fffaf0);
            color: #8a5b00;
        }
        .layout-crm .crm-tabbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }
        .layout-crm .crm-tabbar .tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 16px;
            border: 1px solid #dce7df;
            background: #fff;
            color: #5e6977;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 0;
            transition: all 0.2s ease;
        }
        .layout-crm .crm-tabbar .tab.active {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 12px 24px rgba(6, 58, 28, 0.18);
        }
        .layout-crm .crm-badge-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            font-size: 11px;
            font-weight: 800;
        }
        .layout-crm .crm-list-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }
        .layout-crm .crm-card {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 100%;
            border-radius: 24px;
            border: 1px solid rgba(6, 58, 28, 0.1);
            background: linear-gradient(180deg, #ffffff, #f8fbf9);
            padding: 20px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }
        .layout-crm .crm-card h3 {
            color: #17324d;
            font-size: 18px;
            font-weight: 800;
        }
        .layout-crm .crm-card-copy {
            color: #677483;
            font-size: 14px;
            line-height: 1.7;
        }
        .layout-crm .crm-card-divider {
            height: 1px;
            background: #e8efea;
        }
        .layout-crm .crm-inline-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        @media (max-width: 1280px) {
            .layout-crm .crm-hero-grid,
            .layout-crm .crm-grid-4,
            .layout-crm .crm-list-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 960px) {
            .layout-crm .crm-grid-2,
            .layout-crm .crm-grid-3,
            .layout-crm .crm-grid-4,
            .layout-crm .crm-mini-grid,
            .layout-crm .crm-list-grid,
            .layout-crm .crm-form-grid,
            .layout-crm .crm-hero-grid {
                grid-template-columns: 1fr;
            }
            .layout-crm .crm-surface,
            .layout-crm .crm-hero {
                padding: 20px;
                border-radius: 24px;
            }
            .layout-crm .crm-surface-header {
                flex-direction: column;
            }
        }
    </style>
    @stack('styles')
    {{-- Apply nav mode BEFORE body renders to prevent any flash --}}
    <script>
    (function(){
        try {
            document.documentElement.classList.add('pre-nav-text');
        } catch(e) {}
    })();
    </script>
</head>
<body class="bg-[#F7F6F3] font-sans antialiased @if(auth()->user()->isCrm()) layout-crm @elseif(auth()->user()->isAdmin()) layout-admin @endif" style="margin: 0; padding: 0; overflow: hidden;">
@if(session('impersonating_original_id'))
<div style="position:fixed;top:0;left:0;right:0;z-index:99999;background:linear-gradient(135deg,#92400e,#b45309);color:#fff;padding:10px 24px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:16px;box-shadow:0 3px 12px rgba(0,0,0,0.3);">
    <i class="fas fa-user-secret"></i>
    <span>⚠️ Admin mode: Aap <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->getDisplayRoleName() }}) ke roop mein dekh rahe hain</span>
    <a href="/impersonate/stop" style="background:#fff;color:#b45309;border:none;border-radius:8px;padding:6px 16px;font-weight:700;cursor:pointer;font-size:12px;display:flex;align-items:center;gap:6px;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Wapas Admin
    </a>
</div>
<div style="height:44px;"></div>
@endif
    <script>
    (function(){
        document.body.classList.add('no-transition');
        try {
            document.body.classList.add('nav-text');
        } catch(e) {
            document.body.classList.add('nav-text');
        }
    })();
    </script>
    <div id="sidebarOverlay" onclick="closeMobileSidebar()"></div>
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Hide navigation" title="Hide navigation">
        <i class="fas fa-chevron-left sidebar-toggle-icon"></i>
    </button>
    @endif

    <div class="app-shell">
        <!-- Sidebar -->
        <aside id="sidebar" class="app-sidebar fixed left-0 top-0 h-full bg-white border-r border-gray-200 shadow-sm z-30">
            <!-- Logo and Role -->
            <div class="sidebar-brand">
                <div class="sidebar-logo">
                    <span class="sidebar-logo-mark">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</span>
                    <span>Base CRM</span>
                </div>
                <div class="sidebar-role">
                    @if(auth()->user()->isAdmin())
                        Admin Dashboard
                    @elseif(auth()->user()->isCrm())
                        CRM Workspace
                    @elseif(auth()->user()->isSalesHead())
                        Sales Head
                    @elseif(auth()->user()->isSalesManager())
                        Senior Manager
                    @elseif(auth()->user()->isTelecaller())
                        Sales Executive
                    @else
                        {{ auth()->user()->getDisplayRoleName() ?? 'User' }}
                    @endif
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="sidebar-scroll">
            <nav>
                @if(auth()->user()->isAdmin())
                <div class="nav-section-label">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard" title="Dashboard">
                    <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                    Dashboard
                </a>
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" data-tooltip="All Users" title="All Users">
                    <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                    All Users
                </a>
                <a href="{{ auth()->user()->isCrm() ? route('crm.targets.index') : route('admin.targets.index') }}" class="sidebar-link {{ request()->routeIs('admin.targets.*') || request()->routeIs('crm.targets.*') ? 'active' : '' }}" data-tooltip="Target Setting" title="Target Setting">
                    <i class="fas fa-bullseye" style="margin-right: 10px; width: 20px;"></i>
                    Target Setting
                </a>
                <div class="nav-section-label">Pipeline</div>
                <div class="sidebar-link {{ request()->routeIs('leads.*') || request()->routeIs('admin.other-leads.*') || request()->routeIs('admin.dead-leads') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleLeadsMenu()" data-tooltip="Leads" title="Leads">
                    <i class="fas fa-user-friends" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                    <i class="fas fa-chevron-down ml-auto" id="leadsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="leadsSubMenu" class="pl-8" style="display: {{ request()->routeIs('leads.*') || request()->routeIs('admin.other-leads.*') || request()->routeIs('admin.dead-leads') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'block' : 'none' }};">
                    <a href="{{ route('leads.index') }}" class="sidebar-link {{ request()->routeIs('leads.*') && !request()->routeIs('prospects.*') && !request()->routeIs('meetings.*') && !request()->routeIs('site-visits.*') && !request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="All Leads" title="All Leads">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Leads
                    </a>
                    <a href="{{ route('prospects.index') }}" class="sidebar-link {{ request()->routeIs('prospects.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Prospects" title="Prospects">
                        <i class="fas fa-user-check" style="margin-right: 10px; width: 20px;"></i>
                        Prospects
                    </a>
                    <a href="{{ route('meetings.index') }}" class="sidebar-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Meetings" title="Meetings">
                        <i class="fas fa-handshake" style="margin-right: 10px; width: 20px;"></i>
                        Meetings
                    </a>
                    <a href="{{ route('site-visits.index') }}" class="sidebar-link {{ request()->routeIs('site-visits.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Site Visits" title="Site Visits">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; width: 20px;"></i>
                        Visits
                    </a>
                    <a href="{{ route('closers.index') }}" class="sidebar-link {{ request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Closers" title="Closers">
                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                        Closers
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.other-leads.index') }}" class="sidebar-link {{ request()->routeIs('admin.other-leads.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Other Leads" title="Other Leads">
                        <i class="fas fa-box-open" style="margin-right: 10px; width: 20px;"></i>
                        Other Leads
                    </a>
                    @elseif(auth()->user()->isCrm())
                    <a href="{{ route('admin.dead-leads') }}" class="sidebar-link {{ request()->routeIs('admin.dead-leads') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Other Leads" title="Other Leads">
                        <i class="fas fa-box-open" style="margin-right: 10px; width: 20px;"></i>
                        Other Leads
                    </a>
                    @endif
                </div>
                <div class="sidebar-link {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleProjectsMenu()" data-tooltip="Projects" title="Projects">
                    <i class="fas fa-project-diagram" style="margin-right: 10px; width: 20px;"></i>
                    Projects
                    <i class="fas fa-chevron-down ml-auto" id="projectsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="projectsSubMenu" class="pl-8" style="display: {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'block' : 'none' }};">
                    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->routeIs('projects.*') && !request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="All Projects" title="All Projects">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Projects
                    </a>
                    <a href="{{ route('builders.index') }}" class="sidebar-link {{ request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;" data-tooltip="Builders" title="Builders">
                        <i class="fas fa-building" style="margin-right: 10px; width: 20px;"></i>
                        Builders
                    </a>
                </div>
                <div class="nav-section-label">Operations</div>
                <a href="{{ route('calls.index') }}" class="sidebar-link {{ request()->routeIs('calls.*') ? 'active' : '' }}" data-tooltip="All Calls" title="All Calls">
                    <i class="fas fa-phone" style="margin-right: 10px; width: 20px;"></i>
                    All Calls
                </a>
                <a href="{{ route('chat.index') }}" class="sidebar-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" data-tooltip="WhatsApp Chat" title="WhatsApp Chat">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; width: 20px;"></i>
                    WhatsApp Chat
                </a>
                {{-- Reports section hidden --}}
                {{-- <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') && request()->get('tab') == 'reports' ? 'active' : '' }}">
                    <i class="fas fa-chart-bar" style="margin-right: 10px; width: 20px;"></i>
                    Reports
                </a> --}}
                <a href="{{ route('export.index') }}" class="sidebar-link {{ request()->routeIs('export.*') ? 'active' : '' }}" data-tooltip="Export" title="Export">
                    <i class="fas fa-download" style="margin-right: 10px; width: 20px;"></i>
                    Export
                </a>
                <a href="{{ route('admin.lead-download-requests.index') }}" class="sidebar-link {{ request()->routeIs('admin.lead-download-requests.*') ? 'active' : '' }}" data-tooltip="Lead Downloads" title="Lead Downloads">
                    <i class="fas fa-file-arrow-down" style="margin-right: 10px; width: 20px;"></i>
                    Lead Downloads
                </a>
                <a href="{{ route('admin.forms.index') }}" class="sidebar-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}" data-tooltip="Forms" title="Forms">
                    <i class="fas fa-wpforms" style="margin-right: 10px; width: 20px;"></i>
                    Forms
                </a>
                <a href="{{ route('lead-assignment.index') }}" class="sidebar-link {{ request()->routeIs('lead-assignment.*') && !request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}" data-tooltip="Lead Assignment" title="Lead Assignment">
                    <i class="fas fa-users-cog" style="margin-right: 10px; width: 20px;"></i>
                    Lead Assignment
                </a>
                <a href="{{ route('lead-assignment.calling-tasks.index') }}" class="sidebar-link {{ request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}" data-tooltip="Calling Tasks" title="Calling Tasks">
                    <i class="fas fa-phone-volume" style="margin-right: 10px; width: 20px;"></i>
                    Calling Tasks
                </a>
                <a href="{{ route('lead-import.index') }}" class="sidebar-link {{ request()->routeIs('lead-import.*') ? 'active' : '' }}" data-tooltip="Lead Import" title="Lead Import">
                    <i class="fas fa-cloud-upload-alt" style="margin-right: 10px; width: 20px;"></i>
                    Lead Import
                </a>
                <a href="{{ route('admin.automation.index') }}" class="sidebar-link {{ request()->routeIs('admin.automation.*') ? 'active' : '' }}" data-tooltip="Automation" title="Automation">
                    <i class="fas fa-magic" style="margin-right: 10px; width: 20px;"></i>
                    Automation
                </a>
                <div class="nav-section-label">System</div>
                <a href="{{ route('admin.support.index') }}" class="sidebar-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" data-tooltip="Support" title="Support" style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="display:flex;align-items:center;">
                        <i class="fas fa-headset" style="margin-right: 10px; width: 20px;"></i>
                        <span class="nav-text">Support</span>
                    </span>
                    @php $openTicketCount = \App\Models\SupportTicket::where('status','open')->count(); @endphp
                    @if($openTicketCount > 0)
                    <span style="background:#ef4444;color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;font-weight:700;margin-left:auto;">{{ $openTicketCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.company-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.company-settings.*') ? 'active' : '' }}" data-tooltip="Company Settings" title="Company Settings">
                    <i class="fas fa-cog" style="margin-right: 10px; width: 20px;"></i>
                    Company Settings
                </a>
                <a href="{{ route('admin.system-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.system-settings.*') ? 'active' : '' }}" data-tooltip="System Settings" title="System Settings">
                    <i class="fas fa-server" style="margin-right: 10px; width: 20px;"></i>
                    System Settings
                </a>
                <a href="{{ route('admin.deploy.index') }}" class="sidebar-link {{ request()->routeIs('admin.deploy.*') ? 'active' : '' }}" data-tooltip="Deployment" title="Deployment">
                    <i class="fas fa-rocket" style="margin-right: 10px; width: 20px;"></i>
                    Deployment
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('integrations.index') }}" class="sidebar-link {{ request()->routeIs('integrations.*') ? 'active' : '' }}" data-tooltip="Integration" title="Integration">
                    <i class="fas fa-plug" style="margin-right: 10px; width: 20px;"></i>
                    Integration
                </a>
                @endif
                <a href="{{ route('admin.profile') }}" class="sidebar-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}" data-tooltip="Profile" title="Profile">
                    <i class="fas fa-user" style="margin-right: 10px; width: 20px;"></i>
                    Profile
                </a>
                @else
                <div class="nav-section-label">Main</div>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home" style="margin-right: 10px; width: 20px;"></i>
                    Dashboard
                </a>
                @if(!auth()->user()->isTelecaller())
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users" style="margin-right: 10px; width: 20px;"></i>
                    Users
                </a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                <a href="{{ auth()->user()->isCrm() ? route('crm.targets.index') : route('admin.targets.index') }}" class="sidebar-link {{ request()->routeIs('admin.targets.*') || request()->routeIs('crm.targets.*') ? 'active' : '' }}">
                    <i class="fas fa-bullseye" style="margin-right: 10px; width: 20px;"></i>
                    Target Setting
                </a>
                @endif
                <div class="nav-section-label">Pipeline</div>
                @if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
                <div class="sidebar-link {{ request()->routeIs('leads.*') || request()->routeIs('admin.other-leads.*') || request()->routeIs('admin.dead-leads') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleLeadsMenu()">
                    <i class="fas fa-filter" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                    <i class="fas fa-chevron-down ml-auto" id="leadsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="leadsSubMenu" class="pl-8" style="display: {{ request()->routeIs('leads.*') || request()->routeIs('admin.other-leads.*') || request()->routeIs('admin.dead-leads') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'block' : 'none' }};">
                    <a href="{{ route('leads.index') }}" class="sidebar-link {{ request()->routeIs('leads.*') && !request()->routeIs('prospects.*') && !request()->routeIs('meetings.*') && !request()->routeIs('site-visits.*') && !request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Leads
                    </a>
                    <a href="{{ route('prospects.index') }}" class="sidebar-link {{ request()->routeIs('prospects.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-user-check" style="margin-right: 10px; width: 20px;"></i>
                        Prospects
                    </a>
                    <a href="{{ route('meetings.index') }}" class="sidebar-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-handshake" style="margin-right: 10px; width: 20px;"></i>
                        Meetings
                    </a>
                    <a href="{{ route('site-visits.index') }}" class="sidebar-link {{ request()->routeIs('site-visits.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; width: 20px;"></i>
                        Visits
                    </a>
                    <a href="{{ route('closers.index') }}" class="sidebar-link {{ request()->routeIs('closers.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                        Closers
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.other-leads.index') }}" class="sidebar-link {{ request()->routeIs('admin.other-leads.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-box-open" style="margin-right: 10px; width: 20px;"></i>
                        Other Leads
                    </a>
                    @elseif(auth()->user()->isCrm())
                    <a href="{{ route('admin.dead-leads') }}" class="sidebar-link {{ request()->routeIs('admin.dead-leads') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-box-open" style="margin-right: 10px; width: 20px;"></i>
                        Other Leads
                    </a>
                    @endif
                </div>
                @else
                <a href="{{ route('leads.index') }}" class="sidebar-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="fas fa-filter" style="margin-right: 10px; width: 20px;"></i>
                    Leads
                </a>
                @endif
                <div class="sidebar-link {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : '' }}" style="cursor: pointer;" onclick="toggleProjectsMenu()">
                    <i class="fas fa-project-diagram" style="margin-right: 10px; width: 20px;"></i>
                    Projects
                    <i class="fas fa-chevron-down ml-auto" id="projectsMenuIcon" style="transition: transform 0.3s;"></i>
                </div>
                <div id="projectsSubMenu" class="pl-8" style="display: {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'block' : 'none' }};">
                    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->routeIs('projects.*') && !request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-list" style="margin-right: 10px; width: 20px;"></i>
                        All Projects
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                    <a href="{{ route('builders.index') }}" class="sidebar-link {{ request()->routeIs('builders.*') ? 'active' : '' }}" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-building" style="margin-right: 10px; width: 20px;"></i>
                        Builders
                    </a>
                    @endif
                </div>
                <div class="nav-section-label">Operations</div>
                <a href="{{ route('calls.index') }}" class="sidebar-link {{ request()->routeIs('calls.*') ? 'active' : '' }}">
                    <i class="fas fa-phone" style="margin-right: 10px; width: 20px;"></i>
                    @if(auth()->user()->isTelecaller() || auth()->user()->isSalesExecutive())
                        My Calls
                    @elseif(auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
                        Team Calls
                    @else
                        All Calls
                    @endif
                </a>
                <a href="{{ route('chat.index') }}" class="sidebar-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; width: 20px;"></i>
                    WhatsApp Chat
                </a>
                @if(!auth()->user()->isAdmin() && !auth()->user()->isTelecaller() && !auth()->user()->isSalesHead() && !auth()->user()->isCrm())
                <a href="{{ route('lead-assignment.index') }}" class="sidebar-link {{ request()->routeIs('lead-assignment.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard" style="margin-right: 10px; width: 20px;"></i>
                    Lead Assignment
                </a>
                @endif
                @if(auth()->user()->canManageUsers() && !auth()->user()->isSalesHead())
                <a href="{{ route('lead-import.index') }}" class="sidebar-link {{ request()->routeIs('lead-import.*') ? 'active' : '' }}">
                    <i class="fas fa-cloud-upload-alt" style="margin-right: 10px; width: 20px;"></i>
                    Lead Import
                </a>
                @endif
                @if(auth()->user()->isCrm() || auth()->user()->isAdmin() || auth()->user()->isSalesHead())
                <a href="{{ route('crm.verifications') }}" class="sidebar-link {{ request()->routeIs('crm.verifications') ? 'active' : '' }}">
                    <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px;"></i>
                    Verifications
                </a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
                <a href="{{ route('export.index') }}" class="sidebar-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                    <i class="fas fa-download" style="margin-right: 10px; width: 20px;"></i>
                    Export
                </a>
                @endif
                <div class="nav-section-label">System</div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('integrations.index') }}"
                   class="sidebar-link {{ request()->routeIs('integrations.*') ? 'active' : '' }}"
                   data-tooltip="Integration"
                   title="Integration">
                    <i class="fas fa-plug" style="margin-right: 10px; width: 20px;"></i>
                    Integration
                </a>
                <a href="{{ route('integrations.sheet-integration') }}"
                   class="sidebar-link {{ request()->routeIs('integrations.sheet-integration') || request()->routeIs('integrations.sheet-sync') || request()->routeIs('lead-import.*') ? 'active' : '' }}"
                   data-tooltip="Sheet Integration"
                   title="Sheet Integration">
                    <i class="fas fa-table" style="margin-right: 10px; width: 20px;"></i>
                    Sheet Integration
                </a>
                <a href="{{ route('integrations.meta-sheet.index') }}"
                   class="sidebar-link {{ request()->routeIs('integrations.meta-sheet.*') ? 'active' : '' }}"
                   data-tooltip="Meta Sheets"
                   title="Meta Sheets">
                    <i class="fab fa-facebook" style="margin-right: 10px; width: 20px;"></i>
                    Meta Sheets
                </a>
                @endif
                @if(auth()->user()->isCrm())
                <a href="{{ route('lead-assignment.index') }}"
                   class="sidebar-link {{ request()->routeIs('lead-assignment.*') && !request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}"
                   data-tooltip="Lead Assignment"
                   title="Lead Assignment">
                    <i class="fas fa-clipboard" style="margin-right: 10px; width: 20px;"></i>
                    Lead Assignment
                </a>
                <a href="{{ route('lead-assignment.calling-tasks.index') }}"
                   class="sidebar-link {{ request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}"
                   data-tooltip="Calling Tasks"
                   title="Calling Tasks">
                    <i class="fas fa-phone-volume" style="margin-right: 10px; width: 20px;"></i>
                    Calling Tasks
                </a>
                <a href="{{ route('lead-assignment.lead-off-users') }}"
                   class="sidebar-link {{ request()->routeIs('lead-assignment.telecaller-status') || request()->routeIs('lead-assignment.lead-off-users') ? 'active' : '' }}"
                   data-tooltip="Lead Off Users"
                   title="Lead Off Users">
                    <i class="fas fa-user-slash" style="margin-right: 10px; width: 20px;"></i>
                    Lead Off Users
                </a>
                @endif
                @if(!auth()->user()->isAdmin() && !auth()->user()->isCrm() && auth()->user()->canManageUsers() && !auth()->user()->isSalesHead())
                <a href="{{ route('admin.dead-leads') }}" class="sidebar-link {{ request()->routeIs('admin.dead-leads') ? 'active' : '' }}">
                    <i class="fas fa-trash" style="margin-right: 10px; width: 20px;"></i>
                    Dead Leads / Trash
                </a>
                @endif
                @endif
                @if(!auth()->user()->isAdmin())
                <a href="{{ route('support.index') }}" class="sidebar-link {{ request()->routeIs('support.*') ? 'active' : '' }}" data-tooltip="Support" title="Support">
                    <i class="fas fa-life-ring" style="margin-right: 10px; width: 20px;"></i>
                    <span class="nav-text">Support</span>
                </a>
                @endif
            </nav>
            </div>
            <div class="sidebar-footer">
                <div class="sidebar-user-card">
                    <span class="sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                    <div>
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-role">{{ auth()->user()->getDisplayRoleName() ?? 'User' }}</div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Sidebar Tooltip -->
        <div id="sidebarTooltip" class="sidebar-tooltip">
            <span class="tooltip-text"></span>
            <span class="tooltip-arrow"></span>
        </div>
        
        <!-- Main Content -->
        <div id="mainContent" style="flex: 1; min-width: 0; overflow-y: auto; overflow-x: hidden; height: 100vh; background: #F7F6F3;">
            <div class="container" style="padding: 20px; max-width: 100%; width: 100%; min-width: 0; box-sizing: border-box;">
                <!-- Header -->
                <div class="header main-header">
                    <div>
                        <h1 style="font-size: 28px; font-weight: 700; color: #063A1C;">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                            <p style="color: #B3B5B4; font-size: 14px; margin-top: 4px;">@yield('page-subtitle')</p>
                        @endif
                        @hasSection('header-below-title')
                            <div style="margin-top: 8px;">@yield('header-below-title')</div>
                        @endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button type="button" class="mobile-nav-toggle" onclick="openMobileSidebar()" aria-label="Open navigation">
                            <i class="fas fa-bars"></i>
                        </button>
                        @hasSection('header-actions')
                            @yield('header-actions')
                        @endif
                        @if(!auth()->user()->isAdmin() && !auth()->user()->isCrm())
                        <button type="button" id="navModeToggle" class="btn btn-brand-secondary" title="Hide navigation" style="padding: 10px 12px; font-size: 14px;">
                            <i id="navModeToggleIcon" class="fas fa-eye-slash" style="margin-right: 6px;"></i>
                            <span id="navModeToggleLabel">Hide Nav</span>
                        </button>
                        @endif
                        <!-- Date/Time Clock (shown for all including CRM) -->
                        <div id="datetimeClock" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 8px 12px; font-family: 'Courier New', monospace; font-weight: 600; font-size: 14px; color: #063A1C; min-width: 160px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div id="clockTime" style="font-size: 16px; color: #205A44;">--:--:--</div>
                            <div id="clockDate" style="font-size: 11px; color: #B3B5B4; margin-top: 2px;">-- -- ----</div>
                        </div>
                        @if(!auth()->user()->isCrm())
                        <span style="color: #B3B5B4; font-size: 14px;">{{ auth()->user()->name }}</span>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="header-logout-form" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt" style="margin-right: 5px;"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Mobile Bottom Navigation (shown on small screens only). Admin/CRM: 5 visible (20% each), rest on scroll. -->
    <nav id="mobileFooterNav" @if(auth()->user()->isAdmin()) class="admin-mobile-nav" @elseif(auth()->user()->isCrm()) class="crm-mobile-nav" @endif>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="footer-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('users.index') }}" class="footer-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="{{ route('leads.index') }}" class="footer-nav-link {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}">
                <i class="fas fa-user-friends"></i>
                <span>Leads</span>
            </a>
            <a href="{{ route('admin.other-leads.index') }}" class="footer-nav-link {{ request()->routeIs('admin.other-leads.*') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i>
                <span>Other Leads</span>
            </a>
            <a href="{{ route('projects.index') }}" class="footer-nav-link {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : '' }}">
                <i class="fas fa-project-diagram"></i>
                <span>Projects</span>
            </a>
            <a href="{{ route('calls.index') }}" class="footer-nav-link {{ request()->routeIs('calls.*') ? 'active' : '' }}">
                <i class="fas fa-phone"></i>
                <span>Calls</span>
            </a>
            <a href="{{ route('chat.index') }}" class="footer-nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <i class="fab fa-whatsapp"></i>
                <span>Chat</span>
            </a>
            <a href="{{ route('export.index') }}" class="footer-nav-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </a>
            <a href="{{ route('lead-assignment.calling-tasks.index') }}" class="footer-nav-link {{ request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}">
                <i class="fas fa-phone-volume"></i>
                <span>Call Tasks</span>
            </a>
            <a href="{{ route('admin.company-settings.index') }}" class="footer-nav-link {{ request()->routeIs('admin.company-settings.*') || request()->routeIs('admin.system-settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="{{ route('admin.profile') }}" class="footer-nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        @elseif(auth()->user()->isCrm())
            <a href="{{ route('dashboard') }}" class="footer-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('leads.index') }}" class="footer-nav-link {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}">
                <i class="fas fa-user-friends"></i>
                <span>Leads</span>
            </a>
            <a href="{{ route('crm.verifications') }}" class="footer-nav-link {{ request()->routeIs('crm.verifications') ? 'active' : '' }}">
                <i class="fas fa-check-circle"></i>
                <span>Verifications</span>
            </a>
            <a href="{{ route('lead-import.index') }}" class="footer-nav-link {{ request()->routeIs('lead-import.*') ? 'active' : '' }}">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Lead Import</span>
            </a>
            <a href="{{ route('lead-assignment.index') }}" class="footer-nav-link {{ request()->routeIs('lead-assignment.*') && !request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard"></i>
                <span>Lead Assign</span>
            </a>
            <a href="{{ route('lead-assignment.calling-tasks.index') }}" class="footer-nav-link {{ request()->routeIs('lead-assignment.calling-tasks.*') ? 'active' : '' }}">
                <i class="fas fa-phone-volume"></i>
                <span>Call Tasks</span>
            </a>
            <a href="{{ route('lead-assignment.lead-off-users') }}" class="footer-nav-link {{ request()->routeIs('lead-assignment.telecaller-status') || request()->routeIs('lead-assignment.lead-off-users') ? 'active' : '' }}">
                <i class="fas fa-user-slash"></i>
                <span>Lead Off</span>
            </a>
            <a href="{{ route('export.index') }}" class="footer-nav-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </a>
            <a href="{{ route('users.index') }}" class="footer-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="{{ auth()->user()->isCrm() ? route('crm.targets.index') : route('admin.targets.index') }}" class="footer-nav-link {{ request()->routeIs('admin.targets.*') || request()->routeIs('crm.targets.*') ? 'active' : '' }}">
                <i class="fas fa-bullseye"></i>
                <span>Targets</span>
            </a>
            <a href="{{ route('projects.index') }}" class="footer-nav-link {{ request()->routeIs('projects.*') || request()->routeIs('builders.*') ? 'active' : '' }}">
                <i class="fas fa-project-diagram"></i>
                <span>Projects</span>
            </a>
            <a href="{{ route('calls.index') }}" class="footer-nav-link {{ request()->routeIs('calls.*') ? 'active' : '' }}">
                <i class="fas fa-phone"></i>
                <span>Calls</span>
            </a>
            <a href="{{ route('chat.index') }}" class="footer-nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <i class="fab fa-whatsapp"></i>
                <span>Chat</span>
            </a>
            <a href="{{ route('admin.dead-leads') }}" class="footer-nav-link {{ request()->routeIs('admin.dead-leads') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i>
                <span>Other Leads</span>
            </a>
            <a href="{{ route('dashboard') }}" class="footer-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="{{ route('logout.get') }}" class="footer-nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="footer-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            @if(!auth()->user()->isTelecaller())
            <a href="{{ route('users.index') }}" class="footer-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            @endif
            <a href="{{ route('leads.index') }}" class="footer-nav-link {{ request()->routeIs('leads.*') || request()->routeIs('prospects.*') || request()->routeIs('meetings.*') || request()->routeIs('site-visits.*') || request()->routeIs('closers.*') ? 'active' : '' }}">
                <i class="fas fa-user-friends"></i>
                <span>Leads</span>
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isCrm() || auth()->user()->isSalesManager() || auth()->user()->isSalesHead())
            <a href="{{ route('export.index') }}" class="footer-nav-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </a>
            @endif
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.profile') }}" class="footer-nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            @else
            <a href="{{ route('dashboard') }}" class="footer-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            @endif
        @endif
    </nav>
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    <div class="admin-mobile-nav-scroll-hint" aria-hidden="true"></div>
    @endif

    @stack('scripts')
    <script src="{{ asset('js/branding-update.js') }}"></script>
    <script>
        // Live Clock Functionality (header + CRM compact clock when present)
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeStr = `${hours}:${minutes}:${seconds}`;
            const dateStr = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            const timeElement = document.getElementById('clockTime');
            const dateElement = document.getElementById('clockDate');
            if (timeElement && dateElement) {
                timeElement.textContent = timeStr;
                dateElement.textContent = dateStr;
            }
            const crmTime = document.getElementById('crmClockTime');
            const crmDate = document.getElementById('crmClockDate');
            if (crmTime && crmDate) {
                crmTime.textContent = timeStr;
                crmDate.textContent = dateStr;
            }
        }
        
        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);
        
        window.openMobileSidebar = function() {
            document.body.classList.add('mobile-sidebar-open');
        };

        window.closeMobileSidebar = function() {
            document.body.classList.remove('mobile-sidebar-open');
        };

        // Initialize sidebar functionality when DOM is ready
        function initSidebar() {
            if (window._sidebarAlreadyInit) return;
            window._sidebarAlreadyInit = true;
            document.body.classList.remove('nav-icons');
            document.body.classList.add('nav-text');

            const navToggle = document.getElementById('navModeToggle');
            const navToggleLabel = document.getElementById('navModeToggleLabel');
            const navToggleIcon = document.getElementById('navModeToggleIcon');
            const edgeSidebarToggle = document.getElementById('sidebarToggle');
            const edgeSidebarToggleIcon = edgeSidebarToggle ? edgeSidebarToggle.querySelector('.sidebar-toggle-icon') : null;

            function applySidebarVisibility() {
                const shouldHide = window.innerWidth >= 768 && localStorage.getItem('sidebar_hidden') === '1';
                document.body.classList.toggle('sidebar-hidden', shouldHide);
                if (navToggle && navToggleLabel && navToggleIcon) {
                    navToggleLabel.textContent = shouldHide ? 'Show Nav' : 'Hide Nav';
                    navToggle.title = shouldHide ? 'Show navigation' : 'Hide navigation';
                    navToggleIcon.className = shouldHide ? 'fas fa-eye' : 'fas fa-eye-slash';
                }
                if (edgeSidebarToggle && edgeSidebarToggleIcon) {
                    edgeSidebarToggle.setAttribute('aria-label', shouldHide ? 'Show navigation' : 'Hide navigation');
                    edgeSidebarToggle.setAttribute('title', shouldHide ? 'Show navigation' : 'Hide navigation');
                    edgeSidebarToggleIcon.className = shouldHide ? 'fas fa-chevron-right sidebar-toggle-icon' : 'fas fa-chevron-left sidebar-toggle-icon';
                }
            }

            applySidebarVisibility();

            function toggleSidebarVisibility() {
                if (window.innerWidth < 768) {
                    return;
                }
                const nextHidden = !document.body.classList.contains('sidebar-hidden');
                localStorage.setItem('sidebar_hidden', nextHidden ? '1' : '0');
                applySidebarVisibility();
            }

            if (navToggle) {
                navToggle.addEventListener('click', function() {
                    toggleSidebarVisibility();
                });
            }

            if (edgeSidebarToggle) {
                edgeSidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        return;
                    }
                    toggleSidebarVisibility();
                });
            }

            document.addEventListener('click', function(e) {
                if (window.innerWidth >= 768) {
                    return;
                }
                const sidebar = document.getElementById('sidebar');
                if (!document.body.classList.contains('mobile-sidebar-open') || !sidebar) {
                    return;
                }
                if (!sidebar.contains(e.target) && !e.target.closest('.mobile-nav-toggle')) {
                    closeMobileSidebar();
                }
            });

            // Enable transitions after layout is fully set (prevents page-load flicker)
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    document.body.classList.remove('no-transition');
                    document.body.classList.add('sidebar-ready');
                });
            });
        }
        
        // Run when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSidebar);
        } else {
            // DOM is already ready
            initSidebar();
        }
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeMobileSidebar();
            }
            const shouldHide = window.innerWidth >= 768 && localStorage.getItem('sidebar_hidden') === '1';
            document.body.classList.toggle('sidebar-hidden', shouldHide);
        });
        
        // Toggle Projects sub-menu
        function toggleProjectsMenu() {
            const subMenu = document.getElementById('projectsSubMenu');
            const icon = document.getElementById('projectsMenuIcon');
            const trigger = icon ? icon.closest('.sidebar-link') : null;
            if (subMenu && icon) {
                if (subMenu.style.display === 'none') {
                    subMenu.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                    if (trigger) trigger.classList.add('sidebar-parent-open');
                } else {
                    subMenu.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                    if (trigger) trigger.classList.remove('sidebar-parent-open');
                }
            }
        }
        
        function toggleLeadsMenu() {
            const subMenu = document.getElementById('leadsSubMenu');
            const icon = document.getElementById('leadsMenuIcon');
            const trigger = icon ? icon.closest('.sidebar-link') : null;
            if (subMenu && icon) {
                if (subMenu.style.display === 'none') {
                    subMenu.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                    if (trigger) trigger.classList.add('sidebar-parent-open');
                } else {
                    subMenu.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                    if (trigger) trigger.classList.remove('sidebar-parent-open');
                }
            }
        }

    </script>
    
    @auth
    <!-- Lead assigned modal (global, dismissible) -->
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
                <a id="lead-assigned-view-btn" href="{{ (auth()->user() && (auth()->user()->isTelecaller() || auth()->user()->isSalesExecutive())) ? route('telecaller.tasks').'?status=pending' : route('leads.index') }}" class="px-4 py-2 rounded-lg font-semibold text-white transition" style="background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));">View leads</a>
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
                } else {
                    callBtn.classList.add('hidden');
                }
            }
            if (overlay) overlay.classList.remove('hidden');
            startRingtone();
        };

        window.closeLeadAssignedModal = function() {
            if (overlay) overlay.classList.add('hidden');
            stopRingtone();
        };

        if (document.getElementById('lead-assigned-close-x')) {
            document.getElementById('lead-assigned-close-x').addEventListener('click', closeLeadAssignedModal);
        }
        if (document.getElementById('lead-assigned-cancel-btn')) {
            document.getElementById('lead-assigned-cancel-btn').addEventListener('click', closeLeadAssignedModal);
        }
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) closeLeadAssignedModal();
            });
        }

        var uid = document.querySelector('meta[name="user-id"]') && document.querySelector('meta[name="user-id"]').getAttribute('content');
        var pk = document.querySelector('meta[name="pusher-key"]') && document.querySelector('meta[name="pusher-key"]').getAttribute('content');
        if (uid && pk && typeof Pusher !== 'undefined') {
            try {
                var pusher = new Pusher(pk, {
                    cluster: (document.querySelector('meta[name="pusher-cluster"]') && document.querySelector('meta[name="pusher-cluster"]').getAttribute('content')) || 'mt1',
                    encrypted: true,
                    authEndpoint: '/broadcasting/auth'
                });
                var ch = pusher.subscribe('private-user.' + uid);
                ch.bind('lead.assigned', function(data) {
                    var lead = data.lead || {};
                    var name = lead.name || 'Lead';
                    var phone = lead.phone || '';
                    showLeadAssignedPopup({
                        title: 'New lead assigned',
                        message: 'You have 1 new lead assigned: ' + name + '. View leads to see details and call.',
                        viewUrl: viewUrlDefault,
                        leadPhone: phone,
                        leadName: name
                    });
                });
            } catch (e) { console.warn('Pusher lead-assigned:', e); }
        }
    })();
    </script>
    @endauth

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
            if (!authToken) return;
            var url = (typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : (window.location.origin + '/api')) + '/fcm-subscription';
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + authToken },
                body: JSON.stringify({ fcm_token: fcmToken, device_type: 'web' })
            }).catch(function() {});
        }

        function initFcm() {
            navigator.serviceWorker.register('/fcm-sw.js').then(function(reg) {
                messaging.getToken({ vapidKey: vapidKey, serviceWorkerRegistration: reg }).then(function(token) {
                    if (token) sendFcmTokenToServer(token);
                }).catch(function() {});
            }).catch(function() {});
        }

        messaging.onMessage(function(payload) {
            var n = payload.notification || payload.data || {};
            if (typeof showLeadAssignedPopup === 'function') {
                showLeadAssignedPopup({ title: n.title, message: n.body });
            }
        });

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
    <script>
    // Fix bfcache: if browser shows stale cached page, force fresh reload
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>
</body>
</html>
