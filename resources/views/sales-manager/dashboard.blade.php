@extends('sales-manager.layout')

@section('title', 'Dashboard - Assistant Sales Manager')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    .achievement-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .achievement-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .achievement-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 16px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--link-color);
    }
    .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    .pending-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #fef3c7;
        color: #92400e;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .header {
        display: none;
    }
    .asm-hero-kicker {
        margin: 0 0 22px;
        font-size: 22px;
        font-weight: 700;
        color: #063A1C;
        line-height: 1.1;
        font-family: 'Playfair Display', serif;
    }
    
    /* Responsive Styles */
    @media (max-width: 767px) {
        .chart-container {
            height: 250px;
            margin: 16px 0;
        }
        
        .achievement-card {
            padding: 16px;
        }
        
        .achievement-title {
            font-size: 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        
        .achievement-stats {
            flex-direction: column;
            gap: 12px;
        }
        
        .stat-value {
            font-size: 20px;
        }
        
        /* Stats cards responsive - 2 columns on mobile */
        .stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }
        
        .stats-grid > div {
            padding: 16px !important;
        }
        
        .stats-grid > div h3 {
            font-size: 20px !important;
        }
        
        .stats-grid > div p {
            font-size: 12px !important;
        }
        
        /* Hide Team Members card on mobile */
        .team-members-card {
            display: none !important;
        }
        
        /* Hide Pending Tasks card on mobile (keep only 4 cards: Leads, Prospects, Pending Verifications, Over Due) */
        .stats-grid > div:nth-child(6) {
            display: none !important;
        }
        
        /* Green Gradient Background for All Dashboard Cards on Mobile (like chatbot button) */
        .stats-grid > div,
        .stats-grid > div.bg-white,
        .stats-grid > .dashboard-card {
            background: linear-gradient(135deg, #063A1C 0%, #205A44 100%) !important;
            background-color: transparent !important;
            color: white !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(6, 58, 28, 0.4), 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            position: relative;
            overflow: visible;
        }
        
        /* Remove any white background or overlay completely */
        .stats-grid > div.bg-white,
        .stats-grid > div[class*="bg-white"] {
            background: linear-gradient(135deg, #063A1C 0%, #205A44 100%) !important;
            background-color: transparent !important;
        }
        
        /* Remove any overlay pseudo-elements completely */
        .stats-grid > div::before,
        .stats-grid > div::after,
        .stats-grid > div > div::before,
        .stats-grid > div > div::after,
        .stats-grid > div.bg-white::before,
        .stats-grid > div.bg-white::after {
            display: none !important;
            content: none !important;
            background: none !important;
            opacity: 0 !important;
        }
        
        /* Show flex container with full opacity - no white overlay */
        .stats-grid > div > div.flex.items-center.justify-between {
            opacity: 1 !important;
            visibility: visible !important;
            background: transparent !important;
            position: relative;
            z-index: 1;
        }
        
        /* Ensure no white background on any child elements */
        .stats-grid > div > * {
            background: transparent !important;
        }
        
        /* Force remove white background from bg-white class */
        .stats-grid > div.bg-white {
            background: linear-gradient(135deg, #063A1C 0%, #205A44 100%) !important;
            background-color: transparent !important;
            background-image: none !important;
        }
        
        /* Ensure all text is visible and white on green background */
        .stats-grid > div p,
        .stats-grid > div p.text-gray-500,
        .stats-grid > div .text-gray-500 {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .stats-grid > div h3,
        .stats-grid > div h3.text-gray-900,
        .stats-grid > div .text-gray-900 {
            color: white !important;
            font-weight: 700 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Ensure flex container and all child divs are visible */
        .stats-grid > div > div.flex.items-center.justify-between {
            opacity: 1 !important;
            visibility: visible !important;
            display: flex !important;
        }
        
        .stats-grid > div > div.flex.items-center.justify-between > div {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Hide icon boxes completely on mobile - only hide icon divs with bg-* classes */
        .stats-grid > div .bg-indigo-100,
        .stats-grid > div .bg-green-100,
        .stats-grid > div .bg-yellow-100,
        .stats-grid > div .bg-red-100,
        .stats-grid > div .bg-blue-100,
        .stats-grid > div .bg-orange-100 {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Improve card hover effect */
        .stats-grid > div {
            transition: all 0.3s ease !important;
        }
        
        .stats-grid > div:active {
            transform: scale(0.98) !important;
            box-shadow: 0 2px 6px rgba(6, 58, 28, 0.3) !important;
        }
        
        /* Team call stats section */
        #teamCallStatsSection > div:first-child {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start !important;
        }
        
        #teamCallStatsSection > div:first-child > div {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            width: 100%;
        }
        
        #teamCallStatsSection > div:first-child button {
            flex: 1;
            min-width: 80px;
            padding: 8px 12px;
            font-size: 12px;
        }
        
        /* Table responsive */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
        
        .overflow-x-auto table {
            min-width: 600px;
        }
        
        .overflow-x-auto th,
        .overflow-x-auto td {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        /* Quick actions buttons */
        .grid.grid-cols-1.md\:grid-cols-2 > a {
            padding: 16px;
        }
        
        .grid.grid-cols-1.md\:grid-cols-2 > a i {
            font-size: 24px !important;
        }
        
        /* Hide Quick Actions on Mobile */
        .quick-actions-section {
            display: none !important;
        }
    }
    
    /* Recent Tasks Section Styles */
    .recent-tasks-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .recent-task-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 100px;
    }
    
    .recent-task-card .task-name {
        font-size: 13px;
        font-weight: 600;
        color: #063A1C;
        margin: 0;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .recent-task-card .task-time {
        font-size: 11px;
        color: #6b7280;
        display: flex;
        align-items: center;
    }
    
    .recent-task-card .call-btn {
        width: 100%;
        padding: 8px 12px;
        background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
        margin-top: auto;
    }
    
    .recent-task-card .call-btn:hover {
        background: linear-gradient(135deg, #205A44 0%, #15803d 100%);
        transform: translateY(-1px);
    }
    
    /* Desktop: Hide Recent Tasks, Show Quick Actions */
    @media (min-width: 768px) {
        .recent-tasks-mobile {
            display: none !important;
        }
        .quick-actions-section {
            display: block;
        }
        
        /* Dark Green Gradient Background for All Dashboard Cards on Desktop */
        .stats-grid > div,
        .stats-grid > div.bg-white,
        .stats-grid > .dashboard-card {
            background: linear-gradient(135deg, #063A1C 0%, #205A44 100%) !important;
            background-color: transparent !important;
            color: white !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(6, 58, 28, 0.4), 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Ensure all text is visible and white on green background */
        .stats-grid > div p,
        .stats-grid > div p.text-gray-500,
        .stats-grid > div .text-gray-500 {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500 !important;
        }
        
        .stats-grid > div h3,
        .stats-grid > div h3.text-gray-900,
        .stats-grid > div .text-gray-900 {
            color: white !important;
            font-weight: 700 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Card hover effect */
        .stats-grid > div {
            transition: all 0.3s ease !important;
        }
        
        .stats-grid > div:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(6, 58, 28, 0.5), 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }
    }
    
    /* Mobile: Show Recent Tasks, Hide Quick Actions */
    @media (max-width: 767px) {
        .recent-tasks-mobile {
            display: block;
        }
        .quick-actions-section {
            display: none !important;
        }
        
        .recent-tasks-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .recent-task-card {
            padding: 12px;
        }
        
        /* Royal Green Background for All Dashboard Cards on Mobile */
        .stats-grid > div {
            background: #1E8449 !important; /* Royal green color */
            color: white !important;
            border: none !important;
        }
        
        .stats-grid > div p {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .stats-grid > div h3 {
            color: white !important;
        }
        
        /* Icon background - make it lighter/transparent on royal green */
        .stats-grid > div > div:last-child {
            background: rgba(255, 255, 255, 0.2) !important;
        }
        
        .stats-grid > div > div:last-child i {
            color: white !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 1023px) {
        .chart-container {
            height: 280px;
        }
    }
    
    /* Target Cards Styling */
    .target-card-manager,
    .target-card-team {
        position: relative;
        overflow: hidden;
    }
    
    .target-card-manager::before,
    .target-card-team::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.3), transparent 70%);
        pointer-events: none;
    }
    
    .target-card-manager h2,
    .target-card-team h2 {
        position: relative;
        z-index: 1;
    }
    
    .target-card-manager .space-y-5 > div,
    .target-card-team .space-y-5 > div {
        position: relative;
        z-index: 1;
    }
    
    .target-card-manager p,
    .target-card-team p {
        position: relative;
        z-index: 1;
    }
    
    /* Progress bar styling for target cards */
    .target-card-manager .bg-gray-300,
    .target-card-team .bg-gray-300 {
        background-color: rgba(255, 255, 255, 0.3) !important;
    }
    
    .target-card-manager .bg-white,
    .target-card-team .bg-white {
        background-color: rgba(255, 255, 255, 0.95) !important;
    }
    
    /* Responsive adjustments for target cards */
    @media (max-width: 767px) {
        .target-card-manager,
        .target-card-team {
            padding: 20px !important;
        }
        
        .target-card-manager h2,
        .target-card-team h2 {
            font-size: 18px !important;
            margin-bottom: 20px !important;
        }
        
        .target-card-manager .space-y-5 > div,
        .target-card-team .space-y-5 > div {
            margin-bottom: 16px !important;
        }
    }
    .asm-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f6f3ec 100%);
        border: 1px solid #e4e0d7;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 18px 44px rgba(16, 24, 20, 0.06);
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: 1.35fr 1fr;
        gap: 18px;
    }
    .asm-hero-copy h2 {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 30px;
        line-height: 1.1;
        color: #18201b;
        margin-bottom: 10px;
    }
    .asm-hero-copy h2 span {
        color: #0d6b4f;
    }
    .asm-hero-copy p {
        color: #667068;
        font-size: 14px;
        max-width: 580px;
        line-height: 1.6;
    }
    .asm-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }
    .asm-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid #d8d3c8;
        background: #fff;
        color: #18201b;
        font-size: 13px;
        font-weight: 600;
    }
    .asm-action-btn.primary {
        background: linear-gradient(135deg, #0d6b4f 0%, #0f5b45 100%);
        border-color: #0d6b4f;
        color: #fff;
    }
    .asm-focus-panel {
        background: linear-gradient(180deg, #0a1f18 0%, #14372d 100%);
        color: #fff;
        border-radius: 20px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .asm-hero-side {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: stretch;
    }
    .asm-favorites-panel {
        background: linear-gradient(180deg, #fff 0%, #f5f7f6 100%);
        border: 1px solid #d9e2dd;
        border-radius: 20px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    .asm-favorites-panel .eyebrow {
        font-size: 11px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #4b6358;
        margin-bottom: 8px;
    }
    .asm-favorites-panel h3 {
        font-size: 18px;
        font-weight: 700;
        color: #0f2a1f;
        margin-bottom: 10px;
    }
    .favorite-leads-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 4px;
        overflow: hidden;
    }
    .favorite-lead-item {
        border: 1px solid #e5ece8;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fff;
    }
    .favorite-lead-name {
        font-size: 13px;
        font-weight: 700;
        color: #163628;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .favorite-lead-remark {
        font-size: 12px;
        color: #5b6d63;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    .favorite-leads-empty {
        border: 1px dashed #ced9d3;
        border-radius: 12px;
        padding: 14px;
        font-size: 13px;
        color: #6b7f74;
        background: #fbfdfc;
    }
    .asm-focus-panel .eyebrow {
        font-size: 11px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.58);
        margin-bottom: 10px;
    }
    .asm-focus-panel h3 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .asm-focus-panel p {
        font-size: 13px;
        color: rgba(255,255,255,0.76);
        line-height: 1.6;
    }
    .asm-mini-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 18px;
    }
    .asm-mini-metrics div {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        padding: 12px;
    }
    .asm-mini-metrics strong {
        display: block;
        font-size: 18px;
        margin-bottom: 4px;
    }
    .asm-mini-metrics span {
        font-size: 11px;
        color: rgba(255,255,255,0.64);
    }
    .asm-stats-grid {
        gap: 14px !important;
        margin-bottom: 18px !important;
    }
    .asm-stat-card {
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: linear-gradient(135deg, #0a4e39 0%, #126347 52%, #1f7a59 100%) !important;
        box-shadow: 0 18px 36px rgba(6, 58, 28, 0.18) !important;
        color: #fff !important;
        position: relative;
        overflow: hidden;
    }
    .asm-stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.14), transparent 42%);
        pointer-events: none;
    }
    .asm-stat-card .stat-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        position: relative;
        z-index: 1;
    }
    .asm-stat-card .stat-kicker {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255,255,255,0.68) !important;
        margin-bottom: 8px;
    }
    .asm-stat-card .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #ffffff !important;
        line-height: 1;
        text-shadow: 0 1px 2px rgba(0,0,0,0.16);
    }
    .asm-stat-card .stat-label {
        margin-top: 6px;
        font-size: 13px;
        color: rgba(255,255,255,0.92) !important;
    }
    .asm-stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.92);
        color: #0d6b4f;
        font-size: 16px;
        box-shadow: 0 10px 22px rgba(0,0,0,0.12);
    }
    .asm-panel {
        background: #fff;
        border: 1px solid #e4e0d7;
        border-radius: 20px;
        box-shadow: 0 16px 36px rgba(16, 24, 20, 0.05);
    }
    .asm-panel-title {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 24px;
        color: #18201b;
        margin-bottom: 6px;
    }
    .asm-panel-subtitle {
        color: #667068;
        font-size: 13px;
    }
    @media (max-width: 767px) {
        .asm-hero {
            grid-template-columns: 1fr;
            padding: 18px;
            border-radius: 18px;
        }
        .asm-hero-side {
            grid-template-columns: 1fr;
        }
        .asm-hero-copy h2 {
            font-size: 22px;
            font-family: 'Outfit', sans-serif;
        }
        .asm-hero-kicker {
            font-size: 18px;
            margin-bottom: 16px;
        }
        .asm-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .asm-stat-card {
            padding: 16px !important;
        }
        .asm-stat-card .stat-value {
            font-size: 24px;
        }
    }
    .asm-hero-copy p,
    .asm-action-row {
        display: none;
    }
