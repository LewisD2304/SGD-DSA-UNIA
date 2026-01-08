<?php

namespace App\Livewire\Seguridad\Perfil;

use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Mi Perfil | GESTIÓN DOCUMENTAL')]
class Index extends Component
{
    public $usuario;
    public $persona;

    // Datos del usuario
    public $nombre_usuario;

    // Datos de la persona
    public $nombres_persona;
    public $apellido_paterno_persona;
    public $apellido_materno_persona;
    public $documento_persona;
    public $telefono_persona;
        public $correo_persona;

    // Cambio de contraseña
    public $password_actual;
    public $password_nuevo;
    public $password_confirmacion;

    public $mostrarCambioPassword = false;

    public function mount(): void
    {
        $this->usuario = Auth::user();
        $this->persona = $this->usuario->persona;

        // Cargar datos del usuario
        $this->nombre_usuario = $this->usuario->nombre_usuario ?? '';

        // Cargar datos de la persona con seguridad
        if ($this->persona) {
            $this->nombres_persona = $this->persona->nombres_persona ?? '';
            $this->apellido_paterno_persona = $this->persona->apellido_paterno_persona ?? '';
            $this->apellido_materno_persona = $this->persona->apellido_materno_persona ?? '';
            $this->documento_persona = $this->persona->documento_persona ?? '';
            $this->telefono_persona = $this->persona->telefono_persona ?? '';
                    $this->correo_persona = $this->persona->correo_persona ?? '';
        }
    }

    public function actualizarPerfil(): void
    {
        $this->validate([
            'nombres_persona' => 'required|string|max:255',
            'apellido_paterno_persona' => 'required|string|max:255',
            'apellido_materno_persona' => 'nullable|string|max:255',
            'documento_persona' => 'required|string|max:20',
            'telefono_persona' => 'nullable|string|max:15',
            'correo_persona' => 'required|email|max:255|unique:ta_persona,correo_persona,' . $this->persona->id_persona . ',id_persona',
        ], [
            'nombres_persona.required' => 'El nombre es obligatorio',
            'apellido_paterno_persona.required' => 'El apellido paterno es obligatorio',
            'documento_persona.required' => 'El número de documento es obligatorio',
            'correo_persona.required' => 'El correo electrónico es obligatorio',
            'correo_persona.email' => 'El correo electrónico debe ser válido',
            'correo_persona.unique' => 'El correo electrónico ya está en uso',
        ]);

        try {
            // Actualizar datos de la persona
            if ($this->persona) {
                $this->persona->update([
                    'nombres_persona' => $this->nombres_persona,
                    'apellido_paterno_persona' => $this->apellido_paterno_persona,
                    'apellido_materno_persona' => $this->apellido_materno_persona,
                    'documento_persona' => $this->documento_persona,
                    'telefono_persona' => $this->telefono_persona,
                                    'correo_persona' => $this->correo_persona,
                ]);
            }


            $this->dispatch('notify', tipo: 'success', mensaje: 'Perfil actualizado correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', tipo: 'error', mensaje: 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }

    public function cambiarPassword(): void
    {
        $this->validate([
            'password_actual' => 'required',
            'password_nuevo' => 'required|min:8|different:password_actual',
            'password_confirmacion' => 'required|same:password_nuevo',
        ], [
            'password_actual.required' => 'La contraseña actual es obligatoria',
            'password_nuevo.required' => 'La nueva contraseña es obligatoria',
            'password_nuevo.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'password_nuevo.different' => 'La nueva contraseña debe ser diferente a la actual',
            'password_confirmacion.required' => 'Debe confirmar la nueva contraseña',
            'password_confirmacion.same' => 'Las contraseñas no coinciden',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($this->password_actual, $this->usuario->password_usuario)) {
            $this->addError('password_actual', 'La contraseña actual es incorrecta');
            return;
        }

        try {
            // Actualizar contraseña
            $this->usuario->update([
                'password_usuario' => Hash::make($this->password_nuevo),
            ]);

            // Limpiar campos
            $this->reset(['password_actual', 'password_nuevo', 'password_confirmacion']);
            $this->mostrarCambioPassword = false;

            $this->dispatch('notify', tipo: 'success', mensaje: 'Contraseña actualizada correctamente');
        } catch (\Exception $e) {
            $this->dispatch('notify', tipo: 'error', mensaje: 'Error al cambiar la contraseña: ' . $e->getMessage());
        }
    }

    public function toggleCambioPassword(): void
    {
        $this->mostrarCambioPassword = !$this->mostrarCambioPassword;
        $this->reset(['password_actual', 'password_nuevo', 'password_confirmacion']);
    }

    public function render()
    {
        return view('livewire.seguridad.perfil.index');
    }
}
