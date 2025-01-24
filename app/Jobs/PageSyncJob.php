<?php

namespace App\Jobs;

use App\Models\Store;
use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use App\Jobs\BaseJobService;
use Carbon\Carbon;

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
                $pageEdges = $response->data->pages->edges;

                foreach ($pageEdges as $edge) {
                    $pageData = $edge->node;

                    $existingPage = Page::where('shopify_page_id', $pageData->id)->first();
                    if ($existingPage) {
                        $existingPage->delete();
                    }

                    Page::updateOrCreate(
                        ['shopify_page_id' => $pageData->id],
                        [
                            'store_id' => $this->storeId,
                            'shopify_page_id' => $pageData->id,
                            'title' => $pageData->title ?? null,
                            'handle' => $pageData->handle ?? null,
                            'body' => $pageData->body ?? null,
                            'body_summary' => $pageData->bodySummary ?? null,
                            'is_published' => $pageData->isPublished ?? null,
                            'page_published_at' => Carbon::parse($pageData->publishedAt)->toDateTimeString() ?? null,
                            'page_created_at' => Carbon::parse($pageData->createdAt)->toDateTimeString() ?? null,
                            'page_updated_at' => Carbon::parse($pageData->updatedAt)->toDateTimeString() ?? null,
                        ]
                    );
                }

                $after = '"' . $response->data->pages->pageInfo->endCursor . '"';
                $hasNextPage = $response->data->pages->pageInfo->hasNextPage;
            } else {
                Log::warning("No pages found for store ID: {$this->storeId}");
                break;
            }
        } while ($hasNextPage);

        Log::info("PageSyncJob completed for store ID: {$this->storeId}");
    }
}
