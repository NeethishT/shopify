<?php

namespace App\GlobalMethods;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\DefinedFault;
use App\Exceptions\SuccessResponse;
use Illuminate\Support\Str;

if (!function_exists('raiseError')) {
    function raiseError(int $errCode, $data = [])
    {
        $configArray = Config::get('exceptiondata');
        if (!array_key_exists($errCode, $configArray)) {
            $errCode = 101;
        }
        $configArray[$errCode]['errors'] = $data;
        throw new DefinedFault(
            $configArray[$errCode],
            $configArray[$errCode]['code'] ?? 400
        );
    }
}

if (!function_exists('bye')) {
    function bye($data): JsonResponse
    {
        SuccessResponse::bye($data);
    }
}

if (!function_exists('getDockerURL')) {
    function getDockerURL($path, $port)
    {
        return 'http://localhost:' . $port . '/' . $path;
    }
}

if (!function_exists('getDockerHeaders')) {
    function getDockerHeaders()
    {
        return ['Content-Type' => 'application/json'];
    }
}

if (!function_exists('getShopifyURLForStore')) {
    function getShopifyURLForStore($endpoint, $store)
    {
        return checkIfStoreIsPrivate($store) ?
            'https://' . $store['api_key'] . ':' . $store['api_secret_key'] . '@' . $store['myshopify_domain'] . '/admin/api/' . config('custom.shopify_api_version') . '/' . $endpoint
            :
            'https://' . $store['myshopify_domain'] . '/admin/api/' . config('custom.shopify_api_version') . '/' . $endpoint;
    }
}

if (!function_exists('getShopifyHeadersForStore')) {

    function getShopifyHeadersForStore($store, $method = 'GET')
    {
        return $method == 'GET' ? [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $store['access_token']
        ] : [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: ' . $store['access_token']
        ];
    }
}

if (!function_exists('getGraphQLHeadersForStore')) {
    function getGraphQLHeadersForStore($store)
    {
        return checkIfStoreIsPrivate($store) ? [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $store['api_secret_key'],
            'X-GraphQL-Cost-Include-Fields' => true
        ] : [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $store['access_token'],
            'X-GraphQL-Cost-Include-Fields' => true
        ];
    }
}

if (!function_exists('checkIfStoreIsPrivate')) {
    function checkIfStoreIsPrivate($store)
    {
        return isset($store['api_key']) && isset($store['api_secret_key'])
            && $store['api_key'] !== null && $store['api_secret_key'] !== null
            && strlen($store['api_key']) > 0 && strlen($store['api_secret_key']) > 0;
    }
}

if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol($code)
    {
        switch ($code) {
            case 'INR':
                return '₹ ';
            case 'EUR':
                return '€ ';
            case 'UAH':
                return '₴ ';
            case 'PLN':
                return 'zł ';
            case 'RON':
                return 'Lei ';
            case 'CZK':
                return 'Kč ';
            case 'SEK':
                return 'SEK ';
            case 'HUF':
                return 'Ft ';
            case 'BYN':
                return 'BYN ';
            case 'BGN':
                return 'лв. ';
            case 'DKK':
                return 'DKK ';
            case 'NOK':
                return 'NOK ';
            case 'HRK':
                return 'kn ';
            case 'MDL':
                return 'L ';
            case 'BAM':
                return 'KM ';
            case 'ALL':
                return 'Lek ';
            case 'MKD':
                return 'ден ';
            case 'ISK':
                return 'kr ';
            case 'SAR':
                return 'ر.س';
            case 'ARS':
            case 'CAD':
                return 'C$ ';
            case 'NZD':
                return 'NZ$ ';
            case 'CLP':
            case 'COP':
            case 'MXN':
            case 'SGD':
                return 'S$ ';
            case 'AUD':
                return 'A$ ';
            case 'USD':
                return '$ ';
            case 'GBP':
                return '£ ';
            case 'CHF':
                return 'CHF ';
            case 'ZAR':
                return 'S ';
            case 'RUB':
                return '₽ ';
            case 'QAR':
                return 'ر.ق ';
            case 'MUR':
            case 'NPR':
                return '₨ ';
            case 'MYR':
                return 'RM ';
            case 'KPW':
            case 'KRW':
                return '₩ ';
            case 'JPY':
                return '¥ ';
            case 'IDR':
                return 'Rp ';
            case 'VND':
                return '₫ ';
            case 'KWD':
                return 'د.ك ';
            case 'AED':
                return 'د.إ ';
            case 'OMR':
                return 'ر.ع. ';
            case 'BOB':
                return '$ ';
            case 'AZN':
                return '₼ ';
            case 'THB':
                return '฿ ';
            default:
                return $code;
        }
    }
}

if (!function_exists('determineIfAppIsEmbedded')) {
    function determineIfAppIsEmbedded()
    {
        return config('custom.app_embedded') == 'true' || config('custom.app_embedded') == true;
    }
}
