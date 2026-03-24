<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_telecaller_id' => 'required|exists:users,id',
            'to_telecaller_id' => 'required|exists:users,id|different:from_telecaller_id',
            'transfer_not_interested' => 'boolean',
            'transfer_cnp' => 'boolean',
        ]);

        $fromUserId = $validated['from_telecaller_id'];
        $toUserId = $validated['to_telecaller_id'];

        $query = CrmAssignment::where('assigned_to', $fromUserId);

        $conditions = [];
        
        if ($validated['transfer_not_interested'] ?? false) {
            $conditions[] = "call_status = 'called_not_interested'";
        }
        
        if ($validated['transfer_cnp'] ?? false) {
            $conditions[] = "(call_status = 'pending' AND cnp_count > 0)";
        }

        if (empty($conditions)) {
            return response()->json(['message' => 'Please select at least one lead type to transfer'], 400);
        }

        $whereClause = '(' . implode(' OR ', $conditions) . ')';
        
        $transferred = DB::transaction(function () use ($query, $whereClause, $toUserId) {
            $assignments = (clone $query)->whereRaw($whereClause)->get();
            $count = 0;

            foreach ($assignments as $assignment) {
                $assignment->update([
                    'assigned_to' => $toUserId,
                    'assigned_by' => auth()->id(),
                ]);
                $count++;
            }

            return $count;
        });

        return response()->json([
            'message' => "Successfully transferred {$transferred} leads",
            'transferred_count' => $transferred,
        ]);
    }
}
