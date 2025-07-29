<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// routes/api.php atau web.php
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

require __DIR__.'/auth.php';
