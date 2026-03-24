<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\McubeSetting;
use App\Models\McubeWebhookLog;
use App\Services\McubeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class McubeIntegrationController extends Controller
{
    public function index()
    {
        $settings    = McubeSetting::getSettings();
        $webhookUrl  = url('/api/webhooks/mcube');
        $recentLogs  = McubeWebhookLog::with(['agent', 'lead'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('integrations.mcube.index', compact('settings', 'webhookUrl', 'recentLogs'));
    }

    /** Save token + enabled toggle. */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'token'      => 'nullable|string|max:255',
            'is_enabled' => 'boolean',
        ]);

        $settings = McubeSetting::getSettings();
        $settings->token      = $request->input('token', $settings->token);
        $settings->is_enabled = $request->boolean('is_enabled');
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Settings saved.']);
    }

    /** Generate a random secure token. */
    public function generateToken()
    {
        $token = Str::random(40);
        return response()->json(['success' => true, 'token' => $token]);
    }

    /** Send a dummy payload to our own webhook for testing. */
    public function testWebhook(Request $request)
    {
        $settings = McubeSetting::getSettings();

        if (!$settings->token) {
            return response()->json(['success' => false, 'message' => 'Save a token first before testing.']);
        }
        if (!$settings->is_enabled) {
            return response()->json(['success' => false, 'message' => 'Enable the integration first.']);
        }

        // Dummy payload matching MCube format
        $dummyPayload = [
            'starttime'     => now()->subMinutes(5)->format('Y-m-d H:i:s'),
            'callid'        => 'TEST_' . time(),
            'emp_phone'     => $request->input('emp_phone', '9999999999'),
            'clicktocalldid'=> '9035053338',
            'callto'        => $request->input('callto', '8888888888'),
            'dialstatus'    => 'ANSWER',
            'filename'      => 'https://example.com/test-recording.wav',
            'direction'     => 'inbound',
            'endtime'       => now()->subMinutes(2)->format('Y-m-d H:i:s'),
            'disconnectedby'=> 'Customer',
            'answeredtime'  => '00:00:30',
            'groupname'     => 'Test',
            'agentname'     => 'Test Agent',
        ];

        // Call service directly (bypass HTTP to avoid SSL issues in dev)
        $service = new McubeWebhookService();
        $result  = $service->process($dummyPayload);

        // Log it manually
        McubeWebhookLog::create([
            'callid'        => $dummyPayload['callid'],
            'emp_phone'     => $dummyPayload['emp_phone'],
            'callto'        => $dummyPayload['callto'],
            'dialstatus'    => $dummyPayload['dialstatus'],
            'direction'     => $dummyPayload['direction'],
            'recording_url' => $dummyPayload['filename'],
            'call_starttime'=> $dummyPayload['starttime'],
            'call_endtime'  => $dummyPayload['endtime'],
            'status'        => $result['status'],
            'message'       => $result['message'] . ' [TEST]',
            'lead_id'       => $result['leadId']    ?? null,
            'agent_id'      => $result['agentId']   ?? null,
            'call_log_id'   => $result['callLogId'] ?? null,
            'raw_payload'   => $dummyPayload,
        ]);

        return response()->json([
            'success' => $result['status'] !== 'failed',
            'status'  => $result['status'],
            'message' => $result['message'],
        ]);
    }
}
