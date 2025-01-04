<?php

namespace App\Exceptions;

use App\Exceptions\Supports\RenderSupport;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DefinedFault extends Exception
{
    use RenderSupport;
    public function __construct($message = null, $code = 400, ?Exception $previous = null)
    {
        $message['message'] ??= 'Unknown error.';
        parent::__construct(json_encode($message), $code, $previous);
    }

    public static function __callStatic($err, $args)
    {
        $configArray = Config::get('exceptionData');
        $err = str_replace('_', '', $err);
        if (!array_key_exists($err, $configArray)) {
            $err = '101';
        }
        $configArray[$err]['errors'] = $args[0] ?? [];
        throw new self(
            $configArray[$err],
            $configArray[$err]['code'] ?? 400
        );
    }

    public function getDecodeMessage()
    {
        return json_decode($this->getMessage(), true);
    }

    public function render(): JsonResponse
    {
        $response = $this->getDecodeMessage();
        $response['data'] = $response['data'] ?? [];
        $response['errors'] = $response['errors'] ?? [];

        if ($this->getCode() !== 422) {
            Log::error($response['message'] ?? '');
        }
        return $response()->json($response, $this->getCode());
    }

    public static function unProcessableEntity($errorData)
    {
        throw new self(
            [
                'code' => 422,
                'message' => 'unProcessable Entity',
                'errors' => $errorData,
            ],
            422
        );
    }
}
