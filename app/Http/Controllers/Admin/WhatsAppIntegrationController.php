<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppApiSettings;
use App\Services\WhatsAppApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppIntegrationController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppApiService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display WhatsApp API configuration page
     */
    public function index()
    {
        $settings = WhatsAppApiSettings::getSettings();
        return view('integrations.whatsapp', compact('settings'));
    }

    /**
     * Update WhatsApp API settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'api_endpoint' => 'nullable|url',
            'api_token' => 'required|string',
            'is_active' => 'boolean',
            'base_url' => 'nullable|url',
            'send_message_endpoint' => 'nullable|string',
            'send_template_endpoint' => 'nullable|string',
            'get_conversations_endpoint' => 'nullable|string',
            'get_messages_endpoint' => 'nullable|string',
            'get_templates_endpoint' => 'nullable|string',
            'get_template_endpoint' => 'nullable|string',
            'create_template_endpoint' => 'nullable|string',
            'delete_template_endpoint' => 'nullable|string',
            'get_groups_endpoint' => 'nullable|string',
            'make_group_endpoint' => 'nullable|string',
            'update_group_endpoint' => 'nullable|string',
            'remove_group_endpoint' => 'nullable|string',
            'import_contact_endpoint' => 'nullable|string',
            'update_contact_endpoint' => 'nullable|string',
            'remove_contact_endpoint' => 'nullable|string',
            'add_contacts_endpoint' => 'nullable|string',
            'get_media_endpoint' => 'nullable|string',
            'get_campaigns_endpoint' => 'nullable|string',
            'send_campaign_endpoint' => 'nullable|string',
        ]);

        try {
            $updateData = [
                'api_token' => $request->api_token,
                'is_active' => $request->has('is_active'),
                'is_verified' => false, // Reset verification on update
                'verified_at' => null,
            ];

            // Update api_endpoint if provided
            if ($request->filled('api_endpoint')) {
                $updateData['api_endpoint'] = rtrim($request->api_endpoint, '/');
            }

            // Update base_url if provided
            if ($request->filled('base_url')) {
                $updateData['base_url'] = rtrim($request->base_url, '/');
            }

            // Update all endpoint paths
            $endpointFields = [
                'send_message_endpoint',
                'send_template_endpoint',
                'get_conversations_endpoint',
                'get_messages_endpoint',
                'get_templates_endpoint',
                'get_template_endpoint',
                'create_template_endpoint',
                'delete_template_endpoint',
                'get_groups_endpoint',
                'make_group_endpoint',
                'update_group_endpoint',
                'remove_group_endpoint',
                'import_contact_endpoint',
                'update_contact_endpoint',
                'remove_contact_endpoint',
                'add_contacts_endpoint',
                'get_media_endpoint',
                'get_campaigns_endpoint',
                'send_campaign_endpoint',
            ];

            foreach ($endpointFields as $field) {
                if ($request->filled($field)) {
                    $updateData[$field] = $request->$field;
                }
            }

            $settings = WhatsAppApiSettings::updateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp API settings updated successfully',
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp Settings Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify API connection
     */
    public function verifyConnection(Request $request)
    {
        try {
            $result = $this->whatsappService->verifyConnection();

            if ($result['success']) {
                WhatsAppApiSettings::updateSettings([
                    'is_verified' => true,
                    'verified_at' => now(),
                ]);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WhatsApp Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test message
     */
    public function testMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $result = $this->whatsappService->sendMessage(
                $request->phone,
                $request->message
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WhatsApp Test Message Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
