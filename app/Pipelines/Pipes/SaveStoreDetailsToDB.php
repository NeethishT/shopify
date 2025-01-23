<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Store;
use App\Services\WebhookService;

class SaveStoreDetailsToDB extends BasePipeline
{
    public function handle($shopDetails, Closure $next)
    {
        Log::info('saveStoreDetailsToDatabase called');
        $payload = [
            'access_token' => $this->accessToken,
            'myshopify_domain' => $shopDetails['myshopify_domain'],
            'id' => $shopDetails['id'],
            'email' => $shopDetails['email'],
            'name' => $shopDetails['name'],
            'phone' => $shopDetails['phone'],
            'address1' => $shopDetails['address1'],
            'address2' => $shopDetails['address2'],
            'zip' => $shopDetails['zip']
        ];
        $store_db = Store::updateOrCreate(['myshopify_domain' => $shopDetails['myshopify_domain']], $payload);
        $random_password = '123456';
        Log::info('Password generated ' . $random_password);
        $user_payload = [
            'email' => $shopDetails['email'],
            'password' => bcrypt($random_password),
            'store_id' => $store_db->id,
            'name' => $shopDetails['name']
            //'email_verified_at' => date('Y-m-d h:i:s')
        ];
        Log::info('User payload', $user_payload);
        $user = User::updateOrCreate(['email' => $shopDetails['email']], $user_payload);
        $webHookResponse = (new WebhookService($shopDetails))->done('create');
        return $next($store_db);
    }
}
