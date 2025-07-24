<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeputadoController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Página inicial
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de Deputados
Route::prefix('deputados')->name('deputados.')->group(function () {
    Route::get('/', [DeputadoController::class, 'index'])->name('index');
    Route::get('/{id}', [DeputadoController::class, 'show'])->name('show');
    Route::post('/sincronizar', [DeputadoController::class, 'sincronizar'])->name('sincronizar');
    Route::post('/{id}/sincronizar-despesas', [DespesaController::class, 'sincronizarDeputado'])->name('sincronizar-despesas');
});

// Rotas de Despesas
Route::prefix('despesas')->name('despesas.')->group(function () {
    Route::get('/', [DespesaController::class, 'index'])->name('index');
    Route::get('/{id}', [DespesaController::class, 'show'])->name('show');
});

// API Routes (JSON)
Route::prefix('api')->name('api.')->group(function () {
    // API Deputados
    Route::prefix('deputados')->name('deputados.')->group(function () {
        Route::get('/', [DeputadoController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [DeputadoController::class, 'apiShow'])->name('show');
        Route::post('/sincronizar', [DeputadoController::class, 'sincronizar'])->name('sincronizar');
        Route::post('/{id}/sincronizar-despesas', [DespesaController::class, 'sincronizarDeputado'])->name('sincronizar-despesas');
    });

    // API Despesas
    Route::prefix('despesas')->name('despesas.')->group(function () {
        Route::get('/', [DespesaController::class, 'apiIndex'])->name('index');
        Route::get('/{id}', [DespesaController::class, 'apiShow'])->name('show');
    });
});