<?php

namespace App\Services\ApiRequests\ApiUtils;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApiAccessLogFault;

trait BaseFetchDna
{
    use HttpServiceHelper;
    protected function fetch($isAuth = false)
    {
        $this->prepare(!$isAuth);
        $data = [
            'headers' => $this->headers,
            'json' => $this->requestData
        ];
        $time['start'] = Carbon::now()->format('Y-m-d H:i:s.u');
        [$status, $content, $statusCode] = $this->processApiRequest($data);
        $time['stop'] = Carbon::now()->format('Y-m-d H:i:s.u');
        // $out = $this->getOutFromUnGuard($isAuth,$content);
        $this->httpLogToMongo($this->endPoint, $data, $content, $time, $statusCode);
        return $content;
    }

    protected function processApiRequest(array $data): array
    {
        $data['json'] = $this->guardData($this->requestData);
        return $this->curlInit($this->endPoint, $data);
    }

    protected function isResponseAlive($status)
    {
        if (!$status) {
            raiseError(106);
        }
    }

    public function getOutFromUnGuard($isAuth, $content)
    {
        try {
            Log::info('getOutFromUnGuard', [
                'Encrypt Response' => $content
            ]);
            return $isAuth ? $content : $this->unGuardData($content);
        } catch (ApiAccessLogFault $e) {
            return $e->getMessage();
        }
    }
}
