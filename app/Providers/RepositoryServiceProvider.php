<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        /*******************************************************************
         * Seguridad
         *******************************************************************/

        // Menu
        $this->app->bind(
            'App\Repositories\Seguridad\Menu\MenuRepositoryInterface',
            'App\Repositories\Seguridad\Menu\MenuRepository'
        );
        // Usuario
        $this->app->bind(
            'App\Repositories\Seguridad\Usuario\UsuarioRepositoryInterface',
            'App\Repositories\Seguridad\Usuario\UsuarioRepository'
        );

        // Rol
        $this->app->bind(
            'App\Repositories\Seguridad\Rol\RolRepositoryInterface',
            'App\Repositories\Seguridad\Rol\RolRepository'
        );

        // Persona
        $this->app->bind(
            'App\Repositories\Seguridad\Persona\PersonaRepositoryInterface',
            'App\Repositories\Seguridad\Persona\PersonaRepository'
        );

        // Permiso
        $this->app->bind(
            'App\Repositories\Seguridad\Permiso\PermisoRepositoryInterface',
            'App\Repositories\Seguridad\Permiso\PermisoRepository'
        );

        /*******************************************************************
         * Configuracion
         *******************************************************************/

        // Catalogo
        $this->app->bind(
            'App\Repositories\Configuracion\Catalogo\CatalogoRepositoryInterface',
            'App\Repositories\Configuracion\Catalogo\CatalogoRepository'
        );
    }

    public function boot(): void
    {

    }
}
