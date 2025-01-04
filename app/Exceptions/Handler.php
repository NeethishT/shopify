<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\Supports\OutLander;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends ExceptionHandler
{
    use OutLander;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        SuccessResponse::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $e)
    {
        parent::report($e);
    }

    public function render($request, Throwable $e): JsonResponse
    {
        if ($e instanceof DefinedFault || $e instanceof SuccessResponse) {
            return $e->render();
        }
        [$outMessage, $outData] = $this->generateResponse($e);
        return $this->buildOutLander(
            $outMessage,
            $outData
        );
    }

    public function getCode(): int
    {
        return 500;
    }

    public function generateResponse(Throwable $e): array
    {
        $outMessage = 'Something went wrong';
        $outData = [];

        $isLocal = env('APP_ENV') === 'local';
        if ($isLocal) {
            $outMessage = $e->getMessage();
            $outData = $e->getTrace();
        }

        if (isDead($outMessage)) {
            Log::error($e);
            $outMessage = $isLocal ? get_class($e) : 'Something went wrong';
        }

        return [$outMessage, $outData];
    }
}
