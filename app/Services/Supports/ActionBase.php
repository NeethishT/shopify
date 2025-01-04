<?php

namespace App\Services\Supports;

use Illuminate\Http\Request;

abstract class ActionBase
{
    use RequestCommonData;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract protected function done();
}
