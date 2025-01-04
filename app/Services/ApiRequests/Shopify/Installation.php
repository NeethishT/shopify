<?php

namespace App\Services\ApiRequests\Shopify;

use App\Services\ApiRequests\ApiUtils\EndPointManager;
use App\Services\ApiRequests\Shopify\Supports\ShopifyBase;

class Installation extends ShopifyBase
{

    public function get()
    {
        return $this->fetch() ?? null;
    }

    public function prepare($addBearerToken = false)
    {
        $this->defaultRoute = EndPointManager::SHOPIFY_INSTALLATION_URI;
        $this->addBearerTokenHeader($addBearerToken);
        $this->assignEndPoint();
    }
}
