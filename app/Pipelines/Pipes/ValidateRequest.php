<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;

class ValidateRequest extends BasePipeline
{
    public function handle($request, Closure $next)
    {
        $arr = [];
        $hmac = $request['hmac'];
        unset($request['hmac']);
        foreach ($request as $key => $value) {
            $key = str_replace("%", "%25", $key);
            $key = str_replace("&", "%26", $key);
            $key = str_replace("=", "%3D", $key);
            $value = str_replace("%", "%25", $value);
            $value = str_replace("&", "%26", $value);
            $arr[] = $key . "=" . $value;
        }
        $str = implode('&', $arr);
        $ver_hmac =  hash_hmac('sha256', $str, config('custom.shopify_api_secret'), false);

        if ($ver_hmac != $hmac) {
            Log::info('Request is not valid');
            throw new Exception('Request is not valid!');
        }
        return $next($ver_hmac === $hmac);
    }
}
