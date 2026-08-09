<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'POS Backend API',
        'status' => 'running',
        'api_version' => 'v1',
    ]);
});
