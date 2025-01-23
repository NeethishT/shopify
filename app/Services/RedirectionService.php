<?php

namespace App\Services;

use App\Services\Supports\BaseService;


class RedirectionService extends BaseService
{
    public function __invoke()
    {
        return $this->done();
    }
    protected function done()
    {
        return $this->pipeline
            ->send($this->request)
            ->through([
                \App\Pipelines\Pipes\ValidateRequest::class,
                \App\Pipelines\Pipes\AccessTokenFromShopifyForThisStore::class,
                \App\Pipelines\Pipes\ShopDetails::class,
                \App\Pipelines\Pipes\SaveStoreDetailsToDB::class,
                \App\Pipelines\Pipes\RegisterForFulfillment::class,
                \App\Pipelines\Pipes\IfAppIsEmbedded::class
            ])
            ->thenReturn()
            ->get();
    }
}
