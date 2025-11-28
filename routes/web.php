<?php

use App\Livewire\Inicio\Index as InicioIndex;
use App\Livewire\Seguridad\Rol\Index as RolIndex;
use App\Livewire\Seguridad\Usuario\Index as UsuarioIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware(['auth'])->group(function () {

    // Inicio
    Route::get('/inicio', InicioIndex::class)->name('inicio.index');

    // Seguridad
    Route::prefix('seguridad')->name('seguridad.')->group(function () {
        // Usuarios
        Route::get('/usuario', UsuarioIndex::class)->name('usuario.index');

        // Rol
        Route::get('/rol', RolIndex::class)->name('rol.index');
    });

// });
