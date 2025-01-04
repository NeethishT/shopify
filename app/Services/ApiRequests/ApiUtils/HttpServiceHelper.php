<?php

namespace App\Services\ApiRequests\ApiUtils;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Entities\VendorCurlLog;

trait HttpServiceHelper
{
    public function httpPostJsonCall($apiEndpoint, $requestData, $headerContent = []): array
    {
        $data = [
            'headers' => $headerContent,
            'json' => $requestData
        ];
        $time['start'] = Carbon::now()->format('Y-m-d H:i:s.u');
        [$status, $content, $statusCode] = $this->curlInit($apiEndpoint, $data);
        $time['stop'] = Carbon::now()->format('Y-m-d H:i:s.u');
        $this->httpLogToMongo($apiEndpoint, $data, $content, $time, $statusCode);
        return [
            'status' => $status,
            'response' => $content,
            'statusCode' => $statusCode
        ];
    }

    public function curlInit(string $endpoint, $data, $method = 'post'): array
    {
        $status = false;
        try {
            $client = new GuzzleHttp();
            $clientResponse = $client->{$method}($endpoint, $data);
            $status = !($clientResponse->getStatusCode() !== 200);
            $content = $clientResponse->getBody()->getContents();
            $status = $clientResponse->getStatusCode();
        } catch (ClientException $e) {
            $content = $e->getResponse()->getReasonPhrase() ?? 'Internal issue,Something went wrong';
            $statusCode = $e->getResponse()->getStatusCode() ?? 500;
        } catch (GuzzleException $e) {
            $content = $e->getMessage();
            $statusCode = $e->getCode() ?? 500;
        } catch (Exception $e) {
            $content = 'Dependant API Unknown error : ' . $e->getMessage();
            $statusCode = $e->getCode() ?? 500;
        }
        return [$status, $content, $statusCode];
    }

    public function httpLogToMongo($endpoint, $request, $content, $time, $statusCode)
    {
        Log::info('shopify_logs', [
            'endpoint' => $endpoint,
            'request' => $request,
            'content' => $content,
            'statusCode' => $statusCode
        ]);
        VendorCurlLog::create([
            'uri' => $endpoint,
            'request' => $request['json'] ?? $request['body'] ?? [],
            'header' => $request['headers'],
            'response' => $content,
            'request_time' => $time['start'],
            'response_time' => $time['stop'],
            'code' => $statusCode
        ]);
    }
}
