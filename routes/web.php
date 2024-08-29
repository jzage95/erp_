<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ClienteController;
use Carbon\Carbon;

Route::get('/test-connection', function () {
    try {
        DB::connection()->getPdo();
        return 'Conexão com o banco de dados estabelecida com sucesso!';
    } catch (\Exception $e) {
        return 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
    }
});

//Route::get('/', [ClienteController::class, 'index']); 
Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
Route::get('clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');

