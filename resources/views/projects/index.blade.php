@extends('layouts.app')

@section('title', 'Projects - Base CRM')
@section('page-title', 'Projects')

@section('header-actions')
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
        <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
            Create Project
        </a>
    @endif
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Builder</label>
                <select name="builder_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Builders</option>
                    @foreach($builders as $builder)
                        <option value="{{ $builder->id }}" {{ request('builder_id') == $builder->id ? 'selected' : '' }}>
                            {{ $builder->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Type</label>
                <select name="project_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="residential" {{ request('project_type') == 'residential' ? 'selected' : '' }}>Residential</option>
                    <option value="commercial" {{ request('project_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="mixed" {{ request('project_type') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="project_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="prelaunch" {{ request('project_status') == 'prelaunch' ? 'selected' : '' }}>Prelaunch</option>
                    <option value="under_construction" {{ request('project_status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                    <option value="ready" {{ request('project_status') == 'ready' ? 'selected' : '' }}>Ready</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Projects Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <!-- Card Header -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $project->name }}</h3>
                            @if($project->builder)
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-building mr-1"></i>
                                    {{ $project->builder->name }}
                                </p>
                            @endif
                        </div>
                        @if($project->builder && $project->builder->logo)
                            <img src="{{ $project->builder->logo_url }}" alt="{{ $project->builder->name }}" class="h-12 w-12 rounded-lg object-cover ml-3">
                        @endif
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-6">
                    <!-- Location -->
                    @if($project->city || $project->area)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2 text-indigo-500"></i>
                                @if($project->city && $project->area)
                                    {{ $project->city }}, {{ $project->area }}
                                @else
                                    {{ $project->city ?: $project->area }}
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Project Details -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Type</p>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $project->project_type ? ucfirst($project->project_type) : 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <span class="px-2 py-1 text-xs rounded-full {{ $project->project_status === 'ready' ? 'bg-green-100 text-green-800' : ($project->project_status === 'under_construction' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $project->project_status ? ucfirst(str_replace('_', ' ', $project->project_status)) : 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <!-- Starting Price -->
                    @php
                        $startingUnit = $project->startingFromUnit();
                    @endphp
                    @if($startingUnit && $startingUnit->calculated_price)
                        <div class="mb-4 p-3 bg-indigo-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Starting From</p>
                            <p class="text-lg font-bold text-indigo-600">{{ $startingUnit->formatted_price }}</p>
                        </div>
                    @endif

                    <!-- Project Highlights (if available) -->
                    @if($project->project_highlights)
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 mb-1">Highlights</p>
                            <p class="text-sm text-gray-700 line-clamp-2">{{ Str::limit($project->project_highlights, 100) }}</p>
                        </div>
                    @endif
                </div>

                <!-- Card Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                        View Details <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('projects.edit', $project) }}" class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <i class="fas fa-project-diagram text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500 text-lg mb-2">No projects found</p>
                    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                        <a href="{{ route('projects.create') }}" class="inline-block mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Create Your First Project
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $projects->links() }}
    </div>
@endsection
