<?php

namespace App\Services\Installation;

use App\Services\Supports\ActionBase;
use App\Services\ApiRequests\Shopify\Installation;

class InstallationService extends ActionBase
{
    public function __invoke()
    {
        return $this->done();
    }
    protected function done()
    {
        $response = (new Installation())
            ->assignRequestData($this->buildRequestData())
            ->get();
        if (!empty($response)) {
            raiseError(106);
        }
        bye($response);
    }

    private function buildRequestData(): array
    {
        return [
            'Mobile_no' => $this->request->mobile
        ];
    }
}
