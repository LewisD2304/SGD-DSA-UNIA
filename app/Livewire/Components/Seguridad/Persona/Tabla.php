<?php

namespace App\Livewire\Components\Seguridad\Persona;

use App\Services\Seguridad\PersonaService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Tabla extends Component
{
    use WithPagination;

    #[Url('mostrar')]
    public $mostrarPaginate = 10;
    #[Url('buscar')]
    public $buscar = '';

    protected PersonaService $personaService;

    public function __construct()
    {
        $this->personaService = resolve(personaService::class);
    }

    #[Computed()]
    #[On('refrescarPersonas')]
    public function personas()
    {
        return $this->personaService->listarPaginado($this->mostrarPaginate, $this->buscar, 'id_persona', 'desc');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="row g-5 gx-xl-10 mb-5 mb-xl-10 animate__animated animate__fadeIn animate__faster">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex flex-wrap flex-stack my-5 mx-8">
                        <div class="d-flex align-items-center position-relative my-1 me-4 fs-7">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input
                                type="text"
                                data-kt-user-table-filter="buscar"
                                class="form-control form-control-solid ps-13 w-xl-350px w-300"
                                placeholder="Buscar persona"
                                disabled
                            />
                        </div>

                        <div class="d-flex my-2">
                            <button
                                type="button"
                                class="btn btn-primary px-4 px-sm-6"
                                disabled
                            >
                                <i class="ki-outline ki-plus fs-2 px-0"></i>
                                <span class="d-none d-sm-inline">
                                    Nuevo
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body py-4">
                        <div lass="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer">
                                    <thead>
                                        <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                        <th class="w-10px pe-2">N°</th>
                                        <th class="min-w-200px">NOMBRE COMPLETO</th>
                                        <th class="min-w-200px">DOCUMENTO</th>
                                        <th class="min-w-60px">FECHA DE CREACION</th>
                                        <th class="min-w-60px">ESTADO</th>
                                        <th class="text-center min-w-60px">ACCIÓN</th>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold placeholder-glow">
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                            <td>
                                                <span class="placeholder col-12" style="background-color: #c4c4c4; border-radius: 0.42rem; height: 1.5rem;"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.components.seguridad.persona.tabla');
    }
}
