<?php

use App\Livewire\Inicio\Index as InicioIndex;
use App\Livewire\Documentos\Documento\Index as DocumentoIndex;
use App\Livewire\Documentos\Pendientes\Index as PendientesIndex;
use App\Livewire\Seguridad\Auth\Login;
use App\Livewire\Seguridad\Rol\Index as RolIndex;
use App\Livewire\Seguridad\Rol\ConfiguracionAcceso;
use App\Livewire\Seguridad\Usuario\Index as UsuarioIndex;
use App\Livewire\Seguridad\Persona\Index as PersonaIndex;
use App\Livewire\Seguridad\Menu\Index as MenuIndex;
use App\Http\Controllers\ArchivoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::middleware(['throttle:100,1'])->group(function () {

    Route::redirect('/', 'inicio');

    // Ruta de Login (solo para invitados)
    Route::get('/login', Login::class)->name('login')->middleware('guest');

    // Ruta para cerrar sesión
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout')->middleware('auth');

    // Agrupar rutas protegidas con 'auth'
    Route::middleware(['auth'])->group(function () {

        Route::get('/inicio', InicioIndex::class)->name('inicio.index');

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
            Route::get('/rol/{id_rol}/configuracion-acceso', ConfiguracionAcceso::class)->name('rol.asignar');

            // Persona
            Route::get('/persona', PersonaIndex::class)->name('persona.index');
        });

        /*
    |--------------------------------------------------------------------------
    | MODULO DE GESTION DOCUMENTAL
    |--------------------------------------------------------------------------
    */

        Route::prefix('documentos')->name('documentos.')->group(function () {
            // Documento
            Route::get('/documento', DocumentoIndex::class)->name('documento.index');
            // Pendientes
            Route::get('/pendientes', PendientesIndex::class)->name('pendientes.index');
        });

        // Descargar archivos
        Route::get('/archivo/descargar', [ArchivoController::class, 'descargar'])->name('archivo.descargar');


    });
});
