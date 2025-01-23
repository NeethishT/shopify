<?php

namespace App\Jobs;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use App\Jobs\BaseJobService;

class PageSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HttpServiceHelper;

    private $storeId;
    private $baseService;

    public function __construct($storeId)
    {
        $this->storeId = $storeId;
        $this->baseService = new BaseJobService();
    }

    public function handle()
    {
        Log::info("PageSyncJob started for store ID: {$this->storeId}");

        // Get store details using the common service
        $storeDetails = $this->baseService->getStoreDetails($this->storeId);

        if (!$storeDetails) {
            return;
        }

        $endpoint = "https://{$storeDetails['shop']}/admin/api/2025-01/graphql.json";
        $accessToken = $storeDetails['accessToken'];

        $pages = [];
        $after = null;
        $limit = 250;

        do {
            $variables = ['limit' => $limit, 'after' => $after];
            $query = $this->baseService->getGraphQLQueryForPages();

            $requestData = [
                'headers' => ['X-Shopify-Access-Token' => $accessToken],
                'json' => ['query' => $query, 'variables' => $variables]
            ];

            [$status, $content, $statusCode] = $this->curlInit($endpoint, $requestData);

            if (!$status || $statusCode != 200) {
                Log::error("Failed to fetch pages for store ID: {$this->storeId}, Status: {$statusCode}");
                return;
            }

            $response = json_decode($content);

            if (isset($response->data->pages)) {
                foreach ($response->data->pages->edges as $edge) {
                    $pages[] = $edge->node;
                }
                $after = '"' . $response->data->pages->pageInfo->endCursor . '"';
                $hasNextPage = $response->data->pages->pageInfo->hasNextPage;
            } else {
                Log::warning("No pages found for store ID: {$this->storeId}");
                break;
            }
        } while ($hasNextPage);

        $fileName = 'pages_' . now()->format('Ymd_His') . '.json';
        $directoryPath = storage_path("app/{$storeDetails['shopName']}/pages");

        $this->baseService->saveToFile($directoryPath, $fileName, $pages);

        Log::info("Pages saved to file: {$fileName}");
    }
}
