<?php

namespace App\Jobs;

use App\Models\Store;
use Illuminate\Support\Facades\Log;

class BaseJobService
{
    public function getStoreDetails($storeId)
    {
        $store = Store::find($storeId);

        if (!$store) {
            Log::error("Store not found for ID: {$storeId}");
            return null;
        }

        return [
            'shop'         => $store->myshopify_domain,
            'shopName'     => $store->name,
            'accessToken'  => $store->access_token,
        ];
    }

    public function saveToFile($directoryPath, $fileName, $data)
    {
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        $filePath = $directoryPath . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        Log::info("Data saved to file: {$filePath}");
    }

    public function getGraphQLQueryForProducts(): string
    {
        return <<<'GRAPHQL'
            query($limit: Int!, $after: String) {
                products(first: $limit, after: $after) {
                    pageInfo { hasNextPage endCursor }
                    edges { node { id title handle totalInventory status variants(first: 250) { nodes { id price sku } } images(first: 250) { nodes { url } } } }
                }
            }
        GRAPHQL;
    }

    public function getGraphQLQueryForPages(): string
    {
        return <<<'GRAPHQL'
            query($limit: Int!, $after: String) {
                pages(first: $limit, after: $after) {
                    edges {
                        node {
                            id title handle body bodySummary isPublished publishedAt templateSuffix createdAt updatedAt
                        }
                    }
                    pageInfo { endCursor hasNextPage }
                }
            }
            GRAPHQL;
    }

    public function getGraphQLQueryForBlogs(): string
    {
        return <<<'GRAPHQL'
            query($limit: Int!, $after: String) {
                blogs(first: $limit, after: $after) {
                    edges {
                        node {
                            id
                            handle
                            title
                            updatedAt
                            commentPolicy
                            feed {
                                path
                                location
                            }
                            createdAt
                            templateSuffix
                            tags
                            articles(first: 250) {
                                edges {
                                    node {
                                        id
                                        title
                                        handle
                                        author {
                                            name
                                        }
                                        body
                                        isPublished
                                        publishedAt
                                        summary
                                        tags
                                        templateSuffix
                                        createdAt
                                        updatedAt
                                        image {
                                            id
                                            altText
                                            url
                                            height
                                            width
                                        }
                                    }
                                }
                            }
                        }
                    }
                    pageInfo {
                        endCursor
                        hasNextPage
                        hasPreviousPage
                        startCursor
                    }
                }
            }
        GRAPHQL;
    }
}
