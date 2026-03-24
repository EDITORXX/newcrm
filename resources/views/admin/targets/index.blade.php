@extends('layouts.app')

@section('title', 'Target Management - Admin')
@section('page-title', 'Target Management')
@section('page-subtitle')
    @if(auth()->user()->isSalesHead())
        Set targets for Sales Executives and Senior Managers. Sales Executive targets are view-only.
    @else
        Set and manage monthly targets for Sales Executives and Senior Managers
    @endif
@endsection

@php
    $targetsRouteBase = auth()->user()->isCrm() ? 'crm.targets' : 'admin.targets';
@endphp

@section('header-actions')
    <a href="{{ route($targetsRouteBase . '.create') }}" class="btn btn-brand-primary">
        + Set New Target
    </a>
@endsection

@push('styles')
<style>
    /* Scoped styles so layout navigation/header is not affected */
    .targets-page .t-card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.06); }
    .targets-page .t-filter { background: white; padding: 16px; border-radius: 12px; margin-bottom: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.06); display: flex; gap: 12px; align-items: center; }
    .targets-page .t-filter input, .targets-page .t-filter select { padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; }
    .targets-page table { width: 100%; border-collapse: collapse; }
    .targets-page th, .targets-page td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    .targets-page th { background: #f8f9fa; font-weight: 600; color: #333; }
    .targets-page .t-alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
    .targets-page .t-alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .targets-page .t-alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .targets-page .progress-bar { width: 100%; height: 18px; background: #e0e0e0; border-radius: 10px; overflow: hidden; margin-top: 6px; }
    .targets-page .progress-fill { height: 100%; background: #205A44; transition: width 0.3s; }
    .targets-page .progress-fill.warning { background: #ffc107; }
    .targets-page .progress-fill.danger { background: #dc3545; }
    .targets-page .badge { padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-block; }
    .targets-page .badge-success { background: #d4edda; color: #155724; }
    .targets-page .badge-warning { background: #fff3cd; color: #856404; }
    .targets-page .badge-danger { background: #f8d7da; color: #721c24; }
    .targets-page .badge-info { background: #d1ecf1; color: #0c5460; }
</style>
@endpush

@section('content')
    <div class="targets-page">

        @if(session('success'))
            <div class="t-alert t-alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="t-alert t-alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="t-filter">
            <form method="GET" action="{{ route($targetsRouteBase . '.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <label>Month:</label>
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()">
                <button type="submit" class="btn btn-brand-secondary" style="padding: 10px 14px; font-size: 14px;">Filter</button>
            </form>
        </div>

        <div class="t-card">
            @if($targets->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Month</th>
                            <th>Prospects Extract</th>
                            <th>Prospects Verified</th>
                            <th>Calls</th>
                            <th>Visits</th>
                            <th>Meetings</th>
                            <th>Closers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $target)
                            @php
                                $progress = $target->getProgressData();
                                $user = $target->user;
                                $isManager = $user ? $user->isSalesManager() : false;
                                $isExecutive = $user ? $user->isSalesExecutive() : false;
                                $isTelecaller = $user ? $user->isTelecaller() : false;
                            @endphp
                            <tr>
                                <td>
                                    @if($user)
                                        <strong>{{ $user->name }}</strong><br>
                                        <small style="color: #666;">{{ $user->email }}</small><br>
                                        <span class="badge {{ $isExecutive ? 'badge-info' : ($isManager ? 'badge-warning' : 'badge-success') }}" style="margin-top: 4px; display: inline-block;">
                                            {{ $user->getDisplayRoleName() }}
                                        </span>
                                        @if($isManager && $target->manager_target_calculation_logic)
                                            <br>
                                            <small style="color: #16a34a; font-weight: 600; margin-top: 4px; display: inline-block;">
                                                @if($target->manager_target_calculation_logic === 'juniors_sum')
                                                    Logic 1: Juniors Sum
                                                @else
                                                    Logic 2: Individual + Team
                                                @endif
                                                @if($target->manager_junior_scope)
                                                    ({{ $target->manager_junior_scope === 'executives_only' ? 'Executives Only' : 'Executives + Sales Executives' }})
                                                @endif
                                            </small>
                                        @endif
                                    @else
                                        <strong style="color:#dc3545;">User Deleted</strong><br>
                                        <small style="color: #666;">N/A</small><br>
                                        <span class="badge badge-danger" style="margin-top: 4px; display: inline-block;">
                                            Missing User
                                        </span>
                                    @endif
                                </td>
                                <td><strong>{{ $target->target_month->format('M Y') }}</strong></td>
                                <td>
                                    @if(!$user)
                                        <span style="color: #999;">-</span>
                                    @elseif($isManager)
                                        <span style="color: #999;">N/A</span>
                                    @else
                                        <div>{{ $progress['prospects_extract']['actual'] }} / {{ $target->target_prospects_extract }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $progress['prospects_extract']['percentage'] >= 100 ? '' : ($progress['prospects_extract']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $progress['prospects_extract']['percentage']) }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($progress['prospects_extract']['percentage'], 1) }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!$user)
                                        <span style="color: #999;">-</span>
                                    @elseif($isManager)
                                        <span style="color: #999;">N/A</span>
                                    @else
                                        <div>{{ $progress['prospects_verified']['actual'] }} / {{ $target->target_prospects_verified }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $progress['prospects_verified']['percentage'] >= 100 ? '' : ($progress['prospects_verified']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $progress['prospects_verified']['percentage']) }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($progress['prospects_verified']['percentage'], 1) }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @if(!$user)
                                        <span style="color: #999;">-</span>
                                    @elseif($isManager)
                                        <span style="color: #999;">N/A</span>
                                    @else
                                        <div>{{ $progress['calls']['actual'] }} / {{ $target->target_calls }}</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $progress['calls']['percentage'] >= 100 ? '' : ($progress['calls']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $progress['calls']['percentage']) }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($progress['calls']['percentage'], 1) }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $visitsTarget = $isManager && $target->manager_target_calculation_logic 
                                            ? $target->calculateManagerTarget('visits') 
                                            : ($target->target_visits ?? 0);
                                    @endphp
                                    @if($visitsTarget > 0)
                                        <div>{{ $progress['visits']['achieved'] }} / {{ $visitsTarget }}</div>
                                        @php
                                            $visitsPercentage = $visitsTarget > 0 ? min(100, round(($progress['visits']['achieved'] / $visitsTarget) * 100, 1)) : 0;
                                        @endphp
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $visitsPercentage >= 100 ? '' : ($visitsPercentage >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $visitsPercentage }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($visitsPercentage, 1) }}%</small>
                                        @if($isManager && $target->manager_target_calculation_logic && $visitsTarget != $target->target_visits)
                                            <br><small style="color: #16a34a; font-size: 10px;">(Calculated)</small>
                                        @endif
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $meetingsTarget = $isManager && $target->manager_target_calculation_logic 
                                            ? $target->calculateManagerTarget('meetings') 
                                            : ($target->target_meetings ?? 0);
                                    @endphp
                                    @if($meetingsTarget > 0)
                                        <div>{{ $progress['meetings']['achieved'] }} / {{ $meetingsTarget }}</div>
                                        @php
                                            $meetingsPercentage = $meetingsTarget > 0 ? min(100, round(($progress['meetings']['achieved'] / $meetingsTarget) * 100, 1)) : 0;
                                        @endphp
                                        <div class="progress-bar">
                                            <div class="progress-fill {{ $meetingsPercentage >= 100 ? '' : ($meetingsPercentage >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $meetingsPercentage }}%"></div>
                                        </div>
                                        <small style="color: #666;">{{ number_format($meetingsPercentage, 1) }}%</small>
                                        @if($isManager && $target->manager_target_calculation_logic && $meetingsTarget != $target->target_meetings)
                                            <br><small style="color: #16a34a; font-size: 10px;">(Calculated)</small>
                                        @endif
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$user)
                                        <span style="color: #999;">-</span>
                                    @elseif($isManager || $isExecutive)
                                        @if($target->target_closers > 0)
                                            <div>{{ $progress['closers']['achieved'] }} / {{ $target->target_closers }}</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill {{ $progress['closers']['percentage'] >= 100 ? '' : ($progress['closers']['percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ min(100, $progress['closers']['percentage']) }}%"></div>
                                            </div>
                                            <small style="color: #666;">{{ number_format($progress['closers']['percentage'], 1) }}%</small>
                                        @else
                                            <span style="color: #999;">-</span>
                                        @endif
                                    @else
                                        <span style="color: #999;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$user)
                                        <form method="POST" action="{{ route($targetsRouteBase . '.destroy', $target->id) }}" style="display: inline;" onsubmit="return confirm('User missing. Delete this target record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 8px 14px; font-size: 14px;">Delete</button>
                                        </form>
                                    @elseif(auth()->user()->isSalesHead() && $isTelecaller)
                                        <span style="color: #6b7280; font-size: 14px;">
                                            <i class="fas fa-eye mr-2"></i>View Only
                                        </span>
                                    @else
                                        <a href="{{ route($targetsRouteBase . '.edit', $target->id) }}" class="btn btn-brand-secondary" style="padding: 8px 14px; font-size: 14px;">Edit</a>
                                        <form method="POST" action="{{ route($targetsRouteBase . '.destroy', $target->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this target?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 8px 14px; font-size: 14px;">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="text-align: center; color: #666; padding: 40px;">No targets found for this month. <a href="{{ route($targetsRouteBase . '.create', ['month' => $month]) }}">Create one now</a></p>
            @endif
        </div>
    </div>
@endsection

