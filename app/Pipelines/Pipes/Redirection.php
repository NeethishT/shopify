<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;

class Redirection extends BasePipeline
{
    public function handle($request, Closure $next) {}
}
