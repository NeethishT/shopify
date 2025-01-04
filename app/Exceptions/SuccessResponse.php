<?php

namespace App\Exceptions;

use App\Exceptions\Supports\RenderSupport;
use Exception;

class SuccessResponse extends Exception
{
    use RenderSupport;

    public function __construct($message = "", int $code = 200, ?Exception $previous = null)
    {
        $message['message'] = !empty($message['message']) ? $message['message'] : 'Success';
        parent::__construct(json_encode($message), $code, $previous);
    }
    public static function bye(array $data)
    {
        throw new self($data, 200);
    }
}
