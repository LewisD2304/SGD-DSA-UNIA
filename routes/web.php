<?php

use App\Livewire\Inicio\Index as InicioIndex;
use App\Livewire\Documentos\Documento\Index as DocumentoIndex;
use App\Livewire\Documentos\Pendientes\Index as PendientesIndex;
use App\Livewire\Seguridad\Auth\Login;
use App\Livewire\Seguridad\Rol\Index as RolIndex;
use App\Livewire\Seguridad\Rol\ConfiguracionAcceso;
use App\Livewire\Seguridad\Usuario\Index as UsuarioIndex;
use App\Livewire\Seguridad\Persona\Index as PersonaIndex;
use App\Livewire\Seguridad\Catalogo\Index as CatalogoIndex;
use App\Livewire\Seguridad\Menu\Index as MenuIndex;
use Illuminate\Support\Facades\Storage;
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
            // Catalogo
            Route::get('/catalogo', CatalogoIndex::class)->name('catalogo.index');
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
            // Historial
            Route::get('/historial', \App\Livewire\Documentos\Historial\Index::class)->name('historial.index');
        });


        // Servir archivos desde el disco 'share' (funciona para Disco D, C, o donde sea)
        Route::get('/storage/shared/{path}', function ($path) {

            // 1. Decodificar la ruta (espacios y tildes)
            $path = urldecode($path);

            // 2. Limpieza de seguridad
            $keyword = 'storage/shared/';
            if (strpos($path, $keyword) !== false) {
                $parts = explode($keyword, $path);
                $path = end($parts);
            }

            // 3. VERIFICAR Y SERVIR USANDO EL DISCO 'share'
            if (Storage::disk('share')->exists($path)) {
                return response()->file(Storage::disk('share')->path($path));
            }

            // Si no existe, abortamos
            abort(404);
        })->where('path', '.*')->name('archivo.ver');
    });
});
