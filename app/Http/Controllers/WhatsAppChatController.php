<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppChatController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppApiService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display chat interface
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all conversations, other users see only their own
        if ($user->isAdmin()) {
            $conversations = WhatsAppConversation::with(['messages' => function($query) {
                $query->latest()->limit(1);
            }, 'lead', 'user'])
            ->orderBy('updated_at', 'desc')
            ->get();
        } else {
            $conversations = WhatsAppConversation::where('user_id', $user->id)
                ->with(['messages' => function($query) {
                    $query->latest()->limit(1);
                }, 'lead'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        // Auto-link conversations to leads if phone matches
        foreach ($conversations as $conversation) {
            if (!$conversation->lead_id) {
                $this->autoLinkToLead($conversation);
            }
        }

        return view('chat.index', compact('conversations'));
    }

    /**
     * Auto-link conversation to lead if phone number matches
     */
    private function autoLinkToLead(WhatsAppConversation $conversation)
    {
        // Format phone for matching (remove country code and spaces)
        $phone = preg_replace('/[^0-9]/', '', $conversation->phone_number);
        
        // Try to find lead by phone number
        $lead = \App\Models\Lead::where(function($query) use ($phone) {
            // Match with country code
            $query->whereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") LIKE ?', ['%' . $phone . '%'])
                  ->orWhereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") LIKE ?', ['%' . substr($phone, -10) . '%']); // Last 10 digits
        })->first();

        if ($lead) {
            $conversation->update(['lead_id' => $lead->id]);
            if (!$conversation->contact_name && $lead->name) {
                $conversation->update(['contact_name' => $lead->name]);
            }
        }
    }

    /**
     * Get all conversations for the authenticated user
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();
        
        // Admin can see all conversations, other users see only their own
        if ($user->isAdmin()) {
            $conversationsQuery = WhatsAppConversation::with(['messages' => function($query) {
                $query->latest()->limit(1);
            }, 'lead', 'user']);
        } else {
            $conversationsQuery = WhatsAppConversation::where('user_id', $user->id)
                ->with(['messages' => function($query) {
                    $query->latest()->limit(1);
                }, 'lead']);
        }
        
        $conversations = $conversationsQuery->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($conversation) {
                $latestMessage = $conversation->getLatestMessage();
                
                // Auto-link to lead if not linked
                if (!$conversation->lead_id) {
                    $this->autoLinkToLead($conversation);
                    $conversation->refresh();
                }
                
                return [
                    'id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'contact_name' => $conversation->contact_name,
                    'user_id' => $conversation->user_id,
                    'user_name' => $conversation->user ? $conversation->user->name : null,
                    'lead_id' => $conversation->lead_id,
                    'lead' => $conversation->lead ? [
                        'id' => $conversation->lead->id,
                        'name' => $conversation->lead->name,
                        'email' => $conversation->lead->email,
                        'status' => $conversation->lead->status,
                        'url' => route('leads.show', $conversation->lead->id),
                    ] : null,
                    'unread_count' => $conversation->getUnreadCount(),
                    'latest_message' => $latestMessage ? [
                        'message' => $latestMessage->message,
                        'direction' => $latestMessage->direction,
                        'created_at' => $latestMessage->created_at->format('Y-m-d H:i:s'),
                    ] : null,
                    'updated_at' => $conversation->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Create new conversation (add number)
     */
    public function createConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'contact_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Format phone number
        $phone = preg_replace('/[^0-9]/', '', $request->phone_number);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Check if conversation already exists
        $conversation = WhatsAppConversation::where('user_id', Auth::id())
            ->where('phone_number', $phone)
            ->first();

        if ($conversation) {
            return response()->json([
                'success' => true,
                'message' => 'Conversation already exists',
                'data' => [
                    'id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'contact_name' => $conversation->contact_name,
                ],
            ]);
        }

        // Create new conversation
        $conversation = WhatsAppConversation::create([
            'user_id' => Auth::id(),
            'phone_number' => $phone,
            'contact_name' => $request->contact_name,
        ]);

        // Auto-link to lead if phone matches
        $this->autoLinkToLead($conversation);
        $conversation->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Conversation created successfully',
            'data' => [
                'id' => $conversation->id,
                'phone_number' => $conversation->phone_number,
                'contact_name' => $conversation->contact_name,
                'lead_id' => $conversation->lead_id,
                'lead' => $conversation->lead ? [
                    'id' => $conversation->lead->id,
                    'name' => $conversation->lead->name,
                    'status' => $conversation->lead->status,
                ] : null,
            ],
        ]);
    }

    /**
     * Get conversation with messages
     */
    public function getConversation($id)
    {
        $user = Auth::user();
        
        // Admin can access all conversations, other users can only access their own
        if ($user->isAdmin()) {
            $conversation = WhatsAppConversation::with(['messages', 'user', 'lead'])->find($id);
        } else {
            $conversation = WhatsAppConversation::where('user_id', $user->id)
                ->where('id', $id)
                ->with(['messages', 'lead'])
                ->first();
        }

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found or unauthorized',
            ], 404);
        }

        // Auto-link to lead if not linked
        if (!$conversation->lead_id) {
            $this->autoLinkToLead($conversation);
            $conversation->refresh();
        }

        // Mark as read
        $conversation->markAsRead();

        $messages = $conversation->messages->map(function($message) {
            return [
                'id' => $message->id,
                'direction' => $message->direction,
                'message' => $message->message,
                'status' => $message->status,
                'template_id' => $message->template_id,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'sent_at' => $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : null,
            ];
        });

        // Sync messages from API
        $this->syncMessagesFromAPI($conversation);

        // Reload messages after sync
        $conversation->refresh();
        $messages = $conversation->messages->map(function($message) {
            return [
                'id' => $message->id,
                'direction' => $message->direction,
                'message' => $message->message,
                'status' => $message->status,
                'template_id' => $message->template_id,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                'sent_at' => $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'contact_name' => $conversation->contact_name,
                    'user_id' => $conversation->user_id,
                    'user_name' => $conversation->user ? $conversation->user->name : null,
                    'lead_id' => $conversation->lead_id,
                    'lead' => $conversation->lead ? [
                        'id' => $conversation->lead->id,
                        'name' => $conversation->lead->name,
                        'email' => $conversation->lead->email,
                        'status' => $conversation->lead->status,
                        'url' => route('leads.show', $conversation->lead->id),
                    ] : null,
                ],
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'message' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = WhatsAppConversation::where('user_id', Auth::id())
            ->where('id', $request->conversation_id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        try {
            // Send message via API
            $result = $this->whatsappService->sendTextMessage(
                $conversation->phone_number,
                $request->message
            );

            // Map API status to database status
            $dbStatus = 'sent'; // Default
            if ($result['success']) {
                $apiStatus = $result['data']['status'] ?? null;
                // Map API status values to database enum values
                if ($apiStatus === 'success' || $apiStatus === 'sent' || $apiStatus === 'delivered' || $apiStatus === 'read') {
                    $dbStatus = $apiStatus === 'success' ? 'sent' : $apiStatus;
                } else {
                    $dbStatus = 'sent'; // Default to sent if status is unknown
                }
            } else {
                $dbStatus = 'failed';
            }

            // Save message to database
            $message = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'direction' => 'sent',
                'message' => $request->message,
                'message_id' => $result['success'] ? ($result['data']['id'] ?? $result['data']['message_id'] ?? null) : null,
                'status' => $dbStatus,
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Failed to send message'),
                'api_response' => $result,
                'sent_at' => $result['success'] ? now() : null,
            ]);

            // Update conversation timestamp
            $conversation->touch();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => [
                        'id' => $message->id,
                        'direction' => $message->direction,
                        'message' => $message->message,
                        'status' => $message->status,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message',
                    'error' => $result['error'] ?? 'Unknown error',
                    'data' => [
                        'id' => $message->id,
                        'status' => $message->status,
                    ],
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Message Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send template message
     */
    public function sendTemplateMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'template_id' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = WhatsAppConversation::where('user_id', Auth::id())
            ->where('id', $request->conversation_id)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        try {
            // Get template content
            $template = WhatsAppTemplate::where('template_id', $request->template_id)->first();
            $messageContent = $template ? $template->content : 'Template message';

            // Send template message via API
            $result = $this->whatsappService->sendTemplateMessage(
                $conversation->phone_number,
                $request->template_id,
                $request->parameters ?? []
            );

            // Map API status to database status
            $dbStatus = 'sent'; // Default
            if ($result['success']) {
                $apiStatus = $result['data']['status'] ?? null;
                // Map API status values to database enum values
                if ($apiStatus === 'success' || $apiStatus === 'sent' || $apiStatus === 'delivered' || $apiStatus === 'read') {
                    $dbStatus = $apiStatus === 'success' ? 'sent' : $apiStatus;
                } else {
                    $dbStatus = 'sent'; // Default to sent if status is unknown
                }
            } else {
                $dbStatus = 'failed';
            }

            // Save message to database
            $message = WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'direction' => 'sent',
                'message' => $messageContent,
                'message_id' => $result['success'] ? ($result['data']['id'] ?? $result['data']['message_id'] ?? null) : null,
                'template_id' => $request->template_id,
                'status' => $dbStatus,
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Failed to send template message'),
                'api_response' => $result,
                'sent_at' => $result['success'] ? now() : null,
            ]);

            // Update conversation timestamp
            $conversation->touch();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Template message sent successfully',
                    'data' => [
                        'id' => $message->id,
                        'direction' => $message->direction,
                        'message' => $message->message,
                        'template_id' => $message->template_id,
                        'status' => $message->status,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send template message',
                    'error' => $result['error'] ?? 'Unknown error',
                    'data' => [
                        'id' => $message->id,
                        'status' => $message->status,
                    ],
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Template Message Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending template message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available templates
     */
    public function getTemplates()
    {
        // First try to get from database
        $templates = WhatsAppTemplate::getAvailableTemplates();

        // If no templates in database, try to fetch from API
        if ($templates->isEmpty()) {
            $apiResult = $this->whatsappService->getTemplates();
            if ($apiResult['success'] && !empty($apiResult['data'])) {
                WhatsAppTemplate::syncFromAPI($apiResult['data']);
                $templates = WhatsAppTemplate::getAvailableTemplates();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $templates->map(function($template) {
                return [
                    'id' => $template->id,
                    'template_id' => $template->template_id,
                    'name' => $template->name,
                    'content' => $template->content,
                    'category' => $template->category,
                    'language' => $template->language,
                ];
            }),
        ]);
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        // Admin can mark any conversation as read, other users can only mark their own
        if ($user->isAdmin()) {
            $conversation = WhatsAppConversation::find($id);
        } else {
            $conversation = WhatsAppConversation::where('user_id', $user->id)
                ->where('id', $id)
                ->first();
        }

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        $conversation->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read',
        ]);
    }

    /**
     * Delete conversation
     */
    public function deleteConversation($id)
    {
        $user = Auth::user();
        
        // Admin can delete any conversation, other users can only delete their own
        if ($user->isAdmin()) {
            $conversation = WhatsAppConversation::find($id);
        } else {
            $conversation = WhatsAppConversation::where('user_id', $user->id)
                ->where('id', $id)
                ->first();
        }

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
        ]);
    }

    /**
     * Sync templates from API
     */
    public function syncTemplates()
    {
        try {
            // Check if API is configured
            if (!$this->whatsappService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp API is not configured. Please configure API settings first.',
                    'error' => 'API not configured',
                ], 400);
            }
            
            $apiResult = $this->whatsappService->getTemplates();
            
            if (!$apiResult['success']) {
                $errorMessage = $apiResult['error'] ?? 'Failed to fetch templates from API';
                
                // Provide more helpful error messages
                if (str_contains($errorMessage, 'Could not resolve host') || str_contains($errorMessage, 'cURL error 6')) {
                    $errorMessage = 'Cannot connect to API server. Please check the Base URL in settings. Current URL: ' . ($this->whatsappService->settings->base_url ?? 'Not set');
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $apiResult['error'] ?? 'Unknown error',
                    'details' => $apiResult,
                ], 500);
            }
            
            $templates = [];
            if (isset($apiResult['data'])) {
                $data = $apiResult['data'];
                // Handle different response formats
                if (isset($data['templates']) && is_array($data['templates'])) {
                    $templates = $data['templates'];
                } elseif (isset($data['data']) && is_array($data['data'])) {
                    $templates = $data['data'];
                } elseif (is_array($data)) {
                    $templates = $data;
                }
            }
            
            if (empty($templates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No templates found in API response',
                    'api_response' => $apiResult,
                ], 404);
            }
            
            $syncedCount = 0;
            $errors = [];
            
            foreach ($templates as $template) {
                try {
                    // Handle different template ID formats
                    $templateId = $template['id'] ?? $template['template_id'] ?? $template['name'] ?? null;
                    
                    if (!$templateId) {
                        $errors[] = 'Template missing ID: ' . json_encode($template);
                        continue;
                    }
                    
                    WhatsAppTemplate::updateOrCreate(
                        ['template_id' => $templateId],
                        [
                            'name' => $template['name'] ?? $template['template_name'] ?? 'Untitled Template',
                            'content' => $template['content'] ?? $template['body'] ?? $template['message'] ?? '',
                            'category' => $template['category'] ?? $template['type'] ?? null,
                            'language' => $template['language'] ?? $template['lang'] ?? 'en',
                            'is_active' => ($template['status'] ?? $template['state'] ?? 'APPROVED') === 'APPROVED',
                        ]
                    );
                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error('Error syncing template: ' . $e->getMessage(), ['template' => $template]);
                    $errors[] = 'Template sync error: ' . $e->getMessage();
                    continue;
                }
            }
            
            $response = [
                'success' => true,
                'message' => "Successfully synced {$syncedCount} template(s) from API",
                'synced_count' => $syncedCount,
                'total_templates' => WhatsAppTemplate::count(),
            ];
            
            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('WhatsApp Sync Templates Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error syncing templates: ' . $e->getMessage(),
            ], 500);
        }
    }
}
