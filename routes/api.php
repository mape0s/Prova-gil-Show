<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovimentacaoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => response()->json($request->user()->only(['id', 'name', 'email', 'role_id'])));
    Route::get('/saldo', [AuthController::class, 'saldo']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/extrato', [MovimentacaoController::class, 'extrato']);
    Route::post('/pix', [MovimentacaoController::class, 'pix']);
    Route::post('/aplicar', [MovimentacaoController::class, 'aplicar']);
    Route::post('/resgatar', [MovimentacaoController::class, 'resgatar']);
});
