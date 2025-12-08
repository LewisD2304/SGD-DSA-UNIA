<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Services\Seguridad\PermisoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(PermisoService $permisoService)
    {
        // Gate para verificar permisos: acción + menú según el rol del usuario
        Gate::define('autorizacion', function (Usuario $usuario, string $accion, string $menu) use ($permisoService) {

            return $permisoService->puedeAcceder(
                $usuario,
                strtoupper($accion),
                strtoupper($menu)
            );
        });
    }
}
