<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/pix-teste', function () {
    return response()->json([
        'mensagem' => 'API Pix funcionando!',
        'status' => true
    ]);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
