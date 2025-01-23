<?php

namespace App\Pipelines\Pipes;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use function App\GlobalMethods\getShopifyHeadersForStore;
use App\Services\ApiRequests\ApiUtils\EndPointManager;
use App\Services\BasePipeline;
use Illuminate\Support\Facades\Redirect;

class AccessTokenFromShopifyForThisStore extends BasePipeline
{
    public function handle($isValidated, Closure $next)
    {
        if (request()->has('shop') && request()->has('code')) {
            $this->shop .= request()->shop;
            Log::info('requestAccessTokenFromShopifyForThisStore called');
            $endpoint = $this->shop . EndPointManager::SHOPIFY_ACCESS_TOKEN;
            $data = [
                'headers' => ['Content-Type: application/json'],
                'json' => [
                    'client_id' => $this->api_key,
                    'client_secret' => $this->api_secret,
                    'code' => request()->code
                ]
            ];
            [$status, $content, $statusCode] = $this->curlInit($endpoint,  $data);
            if (!$status || $statusCode != 200) {
                Log::info('Invalid Access Token ');
                throw new Exception('Invalid Access Token');
            }
            if (!is_array($content)) $content = json_decode($content, true);
            if (is_array($content) && isset($content['accessToken']) && $content['accessToken'] !== null)
                $this->accessToken = $content['accessToken'];
            return $next($content['accessToken']);
        }
    }
}
