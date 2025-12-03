<?php

namespace App\Livewire\Seguridad\Auth;

use App\Services\Seguridad\ErrorConexionException;
use App\Services\Seguridad\UsuarioService;
use App\Services\Seguridad\AutenticacionException;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('    | Sistema de Gestión Documental | SGD')]
class Login extends Component
{
    public $titulo = '¡Bienvenido!';
    public $usuario = '';
    public $password = '';
    public $remember = false;
    public $mensaje_error;

    protected UsuarioService $usuario_service;

    public function __construct()
    {
        $this->usuario_service = resolve(UsuarioService::class);
    }

    // Validación dinámica
    public function rules()
    {
        return [
            'usuario' => 'required|min:3|max:30|regex:/^[A-Za-z0-9@._-]+$/',
            'password' => 'required|min:8|max:20'
        ];
    }

    // Mensajes personalizados
    public function messages()
    {
        return [
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'El campo contraseña debe contener al menos 8 caracteres.'
        ];
    }

    // función para iniciar sesión
    public function iniciar_sesion()
    {
        $this->validate();

        try {
            $usuario = limpiarCadena($this->usuario);

            $autenticado = $this->usuario_service->autenticar(
                $usuario,
                $this->password,
            );
            if ($autenticado) {
                return redirect()->intended('/inicio');
            }

        } catch (ErrorConexionException $e) {
            session()->flash('message', $e->getMessage());
        } catch (AutenticacionException $e) {
            session()->flash('message', $e->getMessage()); // <--- aquí se muestra "Credenciales incorrectas."
        } catch (Exception $e) {
            session()->flash('message', "Error inesperado: " . $e->getMessage());
        }
    }

    public function updated($valor)
    {
        $this->validateOnly($valor);
    }

    public function mount()
    {
        // Solo para pruebas
        $this->usuario = '';
        $this->password = '';
    }

    public function render(){
        return view('livewire.auth.login');
    }
}
