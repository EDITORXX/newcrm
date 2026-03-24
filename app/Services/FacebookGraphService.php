<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookGraphService
{
    protected string $baseUrl = 'https://graph.facebook.com';

    public function __construct(
        protected string $accessToken,
        protected string $graphVersion = 'v18.0'
    ) {
    }

    public static function fromSettings(\App\Models\FbLeadAdsSettings $settings): self
    {
        return new self(
            $settings->page_access_token ?? '',
            $settings->graph_version ?? 'v18.0'
        );
    }

    /**
     * Create service from token + graph version (e.g. for a specific FbPage).
     */
    public static function fromToken(string $accessToken, string $graphVersion = 'v18.0'): self
    {
        return new self($accessToken, $graphVersion);
    }

    protected function url(string $path, array $query = []): string
    {
        $base = rtrim($this->baseUrl, '/') . '/' . ltrim($this->graphVersion . '/' . ltrim($path, '/'), '/');
        $query['access_token'] = $this->accessToken;
        return $base . '?' . http_build_query($query);
    }

    /**
     * Test token and get pages (for "Test Connection").
     * Uses GET /me/accounts (User token). If token is a Page token, falls back to GET /me?fields=id,name.
     * Graph API v18+: /me/accounts returns list of pages; /me with Page token returns the single page.
     */
    public function testConnection(): array
    {
        $response = Http::get($this->url('me/accounts', ['fields' => 'id,name,access_token']));

        if ($response->successful()) {
            $data = $response->json();
            $pages = $data['data'] ?? [];
            if (empty($pages)) {
                return [
                    'success' => true,
                    'pages' => [],
                    'error' => null,
                    'message' => 'No pages found. Ensure the token has pages_show_list permission and the Facebook account has at least one Page.',
                ];
            }
            return ['success' => true, 'pages' => $pages, 'error' => null];
        }

        $error = $response->json('error', []);
        $code = $error['code'] ?? null;
        $message = $error['message'] ?? $response->body() ?: 'Unknown error';

        // (#100) Tried accessing nonexistent field (accounts) = Page Access Token used with /me/accounts.
        // With Page token, "me" is the Page; fall back to GET /me?fields=id,name and return single page.
        if ((int) $code === 100 && (str_contains((string) $message, 'accounts') || str_contains((string) $message, 'nonexistent field'))) {
            $meResponse = Http::get($this->url('me', ['fields' => 'id,name']));
            if ($meResponse->successful()) {
                $page = $meResponse->json();
                $page['access_token'] = $this->accessToken;
                $pages = [$page];
                return ['success' => true, 'pages' => $pages, 'error' => null];
            }
        }

        Log::warning('Facebook Graph API test failed', ['body' => $response->body()]);
        return [
            'success' => false,
            'error' => $message,
            'pages' => [],
        ];
    }

    /**
     * Get page info (for showing page name after token test)
     */
    public function getPage(string $pageId): array
    {
        $response = Http::get($this->url($pageId, ['fields' => 'id,name']));
        if (!$response->successful()) {
            return ['success' => false, 'error' => $response->json('error.message', 'Unknown error')];
        }
        return ['success' => true, 'page' => $response->json()];
    }

    /**
     * GET /{page_id}/leadgen_forms?fields=id,name,created_time
     */
    public function getLeadgenForms(string $pageId): array
    {
        $response = Http::get($this->url($pageId . '/leadgen_forms', ['fields' => 'id,name,created_time']));
        if (!$response->successful()) {
            Log::warning('Facebook leadgen_forms failed', ['page_id' => $pageId, 'body' => $response->body()]);
            return ['success' => false, 'error' => $response->json('error.message', 'Unknown error'), 'forms' => []];
        }
        $data = $response->json();
        $forms = $data['data'] ?? [];
        return ['success' => true, 'forms' => $forms];
    }

    /**
     * Get lead details: GET /{leadgen_id}?fields=created_time,field_data,ad_id,form_id,platform
     */
    public function getLeadDetails(string $leadgenId): array
    {
        $response = Http::get($this->url($leadgenId, [
            'fields' => 'created_time,field_data,ad_id,form_id,platform',
        ]));
        if (!$response->successful()) {
            Log::warning('Facebook lead details failed', ['leadgen_id' => $leadgenId, 'body' => $response->body()]);
            return ['success' => false, 'error' => $response->json('error.message', 'Unknown error'), 'data' => null];
        }
        return ['success' => true, 'data' => $response->json()];
    }

    /**
     * Get form fields/questions (from first lead or form metadata if available).
     * Meta doesn't always expose form questions via API; we can infer from field_data keys of a sample lead.
     * For new forms with no leads yet, we might get empty. So we also accept field_data from a lead.
     */
    public function getFormFieldsSample(string $formId): array
    {
        $response = Http::get($this->url($formId, ['fields' => 'id,name,questions']));
        if (!$response->successful()) {
            return ['success' => false, 'fields' => []];
        }
        $data = $response->json();
        $questions = $data['questions'] ?? [];
        $fields = [];
        foreach ($questions as $q) {
            $key = $q['key'] ?? $q['name'] ?? null;
            $label = $q['label'] ?? $q['name'] ?? $key;
            if ($key) {
                $fields[] = ['name' => $key, 'label' => $label];
            }
        }
        return ['success' => true, 'fields' => $fields];
    }
}
