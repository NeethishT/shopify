<?php

namespace App\Services;

use App\Services\Supports\BaseService;


class InstallationService extends BaseService
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
                \App\Pipelines\Pipes\StoreDetails::class,
                \App\Pipelines\Pipes\IfAccessTokenIsValid::class,
                \App\Pipelines\Pipes\IfAppIsEmbedded::class,
            ])
            ->thenReturn()
            ->get();
    }
}
