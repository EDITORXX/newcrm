<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Brickly CRM - Streamline Your Sales Pipeline</title>
    <meta name="description" content="Professional CRM platform designed for real estate companies. Manage leads, track prospects, schedule meetings, verify site visits, and close deals efficiently.">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#0F5132">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Base CRM">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            950: '#032107',
                            900: '#0B3D0F',
                            800: '#0F5132',
                            700: '#166534',
                            600: '#15803d',
                            500: '#16a34a',
                            400: '#22c55e',
                            100: '#dcfce7',
                            50:  '#f0fdf4'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .gradient-mesh {
            background:
                radial-gradient(at 0% 0%, rgba(22, 101, 52, 0.28) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(15, 81, 50, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(22, 101, 52, 0.18) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(11, 61, 15, 0.12) 0px, transparent 50%);
        }
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-4px);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
    
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-brand-700 to-brand-900 flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-building text-white text-lg"></i>
                    </div>
                    <div>
                        <div class="text-base font-black tracking-tight">Brickly CRM</div>
                        <div class="text-[10px] text-slate-400 font-medium hidden sm:block">Professional Sales Management</div>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-6 text-sm font-semibold">
                    <a href="#features" class="text-slate-400 hover:text-white transition">Features</a>
                    <a href="#modules" class="text-slate-400 hover:text-white transition">Modules</a>
                    <a href="#roles" class="text-slate-400 hover:text-white transition">Roles</a>
                    <a href="#workflow" class="text-slate-400 hover:text-white transition">Workflow</a>
                </div>

                <div class="flex items-center gap-2">
                    <button id="installButton"
                            class="hidden px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-sm font-semibold transition"
                            type="button">
                        <i class="fa-solid fa-download mr-1.5"></i>
                        <span class="hidden sm:inline">Install</span>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-900 hover:from-brand-600 hover:to-brand-800 text-white font-bold text-sm shadow-lg transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-brand-700 to-brand-900 hover:from-brand-600 hover:to-brand-800 text-white font-bold text-sm shadow-lg transition">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-mesh pt-16 pb-20 sm:pt-20 sm:pb-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <!-- Left: Headline -->
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-xs font-bold mb-6">
                        <span class="h-2 w-2 rounded-full bg-brand-500 animate-pulse"></span>
                        Production Ready
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.1] tracking-tight">
                        Complete CRM Platform for
                        <span class="bg-gradient-to-r from-brand-500 to-brand-400 bg-clip-text text-transparent">Real Estate Teams</span>
                    </h1>
                    
                    <p class="mt-6 text-lg text-slate-300 leading-relaxed max-w-2xl">
                        Streamline your entire sales pipeline from lead capture to deal closure. 
                        Built-in verification workflows, team management, target tracking, and powerful integrations 
                        including Meta Sheets sync and WhatsApp messaging.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               class="group px-8 py-4 rounded-xl bg-gradient-to-r from-brand-700 to-brand-900 hover:from-brand-600 hover:to-brand-800 text-white font-bold shadow-xl shadow-brand-900/30 transition-all hover:scale-105">
                                Go to Dashboard
                                <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="group px-8 py-4 rounded-xl bg-gradient-to-r from-brand-700 to-brand-900 hover:from-brand-600 hover:to-brand-800 text-white font-bold shadow-xl shadow-brand-900/30 transition-all hover:scale-105">
                                Get Started
                                <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        @endauth
                        
                        <a href="#features" 
                           class="px-8 py-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 font-bold transition">
                            Explore Features
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div class="mt-10 flex flex-wrap items-center gap-6 text-sm">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fa-solid fa-check-circle text-brand-500"></i>
                            <span>PWA Enabled</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fa-solid fa-check-circle text-brand-500"></i>
                            <span>Meta Integration</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fa-solid fa-check-circle text-brand-500"></i>
                            <span>WhatsApp Ready</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Overview Card -->
                <div class="lg:col-span-5">
                    <div class="rounded-3xl bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/10 overflow-hidden shadow-2xl">
                        <div class="p-8">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <div class="text-xs font-bold text-brand-400 uppercase tracking-wider">Platform Overview</div>
                                    <h3 class="mt-2 text-2xl font-black">End-to-End Sales Management</h3>
                                </div>
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-brand-700 to-brand-900 flex items-center justify-center shadow-lg">
                                    <i class="fa-solid fa-chart-line text-white text-xl"></i>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-6 w-6 rounded-lg bg-brand-500/20 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-users-gear text-brand-400 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">Lead Management</div>
                                        <div class="text-xs text-slate-400 mt-1">Capture, assign, and track leads through the complete pipeline</div>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-6 w-6 rounded-lg bg-blue-400/20 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-clipboard-check text-blue-300 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">Verification Workflows</div>
                                        <div class="text-xs text-slate-400 mt-1">CRM/Admin verification for prospects, meetings, visits, and closers</div>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-6 w-6 rounded-lg bg-purple-400/20 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-bullseye text-purple-300 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">Target & Performance Tracking</div>
                                        <div class="text-xs text-slate-400 mt-1">Set monthly targets, track achievements, and monitor team performance</div>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="mt-1 h-6 w-6 rounded-lg bg-orange-400/20 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-brands fa-facebook text-orange-300 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">Meta Sheets Integration</div>
                                        <div class="text-xs text-slate-400 mt-1">One-click lead sync from Facebook/Meta lead forms with duplicate detection</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-white/10">
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Quick Start</div>
                                <ol class="space-y-2 text-sm">
                                    <li class="flex items-start gap-3">
                                        <span class="flex-shrink-0 h-6 w-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-bold">1</span>
                                        <span class="text-slate-300">Login with your credentials</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="flex-shrink-0 h-6 w-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-bold">2</span>
                                        <span class="text-slate-300">Configure integrations (Meta Sheets, WhatsApp)</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="flex-shrink-0 h-6 w-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-bold">3</span>
                                        <span class="text-slate-300">Import and assign leads to your sales team</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="flex-shrink-0 h-6 w-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs font-bold">4</span>
                                        <span class="text-slate-300">Track meetings, visits, and manage closures</span>
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <div class="px-8 py-5 bg-slate-900/80 backdrop-blur">
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-sm text-slate-400">Need help with setup?</div>
                                <a href="#faq" class="text-sm font-bold text-brand-500 hover:text-brand-400 transition">View FAQ →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-700/20 border border-brand-700/30 text-xs font-bold text-brand-400 mb-4">
                    <i class="fa-solid fa-sparkles"></i>
                    CORE CAPABILITIES
                </div>
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Comprehensive Feature Set</h2>
                <p class="mt-4 text-slate-400 text-lg">Everything you need to manage your real estate sales operations in one powerful platform.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $features = [
                        ['icon'=>'fa-filter','title'=>'Lead Management','desc'=>'Centralized lead database with advanced search, filtering, and status tracking. Import from multiple sources including Meta Sheets and manual entry.'],
                        ['icon'=>'fa-clipboard-check','title'=>'Verification System','desc'=>'Built-in approval workflows for prospects, meetings, site visits, and closer requests. CRM/Admin can verify with photo evidence requirements.'],
                        ['icon'=>'fa-user-check','title'=>'Prospect Pipeline','desc'=>'Sales executives create prospects from leads. Manager verification required before moving forward in the pipeline.'],
                        ['icon'=>'fa-handshake','title'=>'Meeting Management','desc'=>'Schedule meetings, mark as completed with photo proof, and track achievements. CRM verification creates counted achievements.'],
                        ['icon'=>'fa-map-marker-alt','title'=>'Site Visit Tracking','desc'=>'Complete site visit workflow from scheduling to completion (with photos) to CRM verification and achievement counting.'],
                        ['icon'=>'fa-bullseye','title'=>'Target Setting','desc'=>'Set monthly targets for calls, meetings, visits, and closers. Real-time progress tracking and performance monitoring for your entire team.'],
                        ['icon'=>'fa-users-gear','title'=>'Lead Assignment','desc'=>'Powerful assignment dashboard for unassigned leads. Bulk assign to team members with single or multiple select. Safe delete operations.'],
                        ['icon'=>'fa-plug','title'=>'Meta Sheets Integration','desc'=>'Connect Facebook/Meta lead forms. One-click sync with intelligent duplicate detection and phone validation. Test before sync.'],
                        ['icon'=>'fa-download','title'=>'Export & Reporting','desc'=>'Export leads, prospects, meetings, visits, and closers to CSV. Multiple filtering options for comprehensive reporting.'],
                        ['icon'=>'fab fa-whatsapp','title'=>'WhatsApp Integration','desc'=>'Built-in WhatsApp chat support for customer communication. Template management and group messaging capabilities.'],
                        ['icon'=>'fa-mobile-screen-button','title'=>'Progressive Web App','desc'=>'Install on mobile devices as native app. Offline-capable with service worker. Works on iOS and Android.'],
                        ['icon'=>'fa-shield-halved','title'=>'Role-Based Access','desc'=>'Granular permissions for Admin, CRM, Sales Head, Senior Manager, and Sales Executive roles. Secure and scalable.'],
                    ];
                @endphp

                @foreach($features as $f)
                    <div class="hover-lift rounded-2xl bg-slate-800/50 backdrop-blur border border-slate-700/50 p-6 hover:bg-slate-800/70 hover:border-brand-700/50 transition">
                        <div class="flex items-start gap-4">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-brand-700/20 to-brand-900/20 border border-brand-700/30 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid {{ $f['icon'] }} text-brand-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-base">{{ $f['title'] }}</h3>
                                <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $f['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section id="modules" class="py-20 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Platform Modules</h2>
                <p class="mt-4 text-slate-400 text-lg">Integrated modules designed for real estate sales workflows.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $modules = [
                        ['icon'=>'fa-users','name'=>'Leads','desc'=>'Complete lead lifecycle management'],
                        ['icon'=>'fa-user-check','name'=>'Prospects','desc'=>'Verified prospect tracking'],
                        ['icon'=>'fa-handshake','name'=>'Meetings','desc'=>'Schedule & verify meetings'],
                        ['icon'=>'fa-map-marker-alt','name'=>'Site Visits','desc'=>'Visit scheduling & verification'],
                        ['icon'=>'fa-trophy','name'=>'Closers','desc'=>'Deal closure workflow'],
                        ['icon'=>'fa-clipboard-check','name'=>'Verifications','desc'=>'Approval queue management'],
                        ['icon'=>'fa-chart-line','name'=>'Exports','desc'=>'Data export & reporting'],
                        ['icon'=>'fab fa-facebook','name'=>'Integrations','desc'=>'Meta Sheets & WhatsApp'],
                        ['icon'=>'fa-users-between-lines','name'=>'Lead Assignment','desc'=>'Assign & distribute leads'],
                        ['icon'=>'fa-cloud-upload-alt','name'=>'Lead Import','desc'=>'Bulk import & smart import'],
                        ['icon'=>'fa-bullseye','name'=>'Targets','desc'=>'Monthly target management'],
                        ['icon'=>'fa-phone','name'=>'Calls','desc'=>'Call logs & tracking'],
                    ];
                @endphp

                @foreach($modules as $m)
                    <div class="rounded-xl bg-slate-900/50 border border-slate-800 p-5 hover:border-brand-700/50 transition">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-lg bg-brand-700/10 border border-brand-700/20 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid {{ $m['icon'] }} text-brand-500"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $m['name'] }}</div>
                                <div class="text-xs text-slate-500 mt-1">{{ $m['desc'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles" class="py-20 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Role-Based Access Control</h2>
                <p class="mt-4 text-slate-400 text-lg">Granular permissions ensure the right access for every team member.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                @php
                    $roles = [
                        [
                            'name'=>'Administrator',
                            'badge'=>'Full Access',
                            'color'=>'red',
                            'permissions'=>[
                                'Complete system access including users, roles, and settings',
                                'All verification workflows (prospects, meetings, visits, closers)',
                                'Target management, lead imports, and assignments',
                                'System configuration, deployment, and integrations',
                                'Export and reporting capabilities'
                            ]
                        ],
                        [
                            'name'=>'CRM Manager',
                            'badge'=>'Core Operations',
                            'color'=>'blue',
                            'permissions'=>[
                                'User management for sales team members',
                                'Target setting and performance monitoring',
                                'Lead import (manual, CSV, smart import) and assignment',
                                'Meta Sheets integration and one-click sync',
                                'Verifications and exports'
                            ]
                        ],
                        [
                            'name'=>'Sales Head',
                            'badge'=>'Team Oversight',
                            'color'=>'purple',
                            'permissions'=>[
                                'Team member management and oversight',
                                'View targets (can set for Assistant Managers and Managers)',
                                'Access to team leads, prospects, meetings, and visits',
                                'Export and reporting for team performance',
                                'Verification access for team activities'
                            ]
                        ],
                        [
                            'name'=>'Senior Manager',
                            'badge'=>'Field Operations',
                            'color'=>'green',
                            'permissions'=>[
                                'Verify prospects created by sales executives',
                                'Schedule and complete meetings (with photo proof)',
                                'Schedule and complete site visits (with photo proof)',
                                'Request closers with documentation',
                                'Team lead and performance visibility'
                            ]
                        ],
                        [
                            'name'=>'Sales Executive',
                            'badge'=>'Front-line',
                            'color'=>'yellow',
                            'permissions'=>[
                                'Assigned leads and tasks management',
                                'Call leads and create prospects',
                                'Submit prospects for manager verification',
                                'Update lead status and requirements',
                                'Personal performance dashboard'
                            ]
                        ],
                        [
                            'name'=>'Finance Manager',
                            'badge'=>'Financial',
                            'color'=>'indigo',
                            'permissions'=>[
                                'Incentive tracking dashboard',
                                'Performance-based incentive calculations',
                                'Closer and visit achievement reports',
                                'Financial overview and analytics'
                            ]
                        ],
                    ];
                @endphp

                @foreach($roles as $role)
                    <div class="rounded-2xl bg-slate-800/50 border border-slate-700/50 p-6 hover:border-brand-700/50 transition">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-black">{{ $role['name'] }}</h3>
                                <div class="inline-block mt-2 px-3 py-1 rounded-full bg-{{ $role['color'] }}-500/10 border border-{{ $role['color'] }}-500/20 text-{{ $role['color'] }}-300 text-xs font-bold">
                                    {{ $role['badge'] }}
                                </div>
                            </div>
                        </div>
                        <ul class="space-y-2.5 text-sm text-slate-400">
                            @foreach($role['permissions'] as $perm)
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-circle-check text-brand-500 mt-0.5 flex-shrink-0"></i>
                                    <span>{{ $perm }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section id="workflow" class="py-20 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">How It Works</h2>
                <p class="mt-4 text-slate-400 text-lg">Three distinct workflows to handle different sales scenarios.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Flow 1 -->
                <div class="rounded-2xl bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 p-6">
                    <div class="flex items-start gap-3 mb-6">
                        <div class="h-10 w-10 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-route text-brand-500"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg">Sales Executive → Closer</h3>
                            <p class="text-xs text-slate-500 mt-1">Standard pipeline workflow</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>1. Lead Assignment</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                CRM/Admin assigns leads to sales executives for calling and initial contact.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>2. Prospect Creation</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Sales executive calls lead, collects information, and creates prospect for manager verification.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>3. Manager Verification</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Manager reviews and verifies prospect. Only direct manager can approve.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>4. Meeting → Visit → Closer</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Manager schedules meeting, completes with photos, CRM verifies. Same for site visit. Then closer request with CRM verification for final closure.
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Flow 2 -->
                <div class="rounded-2xl bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 p-6">
                    <div class="flex items-start gap-3 mb-6">
                        <div class="h-10 w-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-diagram-project text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg">Manager Direct Lead</h3>
                            <p class="text-xs text-slate-500 mt-1">Manager-owned pipeline</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>1. Manager Creates Lead</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Manager can create leads directly without sales executive involvement.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>2. Direct to Meeting</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Skip prospect verification. Manager schedules meetings directly from their own leads.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>3. Standard Verification Flow</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Meeting/visit completion still requires CRM verification and photo proof for achievements.
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Flow 3 -->
                <div class="rounded-2xl bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 p-6">
                    <div class="flex items-start gap-3 mb-6">
                        <div class="h-10 w-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg">Dead Lead Management</h3>
                            <p class="text-xs text-slate-500 mt-1">Safe archival workflow</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>1. Mark as Dead (Any Stage)</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Manager can mark lead as dead at any point with mandatory reason field.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>2. Hidden from Manager View</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                Dead leads automatically hidden from manager dashboards and reports.
                            </div>
                        </details>

                        <details class="group">
                            <summary class="cursor-pointer rounded-xl bg-slate-800/80 border border-slate-700 p-4 font-semibold text-sm hover:bg-slate-800 transition list-none">
                                <div class="flex items-center justify-between">
                                    <span>3. Admin/CRM Trash Access</span>
                                    <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                                </div>
                            </summary>
                            <div class="mt-2 px-4 text-sm text-slate-400">
                                All dead leads visible in Admin/CRM trash for audit and analysis purposes.
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Frequently Asked Questions</h2>
                <p class="mt-4 text-slate-400">Quick answers to common questions about the platform.</p>
            </div>

            <div class="space-y-4">
                <details class="group rounded-xl bg-slate-800/50 border border-slate-700 p-6 hover:bg-slate-800 transition">
                    <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
                        <span>How does the PWA (Progressive Web App) installation work?</span>
                        <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                    </summary>
                    <div class="mt-4 text-sm text-slate-400 leading-relaxed">
                        The system supports PWA installation for mobile devices. On Chrome/Edge, an "Install" button appears when PWA requirements are met. 
                        On iOS, use Safari's "Share → Add to Home Screen" option. Once installed, the app works like a native mobile application.
                    </div>
                </details>

                <details class="group rounded-xl bg-slate-800/50 border border-slate-700 p-6 hover:bg-slate-800 transition">
                    <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
                        <span>What is Meta Sheets integration and how does it work?</span>
                        <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                    </summary>
                    <div class="mt-4 text-sm text-slate-400 leading-relaxed">
                        Meta Sheets integration connects your Facebook/Meta lead forms to the CRM. You can configure sheet URL, 
                        map columns to CRM fields, test the connection, and sync leads with one click. The system includes intelligent 
                        duplicate detection and phone number validation (10-15 digits) to prevent invalid data.
                    </div>
                </details>

                <details class="group rounded-xl bg-slate-800/50 border border-slate-700 p-6 hover:bg-slate-800 transition">
                    <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
                        <span>Are delete operations safe? Can deleted leads be recovered?</span>
                        <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                    </summary>
                    <div class="mt-4 text-sm text-slate-400 leading-relaxed">
                        The system uses soft deletes for most operations. Deleted leads can be viewed in the trash/dead leads section by Admin/CRM. 
                        Lead assignment delete operations only work on unassigned leads for safety. All delete actions include confirmation prompts.
                    </div>
                </details>

                <details class="group rounded-xl bg-slate-800/50 border border-slate-700 p-6 hover:bg-slate-800 transition">
                    <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
                        <span>How do verification workflows ensure quality control?</span>
                        <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                    </summary>
                    <div class="mt-4 text-sm text-slate-400 leading-relaxed">
                        Every critical step requires verification: Prospects need manager approval, meetings and site visits require photo proof 
                        and CRM verification before counting as achievements. Closers need documentation and final CRM approval before marking deals as won.
                    </div>
                </details>

                <details class="group rounded-xl bg-slate-800/50 border border-slate-700 p-6 hover:bg-slate-800 transition">
                    <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
                        <span>Can I export data for external reporting?</span>
                        <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                    </summary>
                    <div class="mt-4 text-sm text-slate-400 leading-relaxed">
                        Yes. The export module supports CSV exports for leads, prospects, meetings, site visits, closers, and dead leads. 
                        Multiple filtering options available. Accessible by Admin, CRM, Senior Manager, and Sales Head roles.
                    </div>
                </details>

                <details class="group rounded-xl bg-slate-800/50 border border-slate-700 p-6 hover:bg-slate-800 transition">
                    <summary class="cursor-pointer font-bold flex items-center justify-between list-none">
                        <span>How are monthly targets and achievements tracked?</span>
                        <i class="fa-solid fa-chevron-down group-open:rotate-180 transition-transform text-slate-500"></i>
                    </summary>
                    <div class="mt-4 text-sm text-slate-400 leading-relaxed">
                        Admin/CRM/Sales Head can set monthly targets for calls, prospects, meetings, visits, and closers. 
                        The system tracks real-time progress with percentage completion. For managers, target calculation can use sum of juniors' 
                        targets or individual + team logic. Achievements update automatically when CRM verifies activities.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-slate-950 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-brand-700 to-brand-900 flex items-center justify-center">
                        <i class="fa-solid fa-building text-white"></i>
                    </div>
                    <div>
                        <div class="font-black">Brickly CRM</div>
                        <div class="text-xs text-slate-500">© {{ date('Y') }} All rights reserved</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 text-sm font-semibold">
                    <a href="#features" class="text-slate-400 hover:text-white transition">Features</a>
                    <a href="#modules" class="text-slate-400 hover:text-white transition">Modules</a>
                    <a href="#roles" class="text-slate-400 hover:text-white transition">Roles</a>
                    <a href="#workflow" class="text-slate-400 hover:text-white transition">Workflow</a>
                    <a href="#faq" class="text-slate-400 hover:text-white transition">FAQ</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- PWA Installation Script (Simplified & Reliable) -->
    <script>
        let deferredPrompt = null;
        const installBtn = document.getElementById('installButton');

        function isIOS() {
            return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        }

        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        }

        function showInstallButton() {
            if (installBtn) {
                installBtn.classList.remove('hidden');
            }
        }

        function hideInstallButton() {
            if (installBtn) {
                installBtn.classList.add('hidden');
            }
        }

        async function installPWA() {
            // Already installed
            if (isStandalone()) {
                alert('Application is already installed on your device.');
                hideInstallButton();
                return;
            }

            // If we have deferred prompt (Android/Chrome/Edge)
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`Install prompt outcome: ${outcome}`);
                deferredPrompt = null;
                
                if (outcome === 'accepted') {
                    hideInstallButton();
                }
                return;
            }

            // iOS-specific instructions
            if (isIOS()) {
                alert('To install on iOS:\n\n1. Tap the Share button (⎋)\n2. Select "Add to Home Screen"\n3. Tap "Add"');
                return;
            }

            // Fallback for browsers that don't fire beforeinstallprompt
            alert('To install this app:\n\nUse your browser\'s menu and look for "Install app" or "Add to home screen" option.');
        }

        // Attach click handler
        if (installBtn) {
            installBtn.addEventListener('click', (e) => {
                e.preventDefault();
                installPWA();
            });
        }

        // Listen for install prompt availability
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallButton();
            console.log('PWA install prompt is available');
        });

        // Track successful installation
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            hideInstallButton();
            deferredPrompt = null;
        });

        // Show install button on iOS devices
        if (isIOS() && !isStandalone()) {
            showInstallButton();
        }

        // Register service worker (if available)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js?v=' + Date.now())
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed'));
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
