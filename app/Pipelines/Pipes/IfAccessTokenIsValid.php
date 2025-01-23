<?php

namespace App\Pipelines\Pipes;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use function App\GlobalMethods\getShopifyHeadersForStore;
use App\Services\ApiRequests\ApiUtils\EndPointManager;
use App\Services\BasePipeline;
use Illuminate\Support\Facades\Redirect;

class IfAccessTokenIsValid extends BasePipeline
{
    public function handle($storeDetails, Closure $next)
    {
        Log::info('checkIfAccessTokenIsValid called');
        if ($storeDetails !== null && isset($storeDetails->access_token) && strlen($storeDetails->access_token) > 0) {
            $token = $storeDetails->access_token;
            $endpoint = (new EndPointManager())->ShopifyURLForStore($storeDetails);
            $data = [
                'headers' => getShopifyHeadersForStore($storeDetails)
            ];
            [$status, $content, $statusCode] = $this->curlInit($endpoint,  $data, 'GET');
            if ($statusCode != 200) {
                Log::info('Access token is not valid');
                $endpoint = $this->shop .
                    EndPointManager::SHOPIFY_AUTH_VERIFY . '?client_id=' . $this->api_key .
                    '&scope=' . $this->api_scopes .
                    '&redirect_uri=' . config('custom.app_url') . '/shopify/auth/redirect';
                return Redirect::to($endpoint);
            }
            return $next($statusCode === 200);
        }
    }
}
