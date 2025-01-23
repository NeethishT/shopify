<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Store;
use App\Services\ApiRequests\ApiUtils\EndPointManager;

class StoreDetails extends BasePipeline
{
    public function handle($request, Closure $next)
    {
        Log::info('Request is valid');
        $shop = $request->has('shop'); //Check if shop parameter exists on the request.
        if (!$shop) {
            Log::info('Shop parameter not present in the request');
            throw new Exception('Shop parameter not present in the request');
        }
        Log::info('Shop parameter exists');
        $this->shop .= request()->shop;
        $storeDetails = app(Store::class)->getStoreByDomain($request->shop);
        if (!$storeDetails) {
            Log::info('Store record does not exist');
            $endpoint = $this->shop .
                EndPointManager::SHOPIFY_AUTH_VERIFY . '?client_id=' . $this->api_key .
                '&scope=' . $this->api_scopes .
                '&redirect_uri=' . config('custom.app_url') . '/shopify/auth/redirect';
            return Redirect::to($endpoint);
        }
        $this->storeDetails = $storeDetails;
        return $next($storeDetails);
    }
}
