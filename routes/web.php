<?php

use App\Livewire\TodoDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/todos', TodoDashboard::class)
    ->name('todos')
    ->middleware('auth');
