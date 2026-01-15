<div>
    @section('breadcrumb')
    <x-breadcrumb titulo="Mi Perfil">
        <x-breadcrumb.item titulo="Mi Perfil" />
    </x-breadcrumb>
    @endsection

    <div class="row g-5 g-xl-10">
        <div class="col-xl-4">
            <div class="card mb-5 mb-xl-10">
                <div class="card-body pt-9 pb-9">
                    <div class="d-flex flex-column align-items-center mb-6">
                        <div class="d-flex flex-center flex-shrink-0 bg-light rounded mb-4">
                            <div class="symbol symbol-150px">
                                <div class="symbol-label fs-2 fw-bold text-primary bg-light-primary">
                                    {{ substr($nombres_persona ?? 'U', 0, 1) }}{{ substr($apellido_paterno_persona ?? 'S', 0, 1) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <h3 class="text-gray-800 fs-2 fw-bold mb-2">
                                {{ $nombres_persona ?? 'Usuario' }} {{ $apellido_paterno_persona ?? '' }}
                            </h3>
                            <div class="d-flex justify-content-center align-items-center mb-4">
                                <i class="ki-outline ki-sms fs-4 text-gray-500 me-1"></i>
                                <span class="text-gray-500 fs-6">{{ $correo_persona ?? 'No disponible' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <div class="border border-gray-300 border-dashed rounded py-4 px-5 text-center flex-grow-1">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-outline ki-user fs-4 text-primary mb-2"></i>
                                <div class="fs-6 fw-bold text-gray-800">{{ $nombre_usuario ?? 'Sin usuario' }}</div>
                                <div class="fs-8 text-gray-500 fw-semibold mt-1">Usuario</div>
                            </div>
                        </div>
                        <div class="border border-gray-300 border-dashed rounded py-4 px-5 text-center flex-grow-1">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-outline ki-profile-user fs-4 text-success mb-2"></i>
                                <div class="fs-6 fw-bold text-gray-800">{{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</div>
                                <div class="fs-8 text-gray-500 fw-semibold mt-1">Rol</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card mb-5 mb-xl-10">
                <div class="card-header cursor-pointer">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">
                            <i class="ki-outline ki-user-edit fs-2 me-2"></i>
                            Información Personal
                        </h3>
                    </div>
                </div>
                <div class="card-body p-9">
                    <form wire:submit.prevent="actualizarPerfil">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nombres</label>
                            <div class="col-lg-8">
                                <input type="text" wire:model="nombres_persona" class="form-control form-control-solid @error('nombres_persona') is-invalid @enderror" placeholder="Nombres" disabled>
                                @error('nombres_persona')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Apellido Paterno</label>
                            <div class="col-lg-8">
                                <input type="text" wire:model="apellido_paterno_persona" class="form-control form-control-solid @error('apellido_paterno_persona') is-invalid @enderror" placeholder="Apellido Paterno" disabled>
                                @error('apellido_paterno_persona')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6 ">Apellido Materno</label>
                            <div class="col-lg-8">
                                <input type="text" wire:model="apellido_materno_persona" class="form-control form-control-solid" placeholder="Apellido Materno" disabled>
                            </div>
                        </div>


                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label required fw-semibold fs-6">Correo Electrónico</label>
                            <div class="col-lg-8">
                                <input type="email" wire:model="correo_persona" class="form-control form-control-solid @error('correo_persona') is-invalid @enderror" placeholder="correo@ejemplo.com" disabled>
                                @error('correo_persona')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
