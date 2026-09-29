<?php

use App\Http\Controllers\EndpointController;
use App\Livewire\TodoDashboard;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', TodoDashboard::class)
    ->name('todos')
    ->middleware('auth');

Route::any('/{endpointPath}', EndpointController::class)
    ->where('endpointPath', '.*')
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
        SubstituteBindings::class,
    ]);
