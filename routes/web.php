<?php

use App\Models\Carreta;
use App\Models\Cliente;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documento/print/{id}', function ($id) {
    $registro = Carreta::find($id); // 👈 Ajuste o Model
    //dd($registro);

    if (!$registro->documento) {
        abort(404);
    }

    $filePath = Storage::disk('public')->path($registro->documento);
    $mimeType = Storage::disk('public')->mimeType($registro->documento);

    if ($filePath) {

        // PDF abre direto para impressão
        if ($mimeType === 'application/pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline'
            ]);
        }

        // Outros tipos faz download
        //return Storage::disk('public')->download($registro->documento); 
        abort(404);
    }

   

})->middleware('auth')->name('documento.print');
