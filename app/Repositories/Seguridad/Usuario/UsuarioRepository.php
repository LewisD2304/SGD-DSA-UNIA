<?php

namespace App\Repositories\Seguridad\Usuario;

use App\Enums\EstadoEnum;
use App\Models\Usuario;
use App\Services\Seguridad\AutenticacionException;
use Illuminate\Support\Facades\Auth;
use App\Traits\BaseRepositoryTrait;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    //TODAS LAS CONSULTAS SE VAN HACER ACA, NADA EN EL CONTROLADOR !!! =D
    //EN EL SERVICIO ESTÁ TODA LA LOGICA

    use BaseRepositoryTrait;
    protected $model;

    public function __construct(Usuario $usuario)
    {
        $this->model = $usuario;
    }

    // Verificar si un usuario existe por nombre de usuario
    public function existePorNombreUsuario(string $nombreUsuario): bool
    {
        return $this->model::where('nombre_usuario', $nombreUsuario)->exists();
    }

    // Listar usuarios habilitadas
    public function buscarHabilitados($buscar, $limite = null)
    {
        return Usuario::join('ta_persona', 'ta_persona.id_persona', '=', 'ta_usuario.id_persona')
            ->buscar($buscar)
            ->estado(EstadoEnum::HABILITADO)
            ->limite($limite)
            ->select(
                'ta_usuario.id_usuario',
                'ta_usuario.nombre_usuario',
                'ta_persona.nombres_persona',
                'ta_persona.apellido_paterno_persona',
                'ta_persona.apellido_materno_persona'
            )
            ->get();
    }

    // Obtener usuarios habilitados por id_persona
    public function obtenerUsuariosPorPersona(int $id_persona)
    {
        return $this->model::where('id_persona', $id_persona)
            ->where('estado_usuario', EstadoEnum::HABILITADO) // Filtrar solo usuarios habilitados
            ->with('roles:id_rol,nombre_rol') // Relación definida en el modelo
            ->get()
            ->map(function ($usuario) {
                return (object) [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre_usuario' => $usuario->nombre_usuario,
                    'nombre_rol' => $usuario->roles->first()->nombre_rol ?? 'Sin rol'
                ];
            });
    }

    // Buscar usuario por nombre de usuario (case insensitive)
    public function buscarPorNombreUsuario(string $nombre_usuario): ?Usuario
    {
        return Usuario::where('nombre_usuario', strtoupper($nombre_usuario))
            ->first();
    }

    // Intentar autenticar un usuario y retornar el modelo autenticado
    public function autenticar(string $usuario, string $password): ?Usuario
    {
        // Buscar por nombre_usuario en mayúsculas, coherente con almacenamiento
        $usuarioModel = $this->buscarPorNombreUsuario($usuario);

        if (!$usuarioModel) {
            throw new AutenticacionException("El usuario ingresado no existe.");
        }

        if ($usuarioModel->estado_usuario === EstadoEnum::DESHABILITADO) {
            throw new AutenticacionException("El usuario y contraseña ingresada es incorrecta.");
        }

        // Verificar contraseña hasheada (bcrypt)
        if (!Hash::check($password, $usuarioModel->clave_usuario)) {
            throw new AutenticacionException("La contraseña ingresada es incorrecta.");
        }

        return $usuarioModel;
    }


    // Verificar si un usuario tiene un permiso
    public function verificarPermiso(int $id_usuario, string $accion, string $menu, ?string $modulo = null)
    {
        DB::connection('seguridad_mysql')->statement("SET @permiso = 0");

        DB::connection('seguridad_mysql')->statement(
            "CALL sp_seguridad_verificar_permiso(:id_usuario, :accion, :menu, :modulo, @permiso)", [
                'id_usuario' => $id_usuario,
                'accion' => $accion,
                'menu' => $menu,
                'modulo' => $modulo
            ]
        );

        $resultado = DB::connection('seguridad_mysql')->select("SELECT @permiso AS tiene_permiso");

        return (bool) $resultado[0]->tiene_permiso;
    }


}
