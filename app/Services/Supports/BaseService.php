<?php

namespace App\Services\Supports;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use App\Traits\CommonHelper;
use App\Traits\HttpServiceHelper;

abstract class BaseService
{
    use CommonHelper, HttpServiceHelper;
    protected $request;
    protected $pipeline;
    protected $api_scopes;
    protected $api_key;
    protected $api_secret;

    public function __construct(Request $request, Pipeline $pipeline)
    {
        $this->request = $request;
        $this->pipeline = $pipeline;
        $this->api_scopes = implode(',', config('custom.api_scopes'));
        $this->api_key = config('custom.shopify_api_key');
        $this->api_secret = config('custom.shopify_api_secret');
    }
    abstract protected function done();
}
