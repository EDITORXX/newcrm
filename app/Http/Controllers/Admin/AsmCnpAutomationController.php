<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsmCnpAutomationAudit;
use App\Models\AsmCnpAutomationConfig;
use App\Models\AsmCnpAutomationPoolUser;
use App\Models\AsmCnpAutomationState;
use App\Models\AsmCnpAutomationUserOverride;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsmCnpAutomationController extends Controller
{
    public function index()
    {
        $config = AsmCnpAutomationConfig::query()
            ->with(['poolUsers.user.role', 'overrides.fromUser.role', 'overrides.toUser.role'])
            ->firstOrFail();

        $asmUsers = User::query()
            ->with('role')
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('slug', Role::ASSISTANT_SALES_MANAGER))
            ->orderBy('name')
            ->get();

        $activeStates = AsmCnpAutomationState::query()
            ->with(['lead:id,name,phone,status', 'originalAssignee:id,name', 'currentAssignee:id,name'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $recentAudits = AsmCnpAutomationAudit::query()
            ->with(['lead:id,name,phone', 'fromUser:id,name', 'toUser:id,name'])
            ->latest('acted_at')
            ->limit(25)
            ->get();

        return view('admin.automation.cnp', compact('config', 'asmUsers', 'activeStates', 'recentAudits'));
    }

    public function update(Request $request)
    {
        $config = AsmCnpAutomationConfig::query()->firstOrFail();

        $data = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'retry_delay_minutes' => 'required|integer|min:5|max:10080',
            'transfer_threshold_hours' => 'required|integer|min:1|max:720',
            'max_cnp_attempts' => 'required|integer|min:1|max:10',
            'fallback_routing' => 'required|in:round_robin',
            'pool_user_ids' => 'nullable|array',
            'pool_user_ids.*' => 'integer|exists:users,id',
            'overrides' => 'nullable|array',
            'overrides.*.from_user_id' => 'nullable|integer|exists:users,id',
            'overrides.*.to_user_id' => 'nullable|integer|exists:users,id|different:overrides.*.from_user_id',
        ]);

        DB::transaction(function () use ($config, $request, $data) {
            $config->update([
                'is_enabled' => $request->boolean('is_enabled'),
                'is_active' => $request->boolean('is_active'),
                'retry_delay_minutes' => $data['retry_delay_minutes'],
                'transfer_threshold_hours' => $data['transfer_threshold_hours'],
                'max_cnp_attempts' => $data['max_cnp_attempts'],
                'fallback_routing' => $data['fallback_routing'],
                'updated_by' => auth()->id(),
            ]);

            AsmCnpAutomationPoolUser::query()->where('config_id', $config->id)->delete();
            foreach (array_values(array_unique($data['pool_user_ids'] ?? [])) as $index => $userId) {
                AsmCnpAutomationPoolUser::create([
                    'config_id' => $config->id,
                    'user_id' => $userId,
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }

            AsmCnpAutomationUserOverride::query()->where('config_id', $config->id)->delete();
            foreach ($data['overrides'] ?? [] as $override) {
                if (empty($override['from_user_id']) || empty($override['to_user_id'])) {
                    continue;
                }

                AsmCnpAutomationUserOverride::create([
                    'config_id' => $config->id,
                    'from_user_id' => $override['from_user_id'],
                    'to_user_id' => $override['to_user_id'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('admin.automation.cnp.index')
            ->with('success', 'ASM CNP automation settings updated successfully.');
    }
}