</style>
@endpush

@section('content')
<section class="asm-hero">
    <div class="asm-hero-copy">
        <div class="asm-hero-kicker">Dashboard</div>
        <h2>Good morning, <span>{{ auth()->user()->name }}</span></h2>
    </div>
    <div class="asm-hero-side">
        <div class="asm-focus-panel">
            <div>
                <div class="eyebrow">Today Focus</div>
                <h3>Lead response, verification queue, and overdue follow-ups.</h3>
                <p>Section order ab operational priority ke hisaab se hai, decorative clutter ke hisaab se nahi.</p>
            </div>
            <div class="asm-mini-metrics">
                <div><strong id="pendingTasksHero">0</strong><span>Pending</span></div>
                <div><strong id="overdueTasksHero">0</strong><span>Overdue</span></div>
                <div><strong id="pendingVerificationsHero">0</strong><span>Verify</span></div>
            </div>
        </div>
        <div class="asm-favorites-panel">
            <div>
                <div class="eyebrow">Favorites</div>
                <h3>Favorite Leads</h3>
            </div>
            <div id="favoriteLeadsList" class="favorite-leads-list">
                <div class="favorite-leads-empty">No favorite leads yet</div>
            </div>
        </div>
    </div>
</section>
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 mb-6 stats-grid asm-stats-grid">
    <!-- Stats Cards - Reordered for mobile: Leads Received, Today Prospects, Pending Verifications, Over Due Task, Team Members -->
    
    <!-- 1. Leads Received -->
    <div class="rounded-lg shadow p-6 dashboard-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Pipeline</p>
                <h3 class="stat-value mt-1" id="assignedLeads">0</h3>
                <p class="stat-label">Leads Received</p>
            </div>
            <span class="stat-icon"><i class="fas fa-user-friends"></i></span>
        </div>
    </div>

    <!-- 2. Today's Prospects -->
    <div class="rounded-lg shadow p-6 dashboard-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Today</p>
                <h3 class="stat-value mt-1" id="todayProspects">0</h3>
                <p class="stat-label">Today's Prospects</p>
            </div>
            <span class="stat-icon"><i class="fas fa-star"></i></span>
        </div>
    </div>

    <!-- 3. Pending Verifications -->
    <div class="rounded-lg shadow p-6 dashboard-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Approval Queue</p>
                <h3 class="stat-value mt-1" id="pendingVerifications">0</h3>
                <p class="stat-label">Pending Verifications</p>
            </div>
            <span class="stat-icon"><i class="fas fa-shield-alt"></i></span>
        </div>
    </div>

    <!-- 4. Over Due Task -->
    <div class="rounded-lg shadow p-6 dashboard-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Attention</p>
                <h3 class="stat-value mt-1" id="overdueTasks">0</h3>
                <p class="stat-label">Overdue Tasks</p>
            </div>
            <span class="stat-icon"><i class="fas fa-clock"></i></span>
        </div>
    </div>

    <!-- 5. Team Members (hidden on mobile) -->
    <div class="rounded-lg shadow p-6 dashboard-card team-members-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Coverage</p>
                <h3 class="stat-value mt-1" id="teamMembersCount">0</h3>
                <p class="stat-label">Team Members</p>
            </div>
            <span class="stat-icon"><i class="fas fa-users"></i></span>
        </div>
    </div>

    <!-- 6. Pending Tasks (kept for desktop) -->
    <div class="rounded-lg shadow p-6 dashboard-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Backlog</p>
                <h3 class="stat-value mt-1" id="pendingTasks">0</h3>
                <p class="stat-label">Pending Tasks</p>
            </div>
            <span class="stat-icon"><i class="fas fa-list-check"></i></span>
        </div>
    </div>

    <!-- 7. No response yet -->
    <div class="rounded-lg shadow p-6 dashboard-card asm-stat-card">
        <div class="stat-head">
            <div>
                <p class="stat-kicker">Risk</p>
                <h3 class="stat-value mt-1" id="smNoResponseYetCount">0</h3>
                <p class="stat-label">No Response Yet</p>
            </div>
            <span class="stat-icon"><i class="fas fa-inbox"></i></span>
        </div>
    </div>
