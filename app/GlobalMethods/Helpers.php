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
