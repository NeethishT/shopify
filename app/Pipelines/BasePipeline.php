<?php

namespace App\Services;

use App\Traits\HttpServiceHelper;

abstract class BasePipeline
{
    use HttpServiceHelper;
    protected $api_scopes;
    protected $api_key;
    protected $api_secret;
    protected $shop = 'https://';
    protected $accessToken;
    protected $storeDetails;


    public function __construct()
    {
        $this->api_scopes = implode(',', config('custom.api_scopes'));
        $this->api_key = config('custom.shopify_api_key');
        $this->api_secret = config('custom.shopify_api_secret');
    }

    // abstract protected function handle();
}
