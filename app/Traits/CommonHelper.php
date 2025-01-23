<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait CommonHelper
{
    public function logging(array $data, $title = 'log')
    {
        Log::debug(sprintf("%s: %s", $title, json_encode($data)));
    }
}
