<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\SiteVisit;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Show verification panel
     */
    public function index()
    {
        $user = auth()->user();
        // Create API token for authenticated requests
        $token = $user->createToken('crm-verification-token')->plainTextToken;
        
        return view('crm.verifications', ['api_token' => $token]);
    }
}
