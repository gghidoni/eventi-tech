<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('api')
    ->group(base_path('routes/api_v1.php'));


