<?php

namespace App\Services\ApiRequests\Shopify\Supports;

use App\Services\ApiRequests\ApiUtils\ApiBase;
use App\Services\ApiRequests\ApiUtils\EndPointManager;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApiAccessLogFault;
use Illuminate\Http\Request;

abstract class ShopifyBase extends ApiBase
{
    protected $request;
    public function __construct()
    {
        $this->baseUri = EndPointManager::SHOPIFY_BASE_URI;
        $this->baseHeader();
        $this->request = app(Request::class);
    }

    public function baseHeader()
    {
        $this->headers = ['Content-Type' => 'application/json'];
    }
    abstract protected function prepare($addBearerToken);
    protected function guardData(array $data): array
    {
        return [
            'req' => openssl_encrypt(
                json_encode($data),
                'AES-128-CBC',
                EndPointManager::SHOPIFY_SECRET,
                0,
                EndPointManager::SHOPIFY_CLIENT_ID
            )
        ];
    }

    protected function unGuardData(array $data)
    {
        $res = openssl_decrypt(
            json_encode($data),
            'AES-128-CBC',
            EndPointManager::SHOPIFY_SECRET,
            0,
            EndPointManager::SHOPIFY_CLIENT_ID
        );
        if (empty($res)) {
            $message = (string)$data;
            return ['status' => '400', 'message' => 'Unable to parse api response :' . $message];
        }
        return json_decode($res, true);
    }

    protected function getBearerToken(): string
    {
        // $value = (new ShopifyAuthToken())
        //     ->assignRequestData([
        //         'name' => EndPointManager::API_USERNAME,
        //         'pwd' => EndPointManager::API_PWD
        //     ])->get();
        return 'Bearer ';
    }

    abstract public function get();
}
