<?php

namespace App\Livewire\Seguridad\Auth;

use App\Services\Seguridad\ErrorConexionException;
use App\Services\Seguridad\UsuarioService;
use App\Services\Seguridad\AutenticacionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
            'password' => 'required|min:8|max:50'
        ];
    }

    // Mensajes personalizados
    public function messages()
    {
        return [
            'password.required' => 'El campo contraseña es obligatorio.'
        ];
    }

    // función para iniciar sesión
    public function iniciarSesion()
    {
        $this->validate();

        try {
            $usuarioInput = limpiarCadena($this->usuario);

            // Autenticar y obtener el modelo de usuario
            $usuarioModel = $this->usuario_service->autenticar($usuarioInput, $this->password);

            // Iniciar sesión y regenerar la sesión
            Auth::login($usuarioModel, $this->remember);
            Session::regenerate();

            return redirect()->intended('/inicio');

        } catch (ErrorConexionException $e) {
            session()->flash('message', $e->getMessage());
        } catch (AutenticacionException $e) {
            session()->flash('message', $e->getMessage());
        } catch (Exception $e) {
            session()->flash('message', "Error inesperado: " . $e->getMessage());
        }
    }

    // Cerrar sesión
    public function cerrarSesion()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
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
