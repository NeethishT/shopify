<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;
use Illuminate\Support\Facades\Log;
use function App\GlobalMethods\getShopifyHeadersForStore;
use App\Services\ApiRequests\ApiUtils\EndPointManager;

class RegisterForFulfillment extends BasePipeline
{
    public function handle($store, Closure $next)
    {
        Log::info('registerForFulfillmentService called');
        $endpoint = (new EndPointManager())->ShopifyURLForStore($store->toArray(), 'fulfillment_services.json');
        $data = [
            'headers' => getShopifyHeadersForStore($store->toArray()),
            'json' => [
                "fulfillment_service" => [
                    "name" => config('custom.fulfillment_service_name'),
                    "callback_url" => 'service_callback' ?? route('service_callback'),
                    "inventory_management" => true,
                    "tracking_support" => true,
                    "fulfillment_orders_opt_in" => true,
                    "requires_shipping_method" => true,
                    "format" => "json"
                ]
            ]
        ];
        [$status, $content, $statusCode] = $this->curlInit($endpoint,  $data);
        $store->update(['fulfillment_service_response' => json_encode($content)]);
        if (isset($content['statusCode']) && $content['statusCode'] == 201)
            $store->update(['fulfillment_service' => true, 'fulfillment_orders_opt_in' => true]);
        Log::info('Response received from shopify for fulfillment service creation ');
        return $next($store);
    }
}
