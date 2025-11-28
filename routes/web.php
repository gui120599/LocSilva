<?php

use App\Http\Controllers\AluguelController;
use App\Http\Controllers\CaixaController;
use Illuminate\Support\Facades\Route;

Route::get('/print-aluguel/{id}',[AluguelController::class, 'printAluguel'])->name('print-aluguel')->middleware('auth');

Route::get('/print-caixa/{id}',[CaixaController::class, 'printCaixa'])->name('print-caixa')->middleware('auth');

Route::get('/laravel/login', fn() => redirect(route('filament.admin.auth.login')))->name('login');
