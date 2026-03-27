<?php

namespace App\Jobs;

use App\Models\FbForm;
use App\Models\FbLead;
use App\Models\FbWebhookEvent;
use App\Models\Lead;
use App\Models\User;
use App\Services\FacebookGraphService;
use App\Services\FacebookLeadMappingService;
use App\Services\SourceAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchFacebookLeadDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $leadgenId,
        public int $fbFormId
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if (FbLead::where('leadgen_id', $this->leadgenId)->exists()) {
            return;
        }

        $fbForm = FbForm::with(['page', 'mapping'])->find($this->fbFormId);
        if (!$fbForm || !$fbForm->page) {
            return;
        }

        $token = $fbForm->page->page_access_token;
        if (empty($token)) {
            $this->failEvent('No token for page');
            return;
        }

        $settings = \App\Models\FbLeadAdsSettings::getSettings();
        $graphVersion = $settings->graph_version ?? 'v18.0';
        $client = FacebookGraphService::fromToken($token, $graphVersion);
        $result = $client->getLeadDetails($this->leadgenId);

        if (!$result['success']) {
            $this->failEvent($result['error'] ?? 'Unknown error');
            return;
        }

        $data = $result['data'];
        $fieldData = $data['field_data'] ?? [];

        $mappingService = new FacebookLeadMappingService();
        $flatFieldData = $mappingService->fieldDataToFlat($fieldData);

        $fbLead = FbLead::create([
            'leadgen_id' => $this->leadgenId,
            'fb_form_id' => $this->fbFormId,
            'field_data_json' => $flatFieldData,
            'raw_response_json' => $data,
        ]);

        // CRM lead auto-create
        $crmLeadId = $this->createCrmLead($fbForm, $mappingService, $fieldData);
        if ($crmLeadId) {
            $fbLead->update(['crm_lead_id' => $crmLeadId]);

            // Auto-assign via Automation Rule (if configured)
            try {
                $lead = Lead::find($crmLeadId);
                if ($lead) {
                    app(SourceAutomationService::class)->assignFromSource(
                        $lead,
                        'facebook_lead_ads',
                        $this->fbFormId
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('FetchFacebookLeadDetailsJob: automation assign failed', [
                    'lead_id' => $crmLeadId,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        FbWebhookEvent::where('leadgen_id', $this->leadgenId)->where('status', 'received')->update(['status' => 'processed']);
    }

    protected function createCrmLead(FbForm $fbForm, FacebookLeadMappingService $mappingService, array $fieldData): ?int
    {
        try {
            $mappingJson = $fbForm->mapping?->mapping_json ?? [];

            $mapped = $mappingService->applyMapping($fieldData, $mappingJson);

            $name  = $mapped['name']  ?? null;
            $phone = $mapped['phone'] ?? null;

            // name aur phone dono required hain leads table mein
            if (empty($name) && empty($phone)) {
                Log::info('FetchFacebookLeadDetailsJob: name aur phone dono missing, CRM lead skip', ['leadgen_id' => $this->leadgenId]);
                return null;
            }

            $createdBy = User::orderBy('id')->value('id') ?? 1;

            $notes = null;
            if (!empty($mapped['meta']) && is_array($mapped['meta'])) {
                $parts = [];
                foreach ($mapped['meta'] as $k => $v) {
                    $parts[] = $k . ': ' . $v;
                }
                $notes = implode("\n", $parts);
            }

            $lead = Lead::create([
                'name'        => $name  ?: 'Facebook Lead',
                'phone'       => $phone ?: 'N/A',
                'email'       => $mapped['email']        ?? null,
                'address'     => $mapped['address']      ?? null,
                'city'        => $mapped['city']         ?? null,
                'state'       => $mapped['state']        ?? null,
                'pincode'     => $mapped['pincode']      ?? null,
                'requirements'=> $mapped['requirements'] ?? null,
                'notes'       => $mapped['notes']        ?? $notes,
                'source'      => \App\Models\Lead::normalizeSource('facebook_lead_ads'),
                'status'      => 'new',
                'created_by'  => $createdBy,
            ]);

            return $lead->id;
        } catch (\Throwable $e) {
            Log::warning('FetchFacebookLeadDetailsJob: CRM lead create failed', [
                'leadgen_id' => $this->leadgenId,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function failEvent(string $error): void
    {
        FbWebhookEvent::where('leadgen_id', $this->leadgenId)->where('status', 'received')->update([
            'status' => 'failed',
            'error'  => $error,
        ]);
        Log::warning('FetchFacebookLeadDetailsJob failed', ['leadgen_id' => $this->leadgenId, 'error' => $error]);
    }
}
