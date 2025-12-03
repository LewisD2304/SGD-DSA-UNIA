<?php

use App\Livewire\Inicio\Index as InicioIndex;
use App\Livewire\Seguridad\Auth\Login;
use App\Livewire\Seguridad\Rol\Index as RolIndex;
use App\Livewire\Seguridad\Usuario\Index as UsuarioIndex;
use App\Livewire\Seguridad\Persona\Index as PersonaIndex;
use App\Livewire\Seguridad\Menu\Index as MenuIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['throttle:100,1'])->group(function () {

    Route::get('/inicio', InicioIndex::class)->name('inicio.index');

    // Ruta de Login (solo para invitados)
    Route::get('/login', Login::class)->name('login')->middleware('guest');

    // Agrupar rutas protegidas con 'auth'
    Route::middleware(['auth'])->group(function () {

        /*
    |--------------------------------------------------------------------------
    | MODULO DE SEGURIDAD
    |--------------------------------------------------------------------------
    */

        // Seguridad
        Route::prefix('seguridad')->name('seguridad.')->group(function () {
            // Menú
            Route::get('/menu', MenuIndex::class)->name('menu.index');
            // Usuarios
            Route::get('/usuario', UsuarioIndex::class)->name('usuario.index');

            // Rol
            Route::get('/rol', RolIndex::class)->name('rol.index');

            // Persona
            Route::get('/persona', PersonaIndex::class)->name('persona.index');
        });
    });
});
