<?php

namespace App\Services\ApiRequests\ApiUtils;

class ApiBase
{
    use BaseFetchDna, AssignApiDataPoints;
    public string $baseUri;

    public array $headers;
    public $requestData;
    public string $endPoint;
    public string $defaultRoute;
    public function settings(string $key) {}
}
