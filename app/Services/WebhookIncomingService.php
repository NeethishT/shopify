<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WebhookIncomingService
{
    protected $request;
    private const INCOMING_EVENTS = [
        'app/uninstall',
        'shop/updated',
        'products/create',
        'products/delete',
        'products/update',
        'collections/create',
        'collections/delete',
        'collections/update',
        'collection_listings/add',
        'collection_listings/remove',
        'collection_listings/update',
    ];
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function run($event)
    {
        if (!in_array($event, self::INCOMING_EVENTS, true)) {
            Log::warning('Unhandled webhook event received', ['event' => $event]);
            return response()->json(['message' => 'Unhandled webhook event'], 400);
        }

        Log::info('Webhook event received', [
            'event' => $event,
            'payload' => $this->request->all(),
        ]);

        // Add further processing logic here, if needed.

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }
}
