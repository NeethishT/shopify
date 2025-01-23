<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookSettings;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;

class WebhookService
{
    use HttpServiceHelper;
    protected $shopDetails;

    public function __construct($shopDetails)
    {
        $this->shopDetails = $shopDetails;
    }

    public function done($type)
    {
        if (method_exists($this, $type)) {
            $this->{$type}();
        } else {
            Log::error("Invalid webhook action: {$type}");
        }
    }

    public function create()
    {
        $this->logInfo("Initiating webhook creation");

        $shop = $this->shopDetails['myshopify_domain'];
        $accessToken = $this->shopDetails['access_token'];
        $webhookSettings = WebhookSettings::where('status', 1)->get();
        $appUrl = config('custom.app_url');

        foreach ($webhookSettings as $webhookSetting) {
            if (!$this->isWebhookExists($webhookSetting->topic)) {
                $this->createWebhook($shop, $accessToken, $webhookSetting, $appUrl);
            }
        }
    }

    private function createWebhook($shop, $accessToken, $webhookSetting, $appUrl)
    {
        $callbackUrl = "{$appUrl}/{$webhookSetting->endpoint}";
        $data = $this->prepareWebhookPayload($webhookSetting->topic, $callbackUrl);

        $endpoint = "https://$shop/admin/api/2025-01/graphql.json";
        [$status, $content, $statusCode] = $this->curlInit($endpoint, $data);

        if (!$status || $statusCode != 200) {
            $this->logError("Failed to create webhook for topic {$webhookSetting->topic}");
            return;
        }

        $response = json_decode($content);
        $webhookId = $response->data->webhookSubscriptionCreate->webhookSubscription->id ?? null;

        if ($webhookId) {
            $this->saveWebhook($webhookId, $webhookSetting->topic, $callbackUrl);
        } else {
            $this->logError("Error creating webhook: " . json_encode($response));
        }
    }

    private function prepareWebhookPayload($topic, $callbackUrl)
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $this->shopDetails['access_token']
            ],
            'json' => [
                "query" => "
                    mutation webhookSubscriptionCreate(\$topic: WebhookSubscriptionTopic!, \$webhookSubscription: WebhookSubscriptionInput!) {
                        webhookSubscriptionCreate(topic: \$topic, webhookSubscription: \$webhookSubscription) {
                            webhookSubscription { id }
                            userErrors { field message }
                        }
                    }",
                "variables" => [
                    'topic' => $topic,
                    'webhookSubscription' => [
                        'callbackUrl' => $callbackUrl,
                        'format' => 'JSON',
                    ],
                ],
            ],
        ];
    }

    private function saveWebhook($webhookId, $topic, $callbackUrl)
    {
        Webhook::create([
            'store_id' => $this->shopDetails['id'],
            'topic' => $topic,
            'endpoint' => $callbackUrl,
            'format' => 'JSON',
            'webhook_id' => $webhookId,
        ]);
        $this->logInfo("Webhook created successfully: {$topic}");
    }

    public function delete()
    {
        $this->logInfo("Initiating webhook deletion");

        $webhooks = Webhook::where(['status' => 1, 'store_id' => $this->shopDetails['id']])->get();
        if ($webhooks->isEmpty()) {
            $this->logInfo("No active webhooks found for deletion");
            return;
        }

        foreach ($webhooks as $webhook) {
            $this->deleteWebhook($webhook);
        }
    }

    private function deleteWebhook($webhook)
    {
        $shop = $this->shopDetails['myshopify_domain'];
        $endpoint = "https://$shop/admin/api/2025-01/graphql.json";
        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $this->shopDetails['access_token']
            ],
            'json' => [
                "query" => "mutation webhookSubscriptionDelete(\$id: ID!) { webhookSubscriptionDelete(id: \$id) { deletedWebhookSubscriptionId userErrors { field message } } }",
                "variables" => [
                    'id' => $webhook->webhook_id,
                ],
            ],
        ];

        [$status, $content, $statusCode] = $this->curlInit($endpoint, $data);

        if (!$status || $statusCode != 200) {
            $this->logError("Failed to delete webhook with ID {$webhook->webhook_id}");
            return;
        }

        Webhook::where('webhook_id', $webhook->webhook_id)->delete();
        $this->logInfo("Webhook deleted successfully: {$webhook->webhook_id}");
    }

    private function isWebhookExists($topic)
    {
        return Webhook::where(['store_id' => $this->shopDetails['id'], 'topic' => $topic])->exists();
    }

    private function logInfo($message)
    {
        Log::info("WebhookService: {$message}");
    }

    private function logError($message)
    {
        Log::error("WebhookService: {$message}");
    }
}
