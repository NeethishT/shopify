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
use App\Services\ApiRequests\ApiUtils\EndPointManager;
use App\Jobs\BaseJobService;

class ProductSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HttpServiceHelper;

    private $storeId;
    private $baseService;


    /**
     * Create a new job instance.
     */
    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
        $this->baseService = new BaseJobService();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Starting ProductSyncJob for store ID: ' . $this->storeId);

        // Get store details using the common service
        $storeDetails = $this->baseService->getStoreDetails($this->storeId);

        if (!$storeDetails) {
            return;
        }

        $shop = $storeDetails['myshopify_domain'];
        $shopName = $storeDetails['name'];
        $accessToken = $storeDetails['access_token'];

        $endpoint = "https://{$storeDetails['shop']}/admin/api/2025-01/graphql.json";
        $products = [];
        $after = null;
        $limit = 250;

        do {
            $data = [
                'headers' => ['X-Shopify-Access-Token' => $accessToken],
                'json' => [
                    'query' => $this->baseService->getGraphQLQueryForProducts(),
                    'variables' => ['limit' => $limit, 'after' => $after]
                ]
            ];

            [$status, $content, $statusCode] = $this->curlInit($endpoint, $data);

            if (!$status || $statusCode !== 200) {
                Log::error('Failed to fetch products. Status Code: ' . $statusCode);
                return [];
            }

            $content = json_decode($content);
            if (!isset($content->data)) {
                Log::warning('No data found in response');
                break;
            }

            foreach ($content->data->products->edges as $edge) {
                $products[] = $edge->node;
            }

            $after = $content->data->products->pageInfo->hasNextPage
                ? $content->data->products->pageInfo->endCursor
                : null;
        } while ($after);

        $fileName = 'products_' . now()->format('Ymd_His') . '.json';
        $directoryPath = storage_path("app/{$storeDetails['shopName']}/products");

        $this->baseService->saveToFile($directoryPath, $fileName, $products);

        Log::info("Products saved to file: {$fileName}");
    }
}
