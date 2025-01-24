<?php

namespace App\Jobs;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use Carbon\Carbon;

class CollectionSyncJob implements ShouldQueue
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
        Log::info("CollectionSyncJob started for store ID: {$this->storeId}");

        $storeDetails = $this->baseService->getStoreDetails($this->storeId);

        if (!$storeDetails) {
            return;
        }


        $endpoint = "https://{$storeDetails['myshopify_domain']}/admin/api/2025-01/graphql.json";
        $accessToken = $storeDetails['access_token'];

        $collectionTypes = ['custom', 'smart'];
        $limit = 250; // Maximum limit per request
        $after = null;

        foreach ($collectionTypes as $collectionType) {
            do {
                $variables = ['limit' => $limit, 'after' => $after];
                $query = $this->baseService->getGraphQLQueryForCollections($collectionType);

                $requestData = [
                    'headers' => ['X-Shopify-Access-Token' => $accessToken],
                    'json' => ['query' => $query, 'variables' => $variables],
                ];

                [$status, $content, $statusCode] = $this->curlInit($endpoint, $requestData);

                if (!$status || $statusCode != 200) {
                    Log::error("Failed to fetch collections for store ID: {$this->storeId}, Status: {$statusCode}");
                    return;
                }

                $response = json_decode($content);

                if (isset($response->data->collections->edges)) {
                    foreach ($response->data->collections->edges as $edge) {
                        $collectionData = [
                            'store_id' => $this->storeId,
                            'shopify_collection_id' => $edge->node->id,
                            'title' => $edge->node->title,
                            'handle' => $edge->node->handle,
                            'description' => $edge->node->description,
                            'description_html' => $edge->node->descriptionHtml,
                            'sort_order' => $edge->node->sortOrder,
                            'template_suffix' => $edge->node->templateSuffix,
                            'store_products_ids' => implode(',', array_map(function ($product) {
                                $productRecord = Product::where('shopify_product_id', $product->id)->first();
                                return $productRecord ? $productRecord->id : null;
                            }, $edge->node->products->nodes)),
                            'shopify_products_ids' => implode(',', array_map(function ($product) {
                                return $product->id;
                            }, $edge->node->products->nodes)),
                            'shopify_products_title' => implode(',', array_map(function ($product) {
                                return $product->title;
                            }, $edge->node->products->nodes)),
                            'shopify_products_handle' => implode(',', array_map(function ($product) {
                                return $product->handle;
                            }, $edge->node->products->nodes)),
                            'collection_published_at' => Carbon::parse($edge->node->updatedAt)->toDateTimeString(),
                            'collection_created_at' => Carbon::parse($edge->node->updatedAt)->toDateTimeString(),
                            'collection_updated_at' => Carbon::parse($edge->node->updatedAt)->toDateTimeString(),
                            'image_id' => $edge->node->image ? $edge->node->image->id : null,
                            'image_url' => $edge->node->image ? $edge->node->image->url : null,
                            'image_width' => $edge->node->image ? $edge->node->image->width : null,
                            'image_height' => $edge->node->image ? $edge->node->image->height : null,
                        ];

                        Collection::updateOrCreate(
                            ['shopify_collection_id' => $edge->node->id],
                            $collectionData
                        );
                    }

                    $after = '"' . $response->data->collections->pageInfo->endCursor . '"';
                    $hasNextPage = $response->data->collections->pageInfo->hasNextPage;
                } else {
                    Log::warning("No collections found for store ID: {$this->storeId}");
                    break;
                }
            } while ($hasNextPage);
        }

        Log::info("CollectionSyncJob completed for store ID: {$this->storeId}");
    }
}
