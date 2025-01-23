<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\ApiRequests\ApiUtils\EndPointManager;
use function App\GlobalMethods\getShopifyHeadersForStore;


class ShopDetails extends BasePipeline
{
    public function handle($accessToken, Closure $next)
    {
        Log::info('getShopDetailsFromShopify called');
        $endpoint = (new EndPointManager())->ShopifyURLForStore(['myshopify_domain' => request()->shop]);
        $data = [
            'headers' => getShopifyHeadersForStore(['access_token' => $accessToken])
        ];
        [$status, $content, $statusCode] = $this->curlInit($endpoint,  $data, 'GET');
        if (!$status || $statusCode != 200) {
            Log::info('Response recieved for shop details');
        }
        if (!is_array($content)) $content = json_decode($content, true);
        return $next($content['shop'] ?? null);
    }
}
