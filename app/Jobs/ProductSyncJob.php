<?php

namespace App\Jobs;

use App\Models\Store;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\HttpServiceHelper;
use App\Services\ApiRequests\ApiUtils\EndPointManager;
use App\Jobs\BaseJobService;
use Carbon\Carbon;

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
            Log::error("Store details not found for store ID: {$this->storeId}");
            return;
        }

        $shop = $storeDetails['myshopify_domain'];
        $shopName = $storeDetails['name'];
        $accessToken = $storeDetails['access_token'];

        $endpoint = "https://{$shop}/admin/api/2025-01/graphql.json";
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
                return;
            }

            $content = json_decode($content);
            if (!isset($content->data)) {
                Log::warning('No data found in response');
                break;
            }

            $productEdges = $content->data->products->edges;

            foreach ($productEdges as $edge) {
                $products[] = $edge->node;
                $productData = $edge->node;

                // Check and delete existing product data
                $existProductIdCheck = Product::where('shopify_product_id', $productData->id)->first();
                if ($existProductIdCheck) {
                    ProductImage::where('product_id', $existProductIdCheck->id)->delete();
                    ProductVariant::where('product_id', $existProductIdCheck->id)->delete();
                    $existProductIdCheck->delete();
                }

                // Store or update product details
                $product = Product::updateOrCreate(
                    ['shopify_product_id' => $productData->id],
                    [
                        'store_id' => $this->storeId,
                        'product_created_at' => Carbon::parse($productData->createdAt)->toDateTimeString(),
                        'product_updated_at' => Carbon::parse($productData->updatedAt)->toDateTimeString(),
                        'body_html' => $productData->descriptionHtml ?? null,
                        'handle' => $productData->handle ?? null,
                        'product_type' => $productData->productType ?? null,
                        'title' => $productData->title ?? null,
                        'vendor' => $productData->vendor ?? null,
                        'tags' => implode(',', $productData->tags) ?? null,
                        'price_min' => $productData->contextualPricing->minVariantPricing->price->amount ?? null,
                        'price_max' => $productData->contextualPricing->maxVariantPricing->price->amount ?? null,
                        'compare_price_min' => $productData->compareAtPriceRange->minVariantCompareAtPrice->amount ?? null,
                        'compare_price_max' => $productData->compareAtPriceRange->maxVariantCompareAtPrice->amount ?? null,
                        'min_variant_price' => $productData->priceRange->minVariantPrice->amount ?? null,
                        'max_variant_price' => $productData->priceRange->maxVariantPrice->amount ?? null,
                        'product_published_at' => Carbon::parse($productData->publishedAt)->toDateTimeString() ?? null,
                        'status' => $productData->status ?? null,
                        'seo_title' => $productData->seo->title ?? null,
                        'seo_description' => $productData->seo->description ?? null,
                        'totalInventory' => $productData->totalInventory ?? null,
                        'tracks_inventory' => $productData->tracksInventory ?? null,
                        'full_url' => $productData->onlineStorePreviewUrl ?? null,
                        'online_store_preview_url' => $productData->onlineStorePreviewUrl ?? null,
                    ]
                );

                // Handle single variant
                if (count($productData->variants->nodes) === 1 && $productData->variants->nodes[0]->position === 1) {
                    $variant = $productData->variants->nodes[0];
                    $product->price = $variant->price ?? null;
                    $product->compare_price = $variant->compareAtPrice ?? null;
                    $product->save();
                }

                // Store product variants
                foreach ($productData->variants->nodes as $variantData) {
                    ProductVariant::updateOrCreate(
                        ['shopify_variant_id' => $variantData->id],
                        [
                            'product_id' => $product->id,
                            'available_for_sale' => $variantData->availableForSale ?? null,
                            'barcode' => $variantData->barcode ?? null,
                            'variant_created_at' => Carbon::parse($variantData->createdAt)->toDateTimeString() ?? null,
                            'display_name' => $variantData->displayName ?? null,
                            'inventory_policy' => $variantData->inventoryPolicy ?? null,
                            'inventory_quantity' => $variantData->inventoryQuantity ?? null,
                            'position' => $variantData->position ?? null,
                            'compare_at_price' => $variantData->compareAtPrice ?? null,
                            'price' => $variantData->price ?? null,
                            'sku' => $variantData->sku ?? null,
                            'title' => $variantData->title ?? null,
                            'variant_updated_at' => Carbon::parse($variantData->updatedAt)->toDateTimeString() ?? null,
                        ]
                    );
                }

                // Store product images
                foreach ($productData->media->nodes as $mediaData) {
                    $imageData = $mediaData->preview->image;
                    if ($mediaData->mediaContentType === 'IMAGE') {
                        ProductImage::updateOrCreate(
                            ['shopify_image_id' => $imageData->id],
                            [
                                'product_id' => $product->id,
                                'height' => $imageData->height ?? null,
                                'width' => $imageData->width ?? null,
                                'url' => $imageData->url ?? null,
                                'media_content_type' => $mediaData->mediaContentType ?? null,
                            ]
                        );
                    }
                }
            }

            $after = $content->data->products->pageInfo->hasNextPage
                ? $content->data->products->pageInfo->endCursor
                : null;
        } while ($after);

        $fileName = 'products_' . now()->format('Ymd_His') . '.json';
        $directoryPath = storage_path("app/{$shopName}/products");

        $this->baseService->saveToFile($directoryPath, $fileName, $products);

        Log::info("Products saved to file: {$fileName}");
    }
}
