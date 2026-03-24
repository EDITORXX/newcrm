<?php

namespace App\Jobs;

use App\Models\FcmToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public string $url,
        public string $tag = 'crm-notification'
    ) {}

    public function handle(): void
    {
        $tokens = FcmToken::where('user_id', $this->userId)->pluck('fcm_token')->toArray();
        if (empty($tokens)) {
            return;
        }

        $credentialsPath = config('firebase.credentials');
        if (!file_exists($credentialsPath)) {
            Log::debug('FCM skipped: service account file not found', ['path' => $credentialsPath]);
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $messaging = $factory->createMessaging();

            $notification = Notification::create($this->title, $this->body);

            $webPushConfig = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $this->title,
                    'body' => $this->body,
                    'icon' => url('/icon-192.png'),
                    'click_action' => $this->url,
                    'tag' => $this->tag,
                    'requireInteraction' => true,
                ],
                'fcm_options' => [
                    'link' => $this->url,
                ],
            ]);

            $data = [
                'title' => $this->title,
                'body' => $this->body,
                'url' => $this->url,
                'tag' => $this->tag,
                'click_action' => $this->url,
            ];

            $invalidTokens = [];

            foreach ($tokens as $token) {
                try {
                    $message = CloudMessage::withTarget('token', $token)
                        ->withNotification($notification)
                        ->withWebPushConfig($webPushConfig)
                        ->withData($data);

                    $messaging->send($message);
                } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                    $invalidTokens[] = $token;
                } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
                    $invalidTokens[] = $token;
                    Log::warning('FCM invalid token', ['user_id' => $this->userId, 'error' => $e->getMessage()]);
                } catch (\Exception $e) {
                    Log::warning('FCM send error', ['user_id' => $this->userId, 'token' => substr($token, 0, 20) . '...', 'error' => $e->getMessage()]);
                }
            }

            if (!empty($invalidTokens)) {
                FcmToken::where('user_id', $this->userId)
                    ->whereIn('fcm_token', $invalidTokens)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error('FCM job error', ['user_id' => $this->userId, 'error' => $e->getMessage()]);
        }
    }
}
