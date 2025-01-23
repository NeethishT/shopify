<?php

namespace App\Jobs;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use App\Jobs\BaseJobService;

class BlogSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HttpServiceHelper;
    private $storeId;
    private $baseService;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($storeId)
    {
        $this->storeId = $storeId;
        $this->baseService = new BaseJobService();
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("BlogSyncJob Webhooks Job inside");
        // Get store details using the common service
        $storeDetails = $this->baseService->getStoreDetails($this->storeId);

        if (!$storeDetails) {
            return;
        }

        $endpoint = "https://{$storeDetails['shop']}/admin/api/2025-01/graphql.json";
        $accessToken = $storeDetails['accessToken'];

        $blogs = [];
        $after = null;
        $limit = 250; // Maximum limit per request is 250
        $page = 0;

        // $trunc = Project::truncate();

        do {
            $requestData = [
                "query" => $this->baseService->getGraphQLQueryForBlogs(),
                "variables" => ['limit' => $limit, 'after' => $after]
            ];

            [$status, $content, $statusCode] = $this->curlInit($endpoint, $requestData);

            if (!$status || $statusCode != 200) {
                Log::error("Failed to fetch blogs for store ID: {$this->storeId}, Status: {$statusCode}");
                return;
            }

            $response = json_decode($content);
            if (isset($response->data->blogs)) {
                foreach ($response->data->blogs->edges as $edge) {
                    $blogs[] = $edge->node;
                }
                $after = '"' . $response->data->blogs->pageInfo->endCursor . '"';
                $hasNextPage = $response->data->blogs->pageInfo->hasNextPage;
            } else {
                Log::warning("No blogs found for store ID: {$this->storeId}");
                break;
            }
        } while ($hasNextPage);


        $fileName = 'blogs_' . now()->format('Ymd_His') . '.json';
        $directoryPath = storage_path("app/{$storeDetails['shopName']}/blogs");

        $this->baseService->saveToFile($directoryPath, $fileName, $blogs);

        Log::info("blogs saved to file: {$fileName}");
    }
}