</div>

<!-- Leads allocated – no response yet (SM/ASM) -->
<div class="bg-white rounded-lg shadow p-6 mb-6 asm-panel">
    <h2 class="asm-panel-title">
        <i class="fas fa-inbox mr-2 text-[#063A1C]"></i>Leads allocated – no response yet
    </h2>
    <p class="asm-panel-subtitle mb-4">Leads assigned to you on which you haven't responded yet.</p>
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <label for="sm-leads-pending-date-range" class="text-sm font-medium text-gray-700">Date range:</label>
        <select id="sm-leads-pending-date-range" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#063A1C] focus:border-[#063A1C]">
            <option value="this_week">This week</option>
            <option value="this_month" selected>This month</option>
            <option value="all_time">All time</option>
        </select>
    </div>
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Lead name</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Phone</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Assigned at</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Action</th>
                </tr>
            </thead>
            <tbody id="sm-leads-pending-response-tbody" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Tasks Section (Mobile View) -->
<div class="bg-white rounded-lg shadow p-6 mb-6 recent-tasks-mobile">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Tasks</h2>
    <div id="recentTasksGrid" class="recent-tasks-grid">
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-2"></i>
            <p class="text-gray-500 text-sm">Loading tasks...</p>
        </div>
    </div>
</div>

<!-- Team Call Statistics -->
<div class="bg-white rounded-lg shadow p-6 mb-6" id="teamCallStatsSection" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <h2 class="text-xl font-bold text-gray-900">
            <i class="fas fa-phone mr-2"></i>Team Call Statistics
        </h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="loadTeamCallStats('today')" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg text-sm font-medium hover:from-[#205A44] hover:to-[#15803d]">Today</button>
            <button onclick="loadTeamCallStats('this_week')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">This Week</button>
            <button onclick="loadTeamCallStats('this_month')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">This Month</button>
        </div>
    </div>
    
    <!-- Team Call Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="text-sm text-blue-600 mb-1">Team Total Calls</div>
            <div class="text-2xl font-bold text-blue-900" id="teamTotalCalls">0</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-sm text-green-600 mb-1">Total Duration</div>
            <div class="text-2xl font-bold text-green-900" id="teamTotalDuration">0s</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="text-sm text-purple-600 mb-1">Average Duration</div>
            <div class="text-2xl font-bold text-purple-900" id="teamAvgDuration">0s</div>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <div class="text-sm text-orange-600 mb-1">Connection Rate</div>
            <div class="text-2xl font-bold text-orange-900" id="teamConnectionRate">0%</div>
        </div>
    </div>

    <!-- Top Performers -->
    <div id="topPerformersSection" style="display: none;">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performers</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Calls</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Duration</th>
                    </tr>
                </thead>
                <tbody id="topPerformersTable" class="bg-white divide-y divide-gray-200">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Team Call Breakdown Chart -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Calls by Team Member</h3>
        <div class="chart-container" style="position: relative; height: 300px;">
            <canvas id="teamCallsChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap;">
        <a href="{{ route('calls.index') }}" class="px-4 py-2 bg-[#205A44] text-white rounded-lg hover:bg-[#15803d] transition-colors duration-200 text-sm font-medium">
            <i class="fas fa-list mr-2"></i> View All Team Calls
        </a>
    </div>
</div>

<!-- Target vs Achievements Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Manager's Own Targets -->
    <div class="rounded-xl shadow-lg p-6 target-card-manager" id="managerTargetsSection" style="background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-bullseye mr-3 text-white"></i>My Targets vs Achievements
        </h2>
        <div class="space-y-5">
            <!-- Meetings -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-3 text-white text-lg"></i>
                        <span class="text-sm font-medium text-white">Meetings</span>
                    </div>
                    <span class="text-sm font-semibold text-white" id="managerMeetingsProgress">0 / 0 (0%)</span>
                </div>
                <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-2 mt-2">
                    <div class="bg-white h-2 rounded-full transition-all" id="managerMeetingsBar" style="width: 0%"></div>
                </div>
            </div>
            <!-- Visits -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-3 text-white text-lg"></i>
                        <span class="text-sm font-medium text-white">Site Visits</span>
                    </div>
                    <span class="text-sm font-semibold text-white" id="managerVisitsProgress">0 / 0 (0%)</span>
                </div>
                <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-2 mt-2">
                    <div class="bg-white h-2 rounded-full transition-all" id="managerVisitsBar" style="width: 0%"></div>
                </div>
            </div>
            <!-- Closers -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-bullseye mr-3 text-white text-lg"></i>
                        <span class="text-sm font-medium text-white">Closers</span>
                    </div>
                    <span class="text-sm font-semibold text-white" id="managerClosersProgress">0 / 0 (0%)</span>
                </div>
                <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-2 mt-2">
                    <div class="bg-white h-2 rounded-full transition-all" id="managerClosersBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <p class="text-center text-white text-sm mt-6 opacity-90">Set your goals and start tracking!</p>
    </div>

    <!-- Team Targets -->
    <div class="rounded-xl shadow-lg p-6 target-card-team" id="teamTargetsSection" style="background: linear-gradient(135deg, #0F4C75 0%, #1B5E7A 100%);">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-users mr-3 text-white"></i>Team Targets vs Achievements
        </h2>
        <div class="space-y-5">
            <!-- Team Meetings -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-3 text-white text-lg"></i>
                        <span class="text-sm font-medium text-white">Meetings</span>
                    </div>
                    <span class="text-sm font-semibold text-white" id="teamMeetingsProgress">0 / 0 (0%)</span>
                </div>
                <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-2 mt-2">
                    <div class="bg-white h-2 rounded-full transition-all" id="teamMeetingsBar" style="width: 0%"></div>
                </div>
            </div>
            <!-- Team Visits -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-3 text-white text-lg"></i>
                        <span class="text-sm font-medium text-white">Site Visits</span>
                    </div>
                    <span class="text-sm font-semibold text-white" id="teamVisitsProgress">0 / 0 (0%)</span>
                </div>
                <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-2 mt-2">
                    <div class="bg-white h-2 rounded-full transition-all" id="teamVisitsBar" style="width: 0%"></div>
                </div>
            </div>
            <!-- Team Closers -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-bullseye mr-3 text-white text-lg"></i>
                        <span class="text-sm font-medium text-white">Closers</span>
                    </div>
                    <span class="text-sm font-semibold text-white" id="teamClosersProgress">0 / 0 (0%)</span>
                </div>
                <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-2 mt-2">
                    <div class="bg-white h-2 rounded-full transition-all" id="teamClosersBar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <p class="text-center text-white text-sm mt-6 opacity-90">Set your goals and start tracking!</p>
    </div>
</div>

<!-- Individual Team Member Cards Section -->
<div class="mb-6" id="teamMembersCardsSection">
    <h2 class="text-xl font-bold text-gray-900 mb-4">
        <i class="fas fa-users mr-2 text-indigo-600"></i>Team Members Targets vs Achievements
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="teamMembersCardsContainer">
        <!-- Individual team member cards will be loaded here -->
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>Loading team members...</p>
        </div>
    </div>
</div>

<!-- Quick Actions (Hidden on Mobile) -->
<div class="bg-white rounded-lg shadow p-6 quick-actions-section">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('sales-manager.leads') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-all text-center">
            <i class="fas fa-briefcase text-3xl text-indigo-600 mb-2"></i>
            <p class="font-semibold text-gray-900">View Leads</p>
        </a>
        <a href="{{ route('sales-manager.tasks') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all text-center">
            <i class="fas fa-tasks text-3xl text-orange-600 mb-2"></i>
            <p class="font-semibold text-gray-900">View Tasks</p>
        </a>
    </div>
</div>

<!-- Incentives Section -->
<div class="bg-white rounded-lg shadow p-6 mb-6" id="incentivesSection">
    <h2 class="text-xl font-bold text-gray-900 mb-4">
        <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>Earn Incentive
    </h2>
    
    <!-- Incentive Potential -->
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-2 border-green-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-green-900">Incentive Potential</span>
            <span class="text-2xl font-bold text-green-700" id="incentivePotential">₹0</span>
        </div>
        <p class="text-xs text-green-700" id="incentivePotentialDetails">Target: 0 Closers × ₹0 = ₹0</p>
    </div>

    <!-- Pending Incentives -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Pending Incentives</h3>
        <div id="pendingIncentivesList" class="space-y-2">
            <p class="text-gray-500 text-sm">No pending incentives</p>
        </div>
    </div>

    <!-- Verified Incentives -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Earned Incentives</h3>
        <div class="mb-3 p-3 bg-green-50 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Total Earned</span>
                <span class="text-xl font-bold text-green-700" id="totalEarnedIncentives">₹0</span>
            </div>
        </div>
        <div id="verifiedIncentivesList" class="space-y-2">
            <p class="text-gray-500 text-sm">No verified incentives yet</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    const API_TOKEN = '{{ $api_token ?? session("api_token") ?? "" }}';
    
    // Store token in localStorage if available
    if (API_TOKEN) {
        localStorage.setItem('sales_manager_token', API_TOKEN);
    }
    
    function getToken() {
        return API_TOKEN || localStorage.getItem('sales_manager_token') || '{{ session("api_token") ?? "" }}';
    }

    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            console.error('No API token available');
            window.location.href = '{{ route("login") }}';
            return null;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        try {
            console.log(`API Call: ${API_BASE_URL}${endpoint}`);
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
                credentials: 'same-origin',
            });

            console.log(`API Response Status: ${response.status} for ${endpoint}`);

            if (response.status === 401) {
                console.error('Unauthorized - token invalid');
                localStorage.removeItem('sales_manager_token');
                window.location.href = '{{ route("login") }}';
                return null;
            }

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`API Error (${response.status}):`, errorText);
                try {
                    return JSON.parse(errorText);
                } catch (e) {
                    return { success: false, message: errorText };
                }
            }

            const data = await response.json();
            console.log(`API Success for ${endpoint}:`, data);
            return data;
        } catch (error) {
            console.error('API Call Error:', error);
            console.error('Error details:', error.message, error.stack);
            return { success: false, message: error.message };
        }
    }

    async function loadSmLeadsPendingResponse() {
        const tbody = document.getElementById('sm-leads-pending-response-tbody');
        const countEl = document.getElementById('smNoResponseYetCount');
        const dateRange = document.getElementById('sm-leads-pending-date-range')?.value || 'this_month';
        if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Loading...</td></tr>';
        try {
            const data = await apiCall('/leads-pending-response?date_range=' + encodeURIComponent(dateRange));
            if (!data || data.error) {
                if (countEl) countEl.textContent = '0';
                if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-red-600">Error loading data.</td></tr>';
                return;
            }
            const count = data.pending_count ?? 0;
            const leads = data.leads || [];
            if (countEl) countEl.textContent = count;
            const leadShowBase = '{{ url("/leads") }}';
            if (!tbody) return;
            if (leads.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No leads pending response.</td></tr>';
                return;
            }
            tbody.innerHTML = leads.map(function(l) {
                const name = (l.name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const phone = (l.phone || '—');
                const masked = phone.length > 4 ? phone.slice(0, 2) + '****' + phone.slice(-4) : phone;
                const assignedAt = l.assigned_at ? new Date(l.assigned_at).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
                return '<tr><td class="px-4 py-3 text-sm text-gray-900">' + name + '</td><td class="px-4 py-3 text-sm text-gray-600">' + masked + '</td><td class="px-4 py-3 text-sm text-gray-600">' + assignedAt + '</td><td class="px-4 py-3"><a href="' + leadShowBase + '/' + (l.lead_id || '') + '" class="text-[#063A1C] font-medium hover:underline">View</a></td></tr>';
            }).join('');
        } catch (err) {
            console.error('Error loading leads pending response:', err);
            if (countEl) countEl.textContent = '0';
            if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-red-600">Error loading data.</td></tr>';
        }
    }

    async function loadDashboardData() {
        try {
            console.log('Loading dashboard data...');
            console.log('API Token available:', !!getToken());
            
            // Load profile for team stats first
            const profile = await apiCall('/profile');
            console.log('Profile API response:', profile);
            
            if (profile && profile.team_stats) {
                document.getElementById('teamMembersCount').textContent = profile.team_stats.total_members || 0;
                document.getElementById('todayProspects').textContent = profile.team_stats.today_prospects || 0;
                // Get pending verifications from team_stats
                document.getElementById('pendingVerifications').textContent = profile.team_stats.pending_verifications || 0;
                // Get assigned leads count
                document.getElementById('assignedLeads').textContent = profile.team_stats.assigned_leads || 0;
                // Get pending tasks count
                document.getElementById('pendingTasks').textContent = profile.team_stats.pending_tasks || 0;
                // Get overdue tasks count
                document.getElementById('overdueTasks').textContent = profile.team_stats.overdue_tasks || 0;
                const pendingTasksHero = document.getElementById('pendingTasksHero');
                const overdueTasksHero = document.getElementById('overdueTasksHero');
                const pendingVerificationsHero = document.getElementById('pendingVerificationsHero');
                if (pendingTasksHero) pendingTasksHero.textContent = profile.team_stats.pending_tasks || 0;
                if (overdueTasksHero) overdueTasksHero.textContent = profile.team_stats.overdue_tasks || 0;
                if (pendingVerificationsHero) pendingVerificationsHero.textContent = profile.team_stats.pending_verifications || 0;
                renderFavoriteLeads(profile.favorite_leads || []);
                console.log('Team stats updated:', profile.team_stats);
            } else {
                console.error('Profile API failed or no team_stats:', profile);
            }

            // Load leads pending response (count + table)
            loadSmLeadsPendingResponse();

            // Load dashboard data for incentives and targets
            try {
                const dashboardData = await fetch('{{ url("/api/dashboard") }}', {
                    headers: {
                        'Authorization': `Bearer ${getToken()}`,
                        'Accept': 'application/json',
                    }
                });

                if (dashboardData.ok) {
                    const data = await dashboardData.json();
                    console.log('Dashboard data:', data);
                    console.log('Manager targets data:', data.manager_targets);

                    // Load incentives
                    if (data.incentives) {
                        loadIncentives(data.incentives);
                    }

                    // Load incentive potential
                    if (data.incentive_potential) {
                        loadIncentivePotential(data.incentive_potential);
                    }

                    // Load manager targets
                    if (data.manager_targets) {
                        console.log('Loading manager targets:', data.manager_targets);
                        loadManagerTargets(data.manager_targets);
                    } else {
                        console.warn('No manager_targets data found in response');
                    }

                    // Load team targets
                    if (data.team_targets && data.team_targets.team_totals) {
                        loadTeamTargets(data.team_targets.team_totals);
                    }

                    // Load individual team member cards
                    if (data.team_targets && data.team_targets.team_members) {
                        loadTeamMemberCards(data.team_targets.team_members);
                    }
                } else {
                    console.error('Dashboard API error:', dashboardData.status, dashboardData.statusText);
                    const errorText = await dashboardData.text();
                    console.error('Error response:', errorText);
                }
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            console.error('Error details:', error.message, error.stack);
        }
    }

    function renderFavoriteLeads(favoriteLeads) {
        const container = document.getElementById('favoriteLeadsList');
        if (!container) {
            return;
        }

        if (!Array.isArray(favoriteLeads) || favoriteLeads.length === 0) {
            container.innerHTML = '<div class="favorite-leads-empty">No favorite leads yet</div>';
            return;
        }

        container.innerHTML = favoriteLeads.slice(0, 5).map(function(item) {
            const leadId = Number(item.lead_id) || null;
            const name = String(item.name || 'Unknown Lead')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            const remark = String(item.remark || 'No remark added')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            const href = leadId ? `{{ url('/leads') }}/${leadId}` : '#';

            return `
                <a href="${href}" class="favorite-lead-item">
                    <div class="favorite-lead-name">${name}</div>
                    <div class="favorite-lead-remark">${remark}</div>
                </a>
            `;
        }).join('');
    }

    function loadIncentives(incentives) {
        // Pending incentives
        const pendingList = document.getElementById('pendingIncentivesList');
        if (incentives.pending && incentives.pending.length > 0) {
            pendingList.innerHTML = incentives.pending.map(inc => `
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${inc.site_visit?.customer_name || 'N/A'}</p>
                            <p class="text-xs text-gray-600">Status: ${inc.status === 'pending_sales_head' ? 'Awaiting Sales Head' : 'Awaiting CRM'}</p>
                        </div>
                        <span class="text-lg font-bold text-yellow-700">₹${parseFloat(inc.amount).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');
        } else {
            pendingList.innerHTML = '<p class="text-gray-500 text-sm">No pending incentives</p>';
        }

        // Total earned
        document.getElementById('totalEarnedIncentives').textContent = `₹${parseFloat(incentives.total_earned || 0).toFixed(2)}`;

        // Verified incentives
        const verifiedList = document.getElementById('verifiedIncentivesList');
        if (incentives.verified && incentives.verified.length > 0) {
            verifiedList.innerHTML = incentives.verified.slice(0, 5).map(inc => `
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${inc.site_visit?.customer_name || 'N/A'}</p>
                            <p class="text-xs text-gray-600">Verified on ${inc.crm_verified_at ? new Date(inc.crm_verified_at).toLocaleDateString('en-IN') : 'N/A'}</p>
                        </div>
                        <span class="text-lg font-bold text-green-700">₹${parseFloat(inc.amount).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');
        } else {
            verifiedList.innerHTML = '<p class="text-gray-500 text-sm">No verified incentives yet</p>';
        }
    }

    function loadIncentivePotential(potential) {
        document.getElementById('incentivePotential').textContent = `₹${parseFloat(potential.potential || 0).toFixed(2)}`;
        document.getElementById('incentivePotentialDetails').textContent = 
            `Target: ${potential.target_closers || 0} Closers × ₹${parseFloat(potential.incentive_per_closer || 0).toFixed(2)} = ₹${parseFloat(potential.potential || 0).toFixed(2)}`;
    }

    function loadManagerTargets(targets) {
        // Meetings
        const meetings = targets.meetings || {target: 0, achieved: 0, percentage: 0};
        document.getElementById('managerMeetingsProgress').textContent = 
            `${meetings.achieved} / ${meetings.target} (${meetings.percentage}%)`;
        document.getElementById('managerMeetingsBar').style.width = `${meetings.percentage}%`;

        // Visits
        const visits = targets.visits || {target: 0, achieved: 0, percentage: 0};
        document.getElementById('managerVisitsProgress').textContent = 
            `${visits.achieved} / ${visits.target} (${visits.percentage}%)`;
        document.getElementById('managerVisitsBar').style.width = `${visits.percentage}%`;

        // Closers
        const closers = targets.closers || {target: 0, achieved: 0, percentage: 0};
        document.getElementById('managerClosersProgress').textContent = 
            `${closers.achieved} / ${closers.target} (${closers.percentage}%)`;
        document.getElementById('managerClosersBar').style.width = `${closers.percentage}%`;
    }

    function loadTeamTargets(teamTotals) {
        // Team Meetings
        const meetings = teamTotals.meetings || {target: 0, achieved: 0, percentage: 0};
        document.getElementById('teamMeetingsProgress').textContent = 
            `${meetings.achieved} / ${meetings.target} (${meetings.percentage}%)`;
        document.getElementById('teamMeetingsBar').style.width = `${meetings.percentage}%`;

        // Team Visits
        const visits = teamTotals.visits || {target: 0, achieved: 0, percentage: 0};
        document.getElementById('teamVisitsProgress').textContent = 
            `${visits.achieved} / ${visits.target} (${visits.percentage}%)`;
        document.getElementById('teamVisitsBar').style.width = `${visits.percentage}%`;

        // Team Closers
        const closers = teamTotals.closers || {target: 0, achieved: 0, percentage: 0};
        document.getElementById('teamClosersProgress').textContent = 
            `${closers.achieved} / ${closers.target} (${closers.percentage}%)`;
        document.getElementById('teamClosersBar').style.width = `${closers.percentage}%`;
    }

    function loadTeamMemberCards(teamMembers) {
        const container = document.getElementById('teamMembersCardsContainer');
        
        if (!teamMembers || teamMembers.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-500 col-span-full"><p>No team members with targets found.</p></div>';
            return;
        }

        // Generate cards for each team member
        container.innerHTML = teamMembers.map(member => {
            const meetings = member.targets.meetings || {target: 0, achieved: 0, percentage: 0};
            const visits = member.targets.visits || {target: 0, achieved: 0, percentage: 0};
            const closers = member.targets.closers || {target: 0, achieved: 0, percentage: 0};
            
            // Determine card color based on role
            let cardGradient = 'linear-gradient(135deg, #0F4C75 0%, #1B5E7A 100%)'; // Default teal
            if (member.user_role === 'telecaller') {
                cardGradient = 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'; // Blue
            } else if (member.user_role === 'sales_executive') {
                cardGradient = 'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)'; // Purple
            }

            return `
                <div class="rounded-xl shadow-lg p-6" style="background: ${cardGradient};">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-white"></i>${member.user_name}
                    </h3>
                    <p class="text-xs text-white opacity-80 mb-4 uppercase">${member.user_role_name}</p>
                    
                    <div class="space-y-4">
                        <!-- Meetings -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-2 text-white text-sm"></i>
                                    <span class="text-xs font-medium text-white">Meetings</span>
                                </div>
                                <span class="text-xs font-semibold text-white">${meetings.achieved} / ${meetings.target} (${meetings.percentage}%)</span>
                            </div>
                            <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-1.5 mt-1">
                                <div class="bg-white h-1.5 rounded-full transition-all" style="width: ${meetings.percentage}%"></div>
                            </div>
                        </div>
                        
                        <!-- Visits -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-white text-sm"></i>
                                    <span class="text-xs font-medium text-white">Site Visits</span>
                                </div>
                                <span class="text-xs font-semibold text-white">${visits.achieved} / ${visits.target} (${visits.percentage}%)</span>
                            </div>
                            <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-1.5 mt-1">
                                <div class="bg-white h-1.5 rounded-full transition-all" style="width: ${visits.percentage}%"></div>
                            </div>
                        </div>
                        
                        <!-- Closers -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <i class="fas fa-bullseye mr-2 text-white text-sm"></i>
                                    <span class="text-xs font-medium text-white">Closers</span>
                                </div>
                                <span class="text-xs font-semibold text-white">${closers.achieved} / ${closers.target} (${closers.percentage}%)</span>
                            </div>
                            <div class="w-full bg-gray-300 bg-opacity-30 rounded-full h-1.5 mt-1">
                                <div class="bg-white h-1.5 rounded-full transition-all" style="width: ${closers.percentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Load recent tasks for mobile view
    async function loadRecentTasks() {
        try {
            const isMobile = window.innerWidth <= 767;
            const recentTasksSection = document.querySelector('.recent-tasks-mobile');
            const tasksGrid = document.getElementById('recentTasksGrid');
            
            if (!tasksGrid) return;
            
            if (!isMobile) {
                // Hide recent tasks section on desktop
                if (recentTasksSection) {
                    recentTasksSection.style.display = 'none';
                }
                return;
            }
            
            // Show section on mobile
            if (recentTasksSection) {
                recentTasksSection.style.display = 'block';
            }
            
            const result = await apiCall('/tasks?status=pending');
            
            if (result && result.success && result.data && Array.isArray(result.data) && result.data.length > 0) {
                const tasks = result.data.slice(0, 4); // Limit to 4 tasks
                
                tasksGrid.innerHTML = tasks.map(task => {
                    const lead = task.lead || {};
                    let leadName = lead.name || 'Prospect';
                    
                    // Extract name from title if needed
                    if (task.title && !lead.name) {
                        const titleMatch = task.title.match(/prospect verification:\s*(.+)/i);
                        if (titleMatch) {
                            leadName = titleMatch[1].trim();
                        }
                    }
                    
                    const scheduledAt = task.scheduled_at ? new Date(task.scheduled_at).toLocaleString('en-IN', {
                        day: '2-digit',
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'Not scheduled';
                    
                    const leadId = lead.id || (task.lead_id || null);
                    
                    return `
                        <div class="recent-task-card">
                            <div class="task-name">${leadName}</div>
                            <div class="task-time">
                                <i class="fas fa-clock" style="font-size: 10px; margin-right: 4px;"></i>
                                ${scheduledAt}
                            </div>
                            <button class="call-btn" onclick="handleTaskCall(${task.id}, ${leadId ? leadId : 'null'})">
                                <i class="fas fa-phone"></i>
                                Call
                            </button>
                        </div>
                    `;
                }).join('');
            } else {
                // Hide if no tasks
                tasksGrid.innerHTML = '';
            }
        } catch (error) {
            console.error('Error loading recent tasks:', error);
            const tasksGrid = document.getElementById('recentTasksGrid');
            if (tasksGrid) {
                tasksGrid.innerHTML = '';
            }
        }
    }
    
    // Handle task call button click
    function handleTaskCall(taskId, leadId) {
        // Navigate to tasks page - the task will be highlighted/opened there
        window.location.href = `{{ url('/sales-manager/tasks') }}?task=${taskId}`;
    }

    // Initialize on page load
    (function() {
        loadDashboardData();
        loadRecentTasks();

        const smPendingDateRange = document.getElementById('sm-leads-pending-date-range');
        if (smPendingDateRange) smPendingDateRange.addEventListener('change', loadSmLeadsPendingResponse);
        
        // Reload recent tasks on window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(loadRecentTasks, 200);
        });
    })();
</script>
@endpush
