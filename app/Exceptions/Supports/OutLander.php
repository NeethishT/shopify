<?php

namespace App\Exceptions\Supports;

use Illuminate\Http\JsonResponse;

trait OutLander
{
    public function buildOutLander($message, $out): JsonResponse
    {
        $build = [
            'message' => $message,
            'data' => $out
        ];

        return response()->json(
            $build,
            $this->getCode() ?? 500,
        );
    }
}
