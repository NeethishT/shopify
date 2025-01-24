<?php

namespace App\Jobs;

use App\Models\Store;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use function App\GlobalMethods\getShopifyHeadersForStore;
use function App\GlobalMethods\getShopifyURLForStore;

class ConfigureWebhooks implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HttpServiceHelper;

    private $storeId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('here in configure webhooks');
            $store = Store::where('id', $this->storeId)->first();
            $endpoint = getShopifyURLForStore('webhooks.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $webhooks_config = config('custom.webhook_events');
            foreach ($webhooks_config as $topic => $url) {
                $data = [
                    'headers' => $headers,
                    'json' => [
                        'webhook' => [
                            'topic' => $topic,
                            'address' => config('app.url') . 'webhook/' . $url,
                            'format' => 'json'
                        ]
                    ]
                ];
                [$status, $content, $statusCode] = $this->curlInit($endpoint, $data);

                if (!$status || $statusCode != 200) {
                    Log::error("Failed to fetch collections for store ID: {$this->storeId}, Status: {$statusCode}");
                    return;
                }

                $response = json_decode($content);

                Log::info('Response for topic ' . $topic);
                Log::info($response['body']);
                //You can write a logic to save this in the database table.
            }
        } catch (Exception $e) {
            //Log::info(json_encode($e->getTrace()));
            Log::info('here in configure webhooks ' . $e->getMessage() . ' ' . $e->getLine());
        }
    }
}
