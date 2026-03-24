<?php

namespace App\Http\Controllers;

use App\Models\Builder;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\PricingService;
use App\Services\CollateralService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $projectService;
    protected $pricingService;
    protected $collateralService;

    public function __construct(
        ProjectService $projectService,
        PricingService $pricingService,
        CollateralService $collateralService
    ) {
        $this->middleware('auth');
        $this->projectService = $projectService;
        $this->pricingService = $pricingService;
        $this->collateralService = $collateralService;
    }

    public function index(Request $request)
    {
        // All authenticated users can view projects list
        $query = Project::query()->with(['builder', 'pricingConfig']);

        // Filter by builder
        if ($request->has('builder_id') && $request->builder_id) {
            $query->where('builder_id', $request->builder_id);
        }

        // Filter by project type
        if ($request->has('project_type') && $request->project_type) {
            $query->where('project_type', $request->project_type);
        }

        // Filter by status
        if ($request->has('project_status') && $request->project_status) {
            $query->where('project_status', $request->project_status);
        }

        // Filter by city
        if ($request->has('city') && $request->city) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $projects = $query->latest()->paginate(15);
        $builders = \App\Models\Builder::where('status', 'active')->get();

        return view('projects.index', compact('projects', 'builders'));
    }

    public function create()
    {
        $currentUser = request()->user();
        
        if (!$currentUser->isAdmin() && !$currentUser->isCrm()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all active builders, or include the selected one even if inactive
        $builders = Builder::where('status', 'active')->with('activeContacts')->get();
        
        // If a builder was just created, make sure it's in the list
        if (session('selected_builder_id')) {
            $selectedBuilder = Builder::with('activeContacts')->find(session('selected_builder_id'));
            if ($selectedBuilder && !$builders->contains('id', $selectedBuilder->id)) {
                $builders->push($selectedBuilder);
            }
        }

        return view('projects.form', ['project' => null, 'builders' => $builders]);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        
        if (!$currentUser->isAdmin() && !$currentUser->isCrm()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'builder_id' => 'required|exists:builders,id',
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'short_overview' => 'nullable|string|max:2000',
            'project_type' => 'required|in:residential,commercial,mixed',
            'residential_sub_type' => 'nullable|in:plot,flat,villa',
            'project_status' => 'required|in:prelaunch,under_construction,ready',
            'availability_type' => 'nullable|in:fresh,resale,both',
            'city' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'land_area' => 'nullable|numeric|min:0',
            'land_area_unit' => 'nullable|in:acres,sq_ft',
            'rera_no' => 'nullable|string|max:255',
            'possession_date' => 'nullable|date',
            'project_highlights' => 'nullable|string',
            'configuration_summary' => 'nullable|array',
            'configuration_summary.*' => 'in:studio,1bhk,2bhk,3bhk,4bhk,other',
            'contacts.primary' => 'required|exists:builder_contacts,id',
            'contacts.secondary' => 'nullable|exists:builder_contacts,id',
            'contacts.escalation' => 'nullable|exists:builder_contacts,id',
            'bsp_per_sqft' => 'nullable|numeric|min:0',
            'price_rounding_rule' => 'nullable|in:none,nearest_1000,nearest_10000',
            'unit_types' => 'nullable|array',
            'unit_types.*.unit_type' => 'required_with:unit_types|string|max:255',
            'unit_types.*.area_sqft' => 'required_with:unit_types|numeric|min:0',
            'towers' => 'nullable|array',
            'towers.*.tower_name' => 'required_with:towers|string|max:255',
            'towers.*.tower_number' => 'nullable|integer',
            'towers.*.unit_types' => 'nullable|array',
            'towers.*.unit_types.*.unit_type' => 'required_with:towers.*.unit_types|string|max:255',
            'towers.*.unit_types.*.area_sqft' => 'required_with:towers.*.unit_types|numeric|min:0',
            'collaterals' => 'nullable|array',
            'collaterals.*.category' => 'required_with:collaterals|in:brochure,floor_plans,layout_plan,price_sheet,videos,legal_approvals,other',
            'collaterals.*.title' => 'required_with:collaterals|string|max:255',
            'collaterals.*.link' => 'required_with:collaterals|url|max:500',
            'collaterals.*.is_latest' => 'nullable|boolean',
        ]);

        $contactIds = $validated['contacts'] ?? null;
        unset($validated['contacts']);

        $logo = $request->hasFile('logo') ? $request->file('logo') : null;
        unset($validated['logo']);

        $bspPerSqft = $validated['bsp_per_sqft'] ?? null;
        $roundingRule = $validated['price_rounding_rule'] ?? 'none';
        unset($validated['bsp_per_sqft'], $validated['price_rounding_rule']);

        $unitTypes = $validated['unit_types'] ?? [];
        unset($validated['unit_types']);

        $towers = $validated['towers'] ?? [];
        unset($validated['towers']);

        $collaterals = $validated['collaterals'] ?? [];
        unset($validated['collaterals']);

        $project = $this->projectService->createProject($validated, $contactIds, $logo);

        // Set BSP if provided
        if ($bspPerSqft) {
            $this->pricingService->setBSP($project, $bspPerSqft, $roundingRule);
        }

        // Save unit types
        if (!empty($unitTypes)) {
            foreach ($unitTypes as $key => $unitTypeData) {
                if (isset($unitTypeData['unit_type']) && isset($unitTypeData['area_sqft'])) {
                    $unitType = $project->unitTypes()->create([
                        'unit_type' => $unitTypeData['unit_type'],
                        'area_sqft' => $unitTypeData['area_sqft'],
                    ]);

                    // Calculate price if BSP is set
                    if ($bspPerSqft) {
                        $this->pricingService->calculateUnitPrice($unitType, $bspPerSqft);
                        $unitType->save();
                    }
                }
            }

            // Mark starting from if BSP is set
            if ($bspPerSqft) {
                $this->pricingService->markStartingFrom($project);
            }
        }

        // Handle towers (for flats)
        if (!empty($towers)) {
            foreach ($towers as $towerData) {
                if (isset($towerData['tower_name'])) {
                    $tower = $this->projectService->createTower($project, [
                        'tower_name' => $towerData['tower_name'],
                        'tower_number' => $towerData['tower_number'] ?? null,
                    ]);

                    // Add unit types to tower
                    if (isset($towerData['unit_types']) && !empty($towerData['unit_types'])) {
                        foreach ($towerData['unit_types'] as $unitTypeData) {
                            if (isset($unitTypeData['unit_type']) && isset($unitTypeData['area_sqft'])) {
                                $unitType = $tower->unitTypes()->create([
                                    'project_id' => $project->id,
                                    'unit_type' => $unitTypeData['unit_type'],
                                    'area_sqft' => $unitTypeData['area_sqft'],
                                ]);

                                // Calculate price if BSP is set
                                if ($bspPerSqft) {
                                    $this->pricingService->calculateUnitPrice($unitType, $bspPerSqft);
                                    $unitType->save();
                                }
                            }
                        }
                    }
                }
            }

            // Mark starting from if BSP is set
            if ($bspPerSqft) {
                $this->pricingService->markStartingFrom($project);
            }
        }

        // Handle collaterals
        if (!empty($collaterals)) {
            foreach ($collaterals as $collateralData) {
                if (isset($collateralData['category']) && isset($collateralData['title']) && isset($collateralData['link'])) {
                    $collateral = $project->collaterals()->create([
                        'category' => $collateralData['category'],
                        'title' => $collateralData['title'],
                        'link' => $collateralData['link'],
                        'is_latest' => isset($collateralData['is_latest']) && $collateralData['is_latest'] == '1',
                    ]);
                    
                    // If this is marked as latest price sheet, unmark others
                    if ($collateralData['category'] === 'price_sheet' && isset($collateralData['is_latest']) && $collateralData['is_latest'] == '1') {
                        $this->collateralService->markLatestPriceSheet($project, $collateral);
                    }
                }
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        // All authenticated users can view project details
        $project->load([
            'builder',
            'projectContacts.builderContact',
            'pricingConfig',
            'unitTypes',
            'towers.unitTypes',
            'collaterals',
        ]);

        // Get collateral buttons
        $collateralButtons = $this->collateralService->generateButtonData($project);

        return view('projects.show', compact('project', 'collateralButtons'));
    }

    public function edit(Project $project)
    {
        $currentUser = request()->user();
        
        if (!$currentUser->isAdmin() && !$currentUser->isCrm()) {
            abort(403, 'Unauthorized action.');
        }

        $builders = Builder::where('status', 'active')->with('activeContacts')->get();
        $project->load(['projectContacts.builderContact', 'unitTypes', 'towers.unitTypes', 'pricingConfig']);

        return view('projects.form', ['project' => $project, 'builders' => $builders]);
    }

    public function update(Request $request, Project $project)
    {
        $currentUser = $request->user();
        
        if (!$currentUser->isAdmin() && !$currentUser->isCrm()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'builder_id' => 'sometimes|exists:builders,id',
            'name' => 'sometimes|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'short_overview' => 'nullable|string|max:2000',
            'project_type' => 'sometimes|in:residential,commercial,mixed',
            'residential_sub_type' => 'nullable|in:plot,flat,villa',
            'project_status' => 'sometimes|in:prelaunch,under_construction,ready',
            'availability_type' => 'sometimes|in:fresh,resale,both',
            'city' => 'sometimes|string|max:255',
            'area' => 'sometimes|string|max:255',
            'land_area' => 'nullable|numeric|min:0',
            'land_area_unit' => 'nullable|in:acres,sq_ft',
            'rera_no' => 'nullable|string|max:255',
            'possession_date' => 'nullable|date',
            'project_highlights' => 'nullable|string',
            'configuration_summary' => 'nullable|array',
            'configuration_summary.*' => 'in:studio,1bhk,2bhk,3bhk,4bhk,other',
            'contacts.primary' => 'required_with:contacts|exists:builder_contacts,id',
            'contacts.secondary' => 'nullable|exists:builder_contacts,id',
            'contacts.escalation' => 'nullable|exists:builder_contacts,id',
            'bsp_per_sqft' => 'nullable|numeric|min:0',
            'price_rounding_rule' => 'nullable|in:none,nearest_1000,nearest_10000',
            'unit_types' => 'nullable|array',
            'unit_types.*.unit_type' => 'required_with:unit_types|string|max:255',
            'unit_types.*.area_sqft' => 'required_with:unit_types|numeric|min:0',
            'towers' => 'nullable|array',
            'towers.*.tower_name' => 'required_with:towers|string|max:255',
            'towers.*.tower_number' => 'nullable|integer',
            'towers.*.unit_types' => 'nullable|array',
            'towers.*.unit_types.*.unit_type' => 'required_with:towers.*.unit_types|string|max:255',
            'towers.*.unit_types.*.area_sqft' => 'required_with:towers.*.unit_types|numeric|min:0',
            'collaterals' => 'nullable|array',
            'collaterals.*.category' => 'required_with:collaterals|in:brochure,floor_plans,layout_plan,price_sheet,videos,legal_approvals,other',
            'collaterals.*.title' => 'required_with:collaterals|string|max:255',
            'collaterals.*.link' => 'required_with:collaterals|url|max:500',
            'collaterals.*.is_latest' => 'nullable|boolean',
        ]);

        $contactIds = $validated['contacts'] ?? null;
        unset($validated['contacts']);

        $logo = $request->hasFile('logo') ? $request->file('logo') : null;
        unset($validated['logo']);

        $bspPerSqft = $validated['bsp_per_sqft'] ?? null;
        $roundingRule = $validated['price_rounding_rule'] ?? null;
        unset($validated['bsp_per_sqft'], $validated['price_rounding_rule']);

        $unitTypes = $validated['unit_types'] ?? [];
        unset($validated['unit_types']);

        $towers = $validated['towers'] ?? [];
        unset($validated['towers']);

        $collaterals = $validated['collaterals'] ?? [];
        unset($validated['collaterals']);

        $project = $this->projectService->updateProject($project, $validated, $contactIds, $logo);

        // Update BSP if provided
        if ($bspPerSqft !== null) {
            $finalRoundingRule = $roundingRule ?? ($project->pricingConfig?->price_rounding_rule ?? 'none');
            $this->pricingService->setBSP($project, $bspPerSqft, $finalRoundingRule);
        } elseif ($roundingRule !== null && $project->pricingConfig) {
            // Update only rounding rule if BSP not changed
            $project->pricingConfig->update(['price_rounding_rule' => $roundingRule]);
            $this->pricingService->recalculateAllPrices($project);
        }

        // Handle unit types
        if (!empty($unitTypes)) {
            $existingUnitTypeIds = [];
            $newlyCreatedUnitTypeIds = [];
            
            foreach ($unitTypes as $key => $unitTypeData) {
                if (isset($unitTypeData['unit_type']) && isset($unitTypeData['area_sqft'])) {
                    // Check if key is numeric (existing ID) or starts with 'new_' (new entry)
                    if (is_numeric($key)) {
                        // Update existing unit type
                        $unitType = $project->unitTypes()->find($key);
                        if ($unitType) {
                            $unitType->update([
                                'unit_type' => $unitTypeData['unit_type'],
                                'area_sqft' => $unitTypeData['area_sqft'],
                            ]);
                            $existingUnitTypeIds[] = $key;
                            
                            // Recalculate price
                            if ($project->pricingConfig) {
                                $this->pricingService->calculateUnitPrice($unitType);
                                $unitType->save();
                            }
                        }
                    } else {
                        // Create new unit type (key starts with 'new_')
                        $unitType = $project->unitTypes()->create([
                            'unit_type' => $unitTypeData['unit_type'],
                            'area_sqft' => $unitTypeData['area_sqft'],
                        ]);
                        
                        // Track newly created ID
                        $newlyCreatedUnitTypeIds[] = $unitType->id;
                        
                        // Calculate price if BSP is set
                        if ($project->pricingConfig) {
                            $this->pricingService->calculateUnitPrice($unitType);
                            $unitType->save();
                        }
                    }
                }
            }
            
            // Delete unit types that were removed (not in the submitted list)
            // Merge existing and newly created IDs to preserve both
            $allPreservedIds = array_merge($existingUnitTypeIds, $newlyCreatedUnitTypeIds);
            if (!empty($allPreservedIds)) {
                $project->unitTypes()->whereNotIn('id', $allPreservedIds)->delete();
            } elseif (empty($unitTypes)) {
                // If no unit types submitted, delete all
                $project->unitTypes()->delete();
            }
            
            // Mark starting from
            if ($project->pricingConfig) {
                $this->pricingService->markStartingFrom($project);
            }
        }

        // Handle towers (for flats)
        if (!empty($towers)) {
            $existingTowerIds = [];
            $newlyCreatedTowerIds = [];
            
            foreach ($towers as $key => $towerData) {
                if (isset($towerData['tower_name'])) {
                    if (is_numeric($key)) {
                        // Update existing tower
                        $tower = $project->towers()->find($key);
                        if ($tower) {
                            $tower->update([
                                'tower_name' => $towerData['tower_name'],
                                'tower_number' => $towerData['tower_number'] ?? null,
                            ]);
                            $existingTowerIds[] = $key;
                        }
                    } else {
                        // Create new tower
                        $tower = $this->projectService->createTower($project, [
                            'tower_name' => $towerData['tower_name'],
                            'tower_number' => $towerData['tower_number'] ?? null,
                        ]);
                        // Track newly created tower ID
                        $newlyCreatedTowerIds[] = $tower->id;
                    }

                    // Handle unit types for this tower
                    if (isset($towerData['unit_types']) && !empty($towerData['unit_types'])) {
                        $existingTowerUnitIds = [];
                        $newlyCreatedTowerUnitIds = [];
                        
                        foreach ($towerData['unit_types'] as $unitKey => $unitTypeData) {
                            if (isset($unitTypeData['unit_type']) && isset($unitTypeData['area_sqft'])) {
                                if (is_numeric($unitKey)) {
                                    // Update existing unit type
                                    $unitType = $tower->unitTypes()->find($unitKey);
                                    if ($unitType) {
                                        $unitType->update([
                                            'unit_type' => $unitTypeData['unit_type'],
                                            'area_sqft' => $unitTypeData['area_sqft'],
                                        ]);
                                        $existingTowerUnitIds[] = $unitKey;
                                        
                                        // Recalculate price
                                        if ($project->pricingConfig) {
                                            $this->pricingService->calculateUnitPrice($unitType);
                                            $unitType->save();
                                        }
                                    }
                                } else {
                                    // Create new unit type
                                    $unitType = $tower->unitTypes()->create([
                                        'project_id' => $project->id,
                                        'unit_type' => $unitTypeData['unit_type'],
                                        'area_sqft' => $unitTypeData['area_sqft'],
                                    ]);
                                    
                                    // Track newly created ID
                                    $newlyCreatedTowerUnitIds[] = $unitType->id;
                                    
                                    // Calculate price if BSP is set
                                    if ($project->pricingConfig) {
                                        $this->pricingService->calculateUnitPrice($unitType);
                                        $unitType->save();
                                    }
                                }
                            }
                        }
                        
                        // Delete removed unit types from tower
                        // Merge existing and newly created IDs to preserve both
                        $allPreservedTowerUnitIds = array_merge($existingTowerUnitIds, $newlyCreatedTowerUnitIds);
                        if (!empty($allPreservedTowerUnitIds)) {
                            $tower->unitTypes()->whereNotIn('id', $allPreservedTowerUnitIds)->delete();
                        } elseif (empty($towerData['unit_types'])) {
                            // If no unit types submitted, delete all
                            $tower->unitTypes()->delete();
                        }
                    }
                }
            }
            
            // Delete removed towers
            // Merge existing and newly created IDs to preserve both
            $allPreservedTowerIds = array_merge($existingTowerIds, $newlyCreatedTowerIds);
            if (!empty($allPreservedTowerIds)) {
                $project->towers()->whereNotIn('id', $allPreservedTowerIds)->delete();
            } elseif (empty($towers)) {
                // If no towers submitted, delete all
                $project->towers()->delete();
            }
            
            // Mark starting from if BSP is set
            if ($project->pricingConfig) {
                $this->pricingService->markStartingFrom($project);
            }
        }

        // Handle collaterals
        if (!empty($collaterals)) {
            $existingCollateralIds = [];
            $newlyCreatedCollateralIds = [];
            
            foreach ($collaterals as $key => $collateralData) {
                if (isset($collateralData['category']) && isset($collateralData['title']) && isset($collateralData['link'])) {
                    if (is_numeric($key)) {
                        // Update existing collateral
                        $collateral = $project->collaterals()->find($key);
                        if ($collateral) {
                            $collateral->update([
                                'category' => $collateralData['category'],
                                'title' => $collateralData['title'],
                                'link' => $collateralData['link'],
                                'is_latest' => isset($collateralData['is_latest']) && $collateralData['is_latest'] == '1',
                            ]);
                            $existingCollateralIds[] = $key;
                            
                            // If this is marked as latest price sheet, unmark others
                            if ($collateralData['category'] === 'price_sheet' && isset($collateralData['is_latest']) && $collateralData['is_latest'] == '1') {
                                $this->collateralService->markLatestPriceSheet($project, $collateral);
                            }
                        }
                    } else {
                        // Create new collateral
                        $collateral = $project->collaterals()->create([
                            'category' => $collateralData['category'],
                            'title' => $collateralData['title'],
                            'link' => $collateralData['link'],
                            'is_latest' => isset($collateralData['is_latest']) && $collateralData['is_latest'] == '1',
                        ]);
                        $newlyCreatedCollateralIds[] = $collateral->id;
                        
                        // If this is marked as latest price sheet, unmark others
                        if ($collateralData['category'] === 'price_sheet' && isset($collateralData['is_latest']) && $collateralData['is_latest'] == '1') {
                            $this->collateralService->markLatestPriceSheet($project, $collateral);
                        }
                    }
                }
            }
            
            // Delete collaterals that were removed
            $allPreservedCollateralIds = array_merge($existingCollateralIds, $newlyCreatedCollateralIds);
            if (!empty($allPreservedCollateralIds)) {
                $project->collaterals()->whereNotIn('id', $allPreservedCollateralIds)->delete();
            } elseif (empty($collaterals)) {
                // If no collaterals submitted, delete all
                $project->collaterals()->delete();
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $currentUser = request()->user();
        
        if (!$currentUser->isAdmin() && !$currentUser->isCrm()) {
            abort(403, 'Unauthorized action.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
