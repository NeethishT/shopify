<?php

namespace App\Pipelines\Pipes;

use App\Services\BasePipeline;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class IfAppIsEmbedded extends BasePipeline
{
    public function handle(Closure $next)
    {
        if (config('custom.app_embedded') == 'true' || config('custom.app_embedded') == true) {
            Log::info('App is in embedded mode');
            $user = User::where('store_id', $this->storeDetails->id)->first();
            Auth::login($user);
            return Redirect::route('accountVerifyPage');
        }
        Log::info('App is not in embedded mode');
        $user = User::where('store_id', $this->storeDetails->id)->first();
        Auth::login($user);
        return Redirect::route('accountVerifyPage');
    }
}
