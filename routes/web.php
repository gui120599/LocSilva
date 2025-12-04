<?php

use App\Http\Controllers\AluguelController;
use App\Http\Controllers\CaixaController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {

    Route::get('/print-aluguel/{id}', [AluguelController::class, 'printAluguel'])->name('print-aluguel');

    Route::get('/print-caixa/{id}', [CaixaController::class, 'printCaixa'])->name('print-caixa');

    Route::get('/print-retirada/{id}', [AluguelController::class, 'printRetirada'])->name('print-retirada');

    Route::get('/print-devolucao/{id}', [AluguelController::class, 'printDevolucao'])->name('print-devolucao');

});

Route::get('/laravel/login', fn() => redirect(route('filament.admin.auth.login')))->name('login');
