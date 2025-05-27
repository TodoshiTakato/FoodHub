<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get(uri: '/user', action: function (Request $request) {
    return $request->user();
})->middleware(middleware: 'auth:apiV1');
