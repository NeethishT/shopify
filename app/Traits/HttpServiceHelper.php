<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

trait HttpServiceHelper
{
    public function curlInit(string $endpoint, array $data, $method = 'POST')
    {
        $status = false;
        try {
            $client = new GuzzleClient();
            $clientResponse = $client->{$method}($endpoint, $data);
            $status = !($clientResponse->getStatusCode() != 200);
            $content = $clientResponse->getBody()->getContents();
            $statusCode = $clientResponse->getStatusCode();
        } catch (ClientException $e) {
            $content = $e->getResponse()->getReasonPhrase() ?? 'Something went wrong';
            $statusCode = $e->getResponse()->getStatusCode() ?? 400;
        } catch (GuzzleException $e) {
            $content = $e->getMessage() ?? 'Something went wrong';
            $statusCode = $e->getCode() ?? 400;
        } catch (Exception $e) {
            $content = 'Dependent API Unkonown error : ' . $e->getMessage() ?? 'Something went wrong';
            $statusCode = $e->getCode() ?? 500;
        }
        Log::info('curlInit Response : ' . json_encode(['endpoint' => $endpoint, 'status' => $status, 'request' => $data, 'content' => $content, 'statusCode' => $statusCode]));
        return [$status, $content, $statusCode];
    }
}
