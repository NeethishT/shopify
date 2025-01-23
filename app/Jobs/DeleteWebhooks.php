<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\WebhookService;
use App\Models\Store;


class DeleteWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $store_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id)
    {
        $this->store_id = $store_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Create Webhooks Job Inside");
        $shopDetails = Store::where('id', $this->store_id)->first()->toArray();
        $webHookResponse = (new WebhookService($shopDetails))->done('create');
    }
}
