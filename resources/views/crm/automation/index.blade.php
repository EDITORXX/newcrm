@extends('layouts.app')

@section('title', 'Automation - Base CRM')
@section('page-title', 'Lead Automation')
@section('page-subtitle', 'Automatically import and distribute leads from Google Sheets or CSV/Excel files')

@section('header-actions')
    <a href="{{ route('admin.automation.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        + Create Automation
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Lead Automation Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Lead Automation</h2>
                <p class="text-sm text-gray-500 mt-1">Automatically distribute leads from any source to your team</p>
            </div>
            <a href="{{ route('admin.automation.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                + Create Automation
            </a>
        </div>

        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">Manage Automations</h3>
            <p class="text-gray-500 mb-4">Create and manage lead distribution rules for Facebook, Pabbly, MCube, Google Sheets and more.</p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('admin.automation.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm">
                    View All Rules
                </a>
                <a href="{{ route('admin.automation.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium text-sm">
                    Create Automation
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mt-8">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-800 mb-1">About Lead Automation</h3>
                <p class="text-sm text-blue-700">
                    Lead Automation automatically imports leads from Google Sheets or CSV/Excel files and distributes them to your team based on your configured distribution rules. 
                    You can set up percentage-based, random, or one-sheet-per-user distribution. After assignment, phone call tasks are automatically created for follow-up.
                </p>
            </div>
        </div>
    </div>
@endsection

