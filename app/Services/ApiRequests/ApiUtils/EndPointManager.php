<?php

namespace App\Services\ApiRequests\ApiUtils;

use function App\GlobalMethods\getShopifyURLForStore;

class EndPointManager
{
    const SHOPIFY_BASE_URI = '';
    const SHOPIFY_SECRET = '';
    const SHOPIFY_CLIENT_ID = '';
    const SHOPIFY_INSTALLATION_URI = '';
    const SHOPIFY_API_VERSION = '2025-01';
    const SHOPIFY_ACCESS_TOKEN = '/admin/oauth/access_token';
    const SHOPIFY_AUTH_VERIFY = '/admin/oauth/authorize';
    const SHOPIFY_SCRIPT_URL = 'https://app.aerochat.ai/static/chatbox.js?data-src=https://app.aerochat.ai/chat/script/';
    const VERIFICATIONID = '2ffb780a382ba57f79b1da2af5701210';
    public function ShopifyURLForStore(array $store, string $endpoint = 'shop.json'): string
    {
        if (getShopifyURLForStore($endpoint, $store)) {
            return 'https://' . $store['api_key'] . ':' . $store['api_secret_key']
                . '@' . $store['myshopify_domain']
                . '/admin/api/' . self::SHOPIFY_API_VERSION
                . '/' . $endpoint;
        }

        return 'https://' . $store['myshopify_domain']
            . '/admin/api/' . self::SHOPIFY_API_VERSION
            . '/' . $endpoint;
    }
}
