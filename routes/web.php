<?php

use Illuminate\Support\Facades\Route;
use App\Services\InstallationService;
use App\Services\WebhookIncomingService;
use App\Services\AccountService;
use App\Services\RedirectionService;

Route::get('/account-verify-page', [AccountService::class, 'accountVerifyPage'])->name('accountVerifyPage'); // 3
Route::post('/account-verify', [AccountService::class, 'accountVerify'])->name('accountValidationApi');
Route::get('/dashboard', [AccountService::class, 'accountDashboard'])->name('accountDashboard');
Route::post('/script-interation', [AccountService::class, 'scriptIntegrationApi'])->name('scriptIntegrationApi');

Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [InstallationService::class, '__invoke']); //1
    Route::get('/redirect', [RedirectionService::class, '__invoke'])->name('app_install_redirect'); // 2
    Route::get('/complete', [InstallationService::class, '__invoke'])->name('app_install_complete');
});

Route::prefix('webhook/services')->group(function () {
    Route::any('/{event}', [WebhookIncomingService::class, 'run']);
});
