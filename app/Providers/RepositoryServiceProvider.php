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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
