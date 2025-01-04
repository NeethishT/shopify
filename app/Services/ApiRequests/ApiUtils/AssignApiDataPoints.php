<?php

namespace App\Services\ApiRequests\ApiUtils;

trait AssignApiDataPoints
{
    public function assignRequestData($data)
    {
        $this->requestData = $data;
        return $this;
    }

    public function addBearerTokenHeader($isNeed = false)
    {
        if ($isNeed) {
            return;
        }
        $this->headers = array_merge(
            $this->headers,
            [
                'Authorization' => $this->getBearerToken()
            ]
        );
    }

    protected function assignEndPoint()
    {
        $this->endPoint = $this->baseUri;
        $this->endPoint .= $this->defaultRoute;
    }
}
