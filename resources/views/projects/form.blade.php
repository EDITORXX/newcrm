@extends('layouts.app')

@section('title', ($project ? 'Edit Project' : 'Create Project') . ' - Base CRM')
@section('page-title', $project ? 'Edit Project' : 'Create Project')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ $project ? route('projects.update', $project) : route('projects.store') }}" enctype="multipart/form-data">
            @csrf
            @if($project)
                @method('PUT')
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Basic Info Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="min-w-0">
                        <label for="builder_id" class="block text-sm font-medium text-gray-700 mb-2">Builder <span class="text-red-500">*</span></label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <select name="builder_id" id="builder_id" required class="flex-1 min-w-0 px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="loadBuilderContacts(this.value)">
                                <option value="">Select Builder</option>
                                @foreach($builders as $builderOption)
                                    <option value="{{ $builderOption->id }}" {{ old('builder_id', $project ? $project->builder_id : (session('selected_builder_id') == $builderOption->id ? 'selected' : '')) == $builderOption->id ? 'selected' : '' }}>
                                        {{ $builderOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                                <div class="flex gap-2 flex-shrink-0">
                                    <a href="{{ route('builders.create', ['return_to' => 'project_form']) }}" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 whitespace-nowrap inline-flex items-center text-sm">
                                        <i class="fas fa-plus mr-1"></i> Create Builder
                                    </a>
                                    @if($project && $project->builder)
                                        <a href="{{ route('builders.edit', $project->builder) }}" target="_blank" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap inline-flex items-center text-sm">
                                            <i class="fas fa-edit mr-1"></i> Edit Builder
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="min-w-0">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $project ? $project->name : '') }}" required
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">Project Logo</label>
                    <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg">
                    @if($project && $project->logo)
                        <div class="mt-2">
                            <img src="{{ $project->logo_url }}" alt="Current logo" class="h-20 w-20 rounded object-cover">
                        </div>
                    @endif
                    <p class="mt-1 text-sm text-gray-500">Max 2MB. Formats: JPG, PNG, WebP</p>
                </div>

                <div class="mb-4">
                    <label for="short_overview" class="block text-sm font-medium text-gray-700 mb-2">Short Overview</label>
                    <textarea name="short_overview" id="short_overview" rows="3" maxlength="2000"
                              class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('short_overview', $project ? $project->short_overview : '') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Brief description of the project</p>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="project_type" class="block text-sm font-medium text-gray-700 mb-2">Project Type <span class="text-red-500">*</span></label>
                        <select name="project_type" id="project_type" required onchange="toggleResidentialSubType()" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="residential" {{ old('project_type', $project ? $project->project_type : '') === 'residential' ? 'selected' : '' }}>Residential</option>
                            <option value="commercial" {{ old('project_type', $project ? $project->project_type : '') === 'commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="mixed" {{ old('project_type', $project ? $project->project_type : '') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                        </select>
                    </div>

                    <div id="residential_sub_type_container" style="display: {{ old('project_type', $project ? $project->project_type : '') === 'residential' ? 'block' : 'none' }};">
                        <label for="residential_sub_type" class="block text-sm font-medium text-gray-700 mb-2">Residential Sub-Type</label>
                        <select name="residential_sub_type" id="residential_sub_type" onchange="toggleTowersSection()" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Type</option>
                            <option value="plot" {{ old('residential_sub_type', $project ? $project->residential_sub_type : '') === 'plot' ? 'selected' : '' }}>Plot</option>
                            <option value="flat" {{ old('residential_sub_type', $project ? $project->residential_sub_type : '') === 'flat' ? 'selected' : '' }}>Flat</option>
                            <option value="villa" {{ old('residential_sub_type', $project ? $project->residential_sub_type : '') === 'villa' ? 'selected' : '' }}>Villa</option>
                        </select>
                    </div>

                    <div>
                        <label for="project_status" class="block text-sm font-medium text-gray-700 mb-2">Project Status <span class="text-red-500">*</span></label>
                        <select name="project_status" id="project_status" required class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="prelaunch" {{ old('project_status', $project ? $project->project_status : '') === 'prelaunch' ? 'selected' : '' }}>Prelaunch</option>
                            <option value="under_construction" {{ old('project_status', $project ? $project->project_status : '') === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            <option value="ready" {{ old('project_status', $project ? $project->project_status : '') === 'ready' ? 'selected' : '' }}>Ready</option>
                        </select>
                    </div>

                    <div>
                        <label for="availability_type" class="block text-sm font-medium text-gray-700 mb-2">Availability Type</label>
                        <select name="availability_type" id="availability_type" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="fresh" {{ old('availability_type', $project ? $project->availability_type : 'fresh') === 'fresh' ? 'selected' : '' }}>Fresh</option>
                            <option value="resale" {{ old('availability_type', $project ? $project->availability_type : 'fresh') === 'resale' ? 'selected' : '' }}>Resale</option>
                            <option value="both" {{ old('availability_type', $project ? $project->availability_type : 'fresh') === 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Location</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City <span class="text-red-500">*</span></label>
                        <input type="text" name="city" id="city" value="{{ old('city', $project ? $project->city : '') }}" required
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="area" class="block text-sm font-medium text-gray-700 mb-2">Area / Locality <span class="text-red-500">*</span></label>
                        <input type="text" name="area" id="area" value="{{ old('area', $project ? $project->area : '') }}" required
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Project Size Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Project Size</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="land_area" class="block text-sm font-medium text-gray-700 mb-2">Land Area</label>
                        <input type="number" step="0.01" name="land_area" id="land_area" value="{{ old('land_area', $project ? $project->land_area : '') }}"
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="land_area_unit" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                        <select name="land_area_unit" id="land_area_unit" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="sq_ft" {{ old('land_area_unit', $project ? $project->land_area_unit : 'sq_ft') === 'sq_ft' ? 'selected' : '' }}>Sq.ft</option>
                            <option value="acres" {{ old('land_area_unit', $project ? $project->land_area_unit : 'sq_ft') === 'acres' ? 'selected' : '' }}>Acres</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Optional Info Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Optional Information</h3>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="rera_no" class="block text-sm font-medium text-gray-700 mb-2">RERA Number</label>
                        <input type="text" name="rera_no" id="rera_no" value="{{ old('rera_no', $project ? $project->rera_no : '') }}"
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="possession_date" class="block text-sm font-medium text-gray-700 mb-2">Possession Date</label>
                        <input type="date" name="possession_date" id="possession_date" value="{{ old('possession_date', $project ? $project->possession_date?->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="project_highlights" class="block text-sm font-medium text-gray-700 mb-2">Project Highlights / USP</label>
                    <textarea name="project_highlights" id="project_highlights" rows="4"
                              class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('project_highlights', $project ? $project->project_highlights : '') }}</textarea>
                </div>
            </div>

            <!-- Unit Types Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Unit Types & Pricing</h3>
                
                <!-- BSP Input Section -->
                @php
                    $existingBSP = $project ? $project->pricingConfig?->bsp_per_sqft : null;
                    $existingRoundingRule = $project ? $project->pricingConfig?->price_rounding_rule : 'none';
                @endphp
                
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="bsp_per_sqft" class="block text-sm font-medium text-gray-700 mb-2">
                                BSP (₹ / sq.ft) 
                                @if(!$existingBSP)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <input type="number" step="0.01" id="bsp_per_sqft" name="bsp_per_sqft" 
                                   value="{{ old('bsp_per_sqft', $existingBSP) }}"
                                   @if(!$existingBSP) required @endif
                                   onchange="updateAllPrices()"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @if($existingBSP)
                                <p class="text-xs text-gray-500 mt-1">Current: ₹{{ number_format($existingBSP, 2) }} / sq.ft</p>
                            @endif
                        </div>
                        <div>
                            <label for="price_rounding_rule" class="block text-sm font-medium text-gray-700 mb-2">Price Rounding Rule</label>
                            <select id="price_rounding_rule" name="price_rounding_rule" 
                                    onchange="updateAllPrices()"
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="none" {{ old('price_rounding_rule', $existingRoundingRule) === 'none' ? 'selected' : '' }}>None</option>
                                <option value="nearest_1000" {{ old('price_rounding_rule', $existingRoundingRule) === 'nearest_1000' ? 'selected' : '' }}>Nearest 1,000</option>
                                <option value="nearest_10000" {{ old('price_rounding_rule', $existingRoundingRule) === 'nearest_10000' ? 'selected' : '' }}>Nearest 10,000</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            @if(!$existingBSP)
                                <p class="text-sm text-gray-600">Set BSP to calculate prices</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Unit Types Table (Only for non-Flat projects) -->
                <div id="unit-types-section" class="mb-4" style="display: {{ old('residential_sub_type', $project ? $project->residential_sub_type : '') === 'flat' ? 'none' : 'block' }};">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-md font-medium text-gray-700">Unit Types</h4>
                        <button type="button" onclick="addUnitTypeRow()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Unit Type
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area (sq.ft)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody id="unit-types-tbody" class="bg-white divide-y divide-gray-200">
                                @if($project && $project->unitTypes->count() > 0)
                                    @foreach($project->unitTypes as $unitType)
                                        <tr class="unit-type-row" data-unit-type-id="{{ $unitType->id }}">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="text" name="unit_types[{{ $unitType->id }}][unit_type]" 
                                                       value="{{ old('unit_types.'.$unitType->id.'.unit_type', $unitType->unit_type) }}"
                                                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                       placeholder="e.g., 2BHK, 2BHK + Servant">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="number" step="0.01" name="unit_types[{{ $unitType->id }}][area_sqft]" 
                                                       value="{{ old('unit_types.'.$unitType->id.'.area_sqft', $unitType->area_sqft) }}"
                                                       onchange="calculatePrice(this)"
                                                       oninput="calculatePrice(this)"
                                                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                       placeholder="1200">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="unit-price-display text-sm font-medium text-gray-900">
                                                    {{ $unitType->formatted_price ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <button type="button" onclick="removeUnitTypeRow(this)" class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <!-- Empty row for new projects -->
                                    <tr class="unit-type-row">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="text" name="unit_types[new_0][unit_type]" 
                                                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="e.g., 2BHK, 2BHK + Servant">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="number" step="0.01" name="unit_types[new_0][area_sqft]" 
                                                   onchange="calculatePrice(this)"
                                                   oninput="calculatePrice(this)"
                                                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="1200">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="unit-price-display text-sm font-medium text-gray-900">—</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <button type="button" onclick="removeUnitTypeRow(this)" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Towers Section (Only for Flats) -->
            <div id="towers-section" class="mb-8" style="display: {{ old('residential_sub_type', $project ? $project->residential_sub_type : '') === 'flat' ? 'block' : 'none' }};">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Towers</h3>
                
                <div id="towers-container">
                    @if($project && $project->towers->count() > 0)
                        @foreach($project->towers as $towerIndex => $tower)
                            @include('projects.partials.tower-row', ['tower' => $tower, 'towerIndex' => $tower->id, 'project' => $project])
                        @endforeach
                    @endif
                </div>
                
                <button type="button" onclick="addTowerRow()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Tower
                </button>
            </div>

            <!-- Collaterals Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Collaterals</h3>
                
                <div class="mb-4">
                    <button type="button" onclick="addCollateralRow()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm mb-4">
                        <i class="fas fa-plus mr-1"></i> Add Collateral
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Link</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Is Latest</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-20">Action</th>
                            </tr>
                        </thead>
                        <tbody id="collaterals-tbody" class="bg-white divide-y divide-gray-200">
                            @if($project && $project->collaterals->count() > 0)
                                @foreach($project->collaterals as $collateral)
                                    <tr class="collateral-row" data-collateral-id="{{ $collateral->id }}">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <select name="collaterals[{{ $collateral->id }}][category]" required
                                                    class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="brochure" {{ $collateral->category === 'brochure' ? 'selected' : '' }}>Brochure</option>
                                                <option value="floor_plans" {{ $collateral->category === 'floor_plans' ? 'selected' : '' }}>Floor Plans</option>
                                                <option value="layout_plan" {{ $collateral->category === 'layout_plan' ? 'selected' : '' }}>Layout Plan</option>
                                                <option value="price_sheet" {{ $collateral->category === 'price_sheet' ? 'selected' : '' }}>Price Sheet</option>
                                                <option value="videos" {{ $collateral->category === 'videos' ? 'selected' : '' }}>Videos</option>
                                                <option value="legal_approvals" {{ $collateral->category === 'legal_approvals' ? 'selected' : '' }}>Legal/RERA/Approvals</option>
                                                <option value="other" {{ $collateral->category === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="text" name="collaterals[{{ $collateral->id }}][title]" 
                                                   value="{{ old('collaterals.'.$collateral->id.'.title', $collateral->title) }}"
                                                   required
                                                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="Collateral Title">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="url" name="collaterals[{{ $collateral->id }}][link]" 
                                                   value="{{ old('collaterals.'.$collateral->id.'.link', $collateral->link) }}"
                                                   required
                                                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="Google Drive or YouTube Link">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <input type="checkbox" name="collaterals[{{ $collateral->id }}][is_latest]" value="1"
                                                   {{ $collateral->is_latest ? 'checked' : '' }}
                                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <button type="button" onclick="removeCollateralRow(this)" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Project Contacts Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Project Contacts</h3>
                <div id="builder-contacts-container" class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="contacts_primary" class="block text-sm font-medium text-gray-700 mb-2">Primary Contact <span class="text-red-500">*</span></label>
                        <select name="contacts[primary]" id="contacts_primary" required class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Primary Contact</option>
                        </select>
                    </div>
                    <div>
                        <label for="contacts_secondary" class="block text-sm font-medium text-gray-700 mb-2">Secondary Contact</label>
                        <select name="contacts[secondary]" id="contacts_secondary" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Secondary Contact</option>
                        </select>
                    </div>
                    <div>
                        <label for="contacts_escalation" class="block text-sm font-medium text-gray-700 mb-2">Escalation Contact</label>
                        <select name="contacts[escalation]" id="contacts_escalation" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Escalation Contact</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('projects.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    {{ $project ? 'Update' : 'Create' }} Project
                </button>
            </div>
        </form>
    </div>
</div>

@php
$buildersData = $builders->map(function($b) {
    $contacts = $b->activeContacts->map(function($c) {
        return [
            'id' => $c->id,
            'person_name' => $c->person_name,
            'mobile_number' => $c->mobile_number
        ];
    })->values()->toArray();
    
    return [
        'id' => $b->id,
        'name' => $b->name,
        'contacts' => $contacts
    ];
})->values()->toArray();
@endphp

<script>
const builders = @json($buildersData);

function loadBuilderContacts(builderId) {
    const builder = builders.find(b => b.id == builderId);
    const primarySelect = document.getElementById('contacts_primary');
    const secondarySelect = document.getElementById('contacts_secondary');
    const escalationSelect = document.getElementById('contacts_escalation');
    
    [primarySelect, secondarySelect, escalationSelect].forEach(select => {
        select.innerHTML = '<option value="">Select Contact</option>';
    });
    
    if (builder && builder.contacts) {
        builder.contacts.forEach(contact => {
            const option = `<option value="${contact.id}">${contact.person_name} (${contact.mobile_number})</option>`;
            primarySelect.innerHTML += option;
            secondarySelect.innerHTML += option;
            escalationSelect.innerHTML += option;
        });
    }
}

// Load contacts if builder is pre-selected
@if($project)
    loadBuilderContacts({{ $project->builder_id }});
    @php
        $primaryContact = $project->primaryContact();
        $secondaryContact = $project->secondaryContact();
        $escalationContact = $project->escalationContact();
    @endphp
    @if($primaryContact)
        document.getElementById('contacts_primary').value = {{ $primaryContact->builder_contact_id }};
    @endif
    @if($secondaryContact)
        document.getElementById('contacts_secondary').value = {{ $secondaryContact->builder_contact_id }};
    @endif
    @if($escalationContact)
        document.getElementById('contacts_escalation').value = {{ $escalationContact->builder_contact_id }};
    @endif
@elseif(session('selected_builder_id'))
    // Load contacts for newly created builder
    loadBuilderContacts({{ session('selected_builder_id') }});
@endif
</script>


<!-- Unit Types JavaScript -->
<script>
let unitTypeNewIndex = {{ $project && $project->unitTypes->count() > 0 ? $project->unitTypes->count() : 1 }};

function addUnitTypeRow() {
    const tbody = document.getElementById('unit-types-tbody');
    const row = document.createElement('tr');
    row.className = 'unit-type-row';
    
    const index = 'new_' + unitTypeNewIndex++;
    row.innerHTML = `
        <td class="px-4 py-3 whitespace-nowrap">
            <input type="text" name="unit_types[${index}][unit_type]" 
                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="e.g., 2BHK, 2BHK + Servant">
        </td>
        <td class="px-4 py-3 whitespace-nowrap">
            <input type="number" step="0.01" name="unit_types[${index}][area_sqft]" 
                   onchange="calculatePrice(this)"
                   oninput="calculatePrice(this)"
                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="1200">
        </td>
        <td class="px-4 py-3 whitespace-nowrap">
            <span class="unit-price-display text-sm font-medium text-gray-900">—</span>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-center">
            <button type="button" onclick="removeUnitTypeRow(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeUnitTypeRow(button) {
    const row = button.closest('tr');
    if (document.querySelectorAll('.unit-type-row').length > 1) {
        row.remove();
    } else {
        alert('At least one unit type is required.');
    }
}

function calculatePrice(input) {
    const row = input.closest('tr');
    const areaInput = row.querySelector('input[name*="[area_sqft]"]');
    const priceDisplay = row.querySelector('.unit-price-display');
    
    const area = parseFloat(areaInput.value) || 0;
    const bsp = parseFloat(document.getElementById('bsp_per_sqft').value) || 0;
    const roundingRule = document.getElementById('price_rounding_rule').value;
    
    if (!bsp || area <= 0) {
        priceDisplay.textContent = '—';
        return;
    }
    
    let price = area * bsp;
    
    // Apply rounding rule
    if (roundingRule === 'nearest_1000') {
        price = Math.round(price / 1000) * 1000;
    } else if (roundingRule === 'nearest_10000') {
        price = Math.round(price / 10000) * 10000;
    }
    
    // Format price in Indian currency
    priceDisplay.textContent = formatIndianCurrency(price);
}

function updateAllPrices() {
    const rows = document.querySelectorAll('.unit-type-row');
    rows.forEach(row => {
        const areaInput = row.querySelector('input[name*="[area_sqft]"]');
        if (areaInput) {
            calculatePrice(areaInput);
        }
    });
}

function formatIndianCurrency(price) {
    if (price >= 10000000) {
        return '₹' + (price / 10000000).toFixed(2) + ' Cr';
    } else if (price >= 100000) {
        return '₹' + (price / 100000).toFixed(2) + ' L';
    }
    return '₹' + Math.round(price).toLocaleString('en-IN');
}

// Initialize prices on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAllPrices();
    toggleResidentialSubType();
    toggleTowersSection();
});

// Tower Management Functions
let towerNewIndex = {{ $project && $project->towers->count() > 0 ? $project->towers->max('id') + 1 : 1 }};

function toggleResidentialSubType() {
    const projectType = document.getElementById('project_type').value;
    const container = document.getElementById('residential_sub_type_container');
    if (projectType === 'residential') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        document.getElementById('residential_sub_type').value = '';
        toggleTowersSection();
    }
}

function toggleTowersSection() {
    const residentialSubType = document.getElementById('residential_sub_type').value;
    const towersSection = document.getElementById('towers-section');
    const unitTypesSection = document.getElementById('unit-types-section');
    
    if (residentialSubType === 'flat') {
        // Show towers, hide direct unit types
        towersSection.style.display = 'block';
        if (unitTypesSection) {
            unitTypesSection.style.display = 'none';
        }
    } else {
        // Hide towers, show direct unit types
        towersSection.style.display = 'none';
        if (unitTypesSection) {
            unitTypesSection.style.display = 'block';
        }
    }
}

function addTowerRow() {
    const container = document.getElementById('towers-container');
    const index = 'new_' + towerNewIndex++;
    
    const towerRow = document.createElement('div');
    towerRow.className = 'tower-row mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50';
    towerRow.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-semibold text-gray-800">New Tower</h4>
            <button type="button" onclick="removeTowerRow(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i> Remove Tower
            </button>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tower Name <span class="text-red-500">*</span></label>
                <input type="text" name="towers[${index}][tower_name]" required
                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="e.g., Tower A, Tower 1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tower Number (Optional)</label>
                <input type="number" name="towers[${index}][tower_number]"
                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="1, 2, 3...">
            </div>
        </div>
        <div class="mb-4">
            <div class="flex justify-between items-center mb-3">
                <h5 class="text-md font-medium text-gray-700">Unit Types</h5>
                <button type="button" onclick="addTowerUnitType(this)" class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    <i class="fas fa-plus mr-1"></i> Add Unit Type
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Area (sq.ft)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-20">Action</th>
                        </tr>
                    </thead>
                    <tbody class="tower-unit-types-tbody bg-white divide-y divide-gray-200">
                        <tr class="tower-unit-type-row">
                            <td class="px-4 py-2 whitespace-nowrap" colspan="4" class="text-center text-gray-500">
                                No unit types. Click "Add Unit Type" to add units or this tower will show "Coming Soon".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    container.appendChild(towerRow);
}

function removeTowerRow(button) {
    const row = button.closest('.tower-row');
    row.remove();
}

function addTowerUnitType(button) {
    const towerRow = button.closest('.tower-row');
    const tbody = towerRow.querySelector('.tower-unit-types-tbody');
    
    // Remove "no units" message if exists
    const noUnitsRow = tbody.querySelector('tr td[colspan="4"]');
    if (noUnitsRow) {
        noUnitsRow.closest('tr').remove();
    }
    
    // Get tower index from input name
    const towerInput = towerRow.querySelector('input[name*="[tower_name]"]');
    const towerName = towerInput.name.match(/towers\[([^\]]+)\]/)[1];
    const unitIndex = 'new_' + Date.now();
    
    const row = document.createElement('tr');
    row.className = 'tower-unit-type-row';
    row.innerHTML = `
        <td class="px-4 py-2 whitespace-nowrap">
            <input type="text" name="towers[${towerName}][unit_types][${unitIndex}][unit_type]" 
                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="e.g., 2BHK">
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
            <input type="number" step="0.01" name="towers[${towerName}][unit_types][${unitIndex}][area_sqft]" 
                   onchange="calculateTowerUnitPrice(this)"
                   oninput="calculateTowerUnitPrice(this)"
                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="1200">
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
            <span class="tower-unit-price-display text-sm font-medium text-gray-900">—</span>
        </td>
        <td class="px-4 py-2 whitespace-nowrap text-center">
            <button type="button" onclick="removeTowerUnitTypeRow(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeTowerUnitTypeRow(button) {
    const row = button.closest('tr');
    const tbody = row.closest('tbody');
    row.remove();
    
    // Show "no units" message if tbody is empty
    if (tbody.querySelectorAll('tr').length === 0) {
        const noUnitsRow = document.createElement('tr');
        noUnitsRow.className = 'tower-unit-type-row';
        noUnitsRow.innerHTML = `
            <td class="px-4 py-2 whitespace-nowrap text-center text-gray-500" colspan="4">
                No unit types. Click "Add Unit Type" to add units or this tower will show "Coming Soon".
            </td>
        `;
        tbody.appendChild(noUnitsRow);
    }
}

function calculateTowerUnitPrice(input) {
    const row = input.closest('tr');
    const areaInput = row.querySelector('input[name*="[area_sqft]"]');
    const priceDisplay = row.querySelector('.tower-unit-price-display');
    
    const area = parseFloat(areaInput.value) || 0;
    const bsp = parseFloat(document.getElementById('bsp_per_sqft').value) || 0;
    const roundingRule = document.getElementById('price_rounding_rule').value;
    
    if (!bsp || area <= 0) {
        priceDisplay.textContent = '—';
        return;
    }
    
    let price = area * bsp;
    
    // Apply rounding rule
    if (roundingRule === 'nearest_1000') {
        price = Math.round(price / 1000) * 1000;
    } else if (roundingRule === 'nearest_10000') {
        price = Math.round(price / 10000) * 10000;
    }
    
    // Format price in Indian currency
    priceDisplay.textContent = formatIndianCurrency(price);
}

// Collaterals JavaScript
let collateralNewIndex = {{ $project && $project->collaterals->count() > 0 ? $project->collaterals->count() : 1 }};

function addCollateralRow() {
    const tbody = document.getElementById('collaterals-tbody');
    const row = document.createElement('tr');
    row.className = 'collateral-row';
    
    const index = 'new_' + collateralNewIndex++;
    row.innerHTML = `
        <td class="px-4 py-3 whitespace-nowrap">
            <select name="collaterals[${index}][category]" required
                    class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Category</option>
                <option value="brochure">Brochure</option>
                <option value="floor_plans">Floor Plans</option>
                <option value="layout_plan">Layout Plan</option>
                <option value="price_sheet">Price Sheet</option>
                <option value="videos">Videos</option>
                <option value="legal_approvals">Legal/RERA/Approvals</option>
                <option value="other">Other</option>
            </select>
        </td>
        <td class="px-4 py-3 whitespace-nowrap">
            <input type="text" name="collaterals[${index}][title]" 
                   required
                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Collateral Title">
        </td>
        <td class="px-4 py-3 whitespace-nowrap">
            <input type="url" name="collaterals[${index}][link]" 
                   required
                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Google Drive or YouTube Link">
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-center">
            <input type="checkbox" name="collaterals[${index}][is_latest]" value="1"
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-center">
            <button type="button" onclick="removeCollateralRow(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeCollateralRow(button) {
    const row = button.closest('tr');
    row.remove();
}

// Tower Copy Units JavaScript
function toggleCopyUnitsDropdown(button) {
    const dropdown = button.nextElementSibling;
    const isHidden = dropdown.classList.contains('hidden');
    
    // Close all other dropdowns
    document.querySelectorAll('.copy-units-dropdown').forEach(d => {
        if (d !== dropdown) {
            d.classList.add('hidden');
        }
    });
    
    if (isHidden) {
        dropdown.classList.remove('hidden');
        populateCopyUnitsDropdown(button);
    } else {
        dropdown.classList.add('hidden');
    }
}

function populateCopyUnitsDropdown(button) {
    const dropdown = button.nextElementSibling;
    const optionsDiv = dropdown.querySelector('.copy-units-options');
    const currentTowerRow = button.closest('.tower-row');
    const currentTowerInput = currentTowerRow.querySelector('input[name*="[tower_name]"]');
    const currentTowerName = currentTowerInput ? currentTowerInput.name.match(/towers\[([^\]]+)\]/)[1] : null;
    
    // Get all towers except current one
    const allTowerRows = document.querySelectorAll('.tower-row');
    optionsDiv.innerHTML = '';
    
    if (allTowerRows.length <= 1) {
        optionsDiv.innerHTML = '<div class="p-2 text-xs text-gray-500">No other towers available</div>';
        return;
    }
    
    allTowerRows.forEach(towerRow => {
        const towerInput = towerRow.querySelector('input[name*="[tower_name]"]');
        if (!towerInput) return;
        
        const towerName = towerInput.name.match(/towers\[([^\]]+)\]/)[1];
        if (towerName === currentTowerName) return; // Skip current tower
        
        const towerDisplayName = towerInput.value || `Tower ${towerName}`;
        const unitTypesTbody = towerRow.querySelector('.tower-unit-types-tbody');
        const unitRows = unitTypesTbody ? unitTypesTbody.querySelectorAll('.tower-unit-type-row:not([colspan])') : [];
        
        if (unitRows.length === 0) {
            return; // Skip towers with no units
        }
        
        const option = document.createElement('div');
        option.className = 'p-2 hover:bg-gray-100 cursor-pointer';
        option.innerHTML = `<div class="text-sm font-medium">${towerDisplayName}</div><div class="text-xs text-gray-500">${unitRows.length} unit(s)</div>`;
        option.onclick = () => {
            copyUnitsFromTower(towerName, currentTowerName);
            dropdown.classList.add('hidden');
        };
        optionsDiv.appendChild(option);
    });
    
    if (optionsDiv.children.length === 0) {
        optionsDiv.innerHTML = '<div class="p-2 text-xs text-gray-500">No towers with units available</div>';
    }
}

function copyUnitsFromTower(sourceTowerName, targetTowerName) {
    // Find source tower row
    const sourceTowerRow = Array.from(document.querySelectorAll('.tower-row')).find(row => {
        const input = row.querySelector('input[name*="[tower_name]"]');
        return input && input.name.match(/towers\[([^\]]+)\]/)[1] === sourceTowerName;
    });
    
    if (!sourceTowerRow) {
        alert('Source tower not found');
        return;
    }
    
    // Find target tower row
    const targetTowerRow = Array.from(document.querySelectorAll('.tower-row')).find(row => {
        const input = row.querySelector('input[name*="[tower_name]"]');
        return input && input.name.match(/towers\[([^\]]+)\]/)[1] === targetTowerName;
    });
    
    if (!targetTowerRow) {
        alert('Target tower not found');
        return;
    }
    
    // Get source unit types
    const sourceTbody = sourceTowerRow.querySelector('.tower-unit-types-tbody');
    const sourceUnitRows = sourceTbody ? sourceTbody.querySelectorAll('.tower-unit-type-row:not([colspan])') : [];
    
    if (sourceUnitRows.length === 0) {
        alert('Source tower has no units to copy');
        return;
    }
    
    // Get target tbody
    const targetTbody = targetTowerRow.querySelector('.tower-unit-types-tbody');
    if (!targetTbody) {
        alert('Target tower tbody not found');
        return;
    }
    
    // Remove "no units" message if exists
    const noUnitsRow = targetTbody.querySelector('tr td[colspan]');
    if (noUnitsRow) {
        noUnitsRow.closest('tr').remove();
    }
    
    // Copy each unit
    sourceUnitRows.forEach(sourceRow => {
        const unitTypeInput = sourceRow.querySelector('input[name*="[unit_type]"]');
        const areaInput = sourceRow.querySelector('input[name*="[area_sqft]"]');
        
        if (!unitTypeInput || !areaInput) return;
        
        const unitType = unitTypeInput.value;
        const area = areaInput.value;
        
        // Create new row in target tower
        const unitIndex = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const newRow = document.createElement('tr');
        newRow.className = 'tower-unit-type-row';
        newRow.innerHTML = `
            <td class="px-4 py-2 whitespace-nowrap">
                <input type="text" name="towers[${targetTowerName}][unit_types][${unitIndex}][unit_type]" 
                       value="${unitType}"
                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="e.g., 2BHK">
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
                <input type="number" step="0.01" name="towers[${targetTowerName}][unit_types][${unitIndex}][area_sqft]" 
                       value="${area}"
                       onchange="calculateTowerUnitPrice(this)"
                       oninput="calculateTowerUnitPrice(this)"
                       class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="1200">
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
                <span class="tower-unit-price-display text-sm font-medium text-gray-900">—</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap text-center">
                <button type="button" onclick="removeTowerUnitTypeRow(this)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        targetTbody.appendChild(newRow);
        
        // Calculate price if BSP is set
        const areaInputNew = newRow.querySelector('input[name*="[area_sqft]"]');
        if (areaInputNew) {
            calculateTowerUnitPrice(areaInputNew);
        }
    });
    
    // Show success message
    const successMsg = document.createElement('div');
    successMsg.className = 'mt-2 p-2 bg-green-100 text-green-800 rounded text-sm';
    successMsg.textContent = `Copied ${sourceUnitRows.length} unit(s) successfully!`;
    targetTowerRow.appendChild(successMsg);
    setTimeout(() => successMsg.remove(), 3000);
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.copy-units-dropdown') && !event.target.closest('button[onclick*="toggleCopyUnitsDropdown"]')) {
        document.querySelectorAll('.copy-units-dropdown').forEach(d => d.classList.add('hidden'));
    }
});
</script>
@endsection
