<?php

namespace App\Jobs;

use App\Models\Blog;
use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use App\Jobs\BaseJobService;
use Carbon\Carbon;

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
            $variables = ['limit' => $limit, 'after' => $after];
            $query = $storeDetails = $this->baseService->getGraphQLQueryForBlogs();

            $requestData = [
                'headers' => ['X-Shopify-Access-Token' => $accessToken],
                'json' => ['query' => $query, 'variables' => $variables],
            ];

            [$status, $content, $statusCode] = $this->curlInit($endpoint, $requestData);

            if (!$status || $statusCode != 200) {
                Log::error("Failed to fetch blogs for store ID: {$this->storeId}, Status: {$statusCode}");
                return;
            }

            $response = json_decode($content);

            if (isset($response->data->blogs->edges)) {
                foreach ($response->data->blogs->edges as $edge) {
                    $blogNode = $edge->node;

                    Blog::where('shopify_blog_id', $blogNode->id)->delete();
                    $blogData = [
                        'shopify_blog_id' => $blogNode->id,
                        'title' => $blogNode->title,
                        'handle' => $blogNode->handle,
                        'tags' => implode(',', $blogNode->tags ?? []),
                        'template_suffix' => $blogNode->templateSuffix ?? null,
                        'blog_created_at' => Carbon::parse($blogNode->createdAt)->toDateTimeString(),
                        'blog_updated_at' => Carbon::parse($blogNode->updatedAt)->toDateTimeString(),
                        'store_id' => $this->storeId
                    ];

                    $blog = Blog::updateOrCreate(
                        ['shopify_blog_id' => $blogNode->id],
                        $blogData
                    );

                    foreach ($blogNode->articles->edges as $articleEdge) {
                        $articleNode = $articleEdge->node;

                        $articleData = [
                            'store_id' => $this->storeId,
                            'blog_id' => $blog->id,
                            'shopify_article_id' => $articleNode->id,
                            'title' => $articleNode->title ?? null,
                            'handle' => $articleNode->handle ?? null,
                            'author' => $articleNode->author->name ?? 'Unknown',
                            'body_html' => $articleNode->body ?? null,
                            'tags' => implode(',', $articleNode->tags ?? []),
                            'is_published' => $articleNode->isPublished ?? false,
                            'template_suffix' => $articleNode->templateSuffix ?? null,
                            'article_published_at' => $articleNode->publishedAt ? Carbon::parse($articleNode->publishedAt)->toDateTimeString() : null,
                            'article_created_at' => Carbon::parse($articleNode->createdAt)->toDateTimeString(),
                            'article_updated_at' => Carbon::parse($articleNode->updatedAt)->toDateTimeString(),
                            'image_id' => $articleNode->image->id ?? null,
                            'image_url' => $articleNode->image->url ?? null,
                            'image_height' => $articleNode->image->height ?? null,
                            'image_width' => $articleNode->image->width ?? null,
                        ];

                        Article::updateOrCreate(
                            ['shopify_article_id' => $articleNode->id],
                            $articleData
                        );
                    }
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
