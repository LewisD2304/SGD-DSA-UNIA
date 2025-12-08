<div class="w-lg-500px p-10">
    <form method="POST" wire:submit.prevent="iniciarSesion" class="form w-100" novalidate="novalidate" autocomplete="off">
        @csrf
        <div class="text-center mb-11">
            <h1 class="text-gray-900 fw-bolder mb-3">
                {{ $titulo  }}
            </h1>

            <div class="text-gray-500 fw-semibold fs-6">
                Accede a tu espacio en la plataforma
            </div>
        </div>

        <div class="fv-row mb-8 fv-plugins-icon-container">
            <input
                type="text"
                class="form-control bg-transparent @if ($errors->has('usuario')) is-invalid @endif"
                id="usuario"
                wire:model.defer="usuario"
                oninput="this.value = this.value.toUpperCase();"
                placeholder="Usuario"
                autocomplete="username"
                minlength="3"
                maxlength="30"
            >
            @error('usuario')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-3 fv-plugins-icon-container" data-kt-password-meter="true">
            <div class="input-group">
                <input
                    id="password"
                    class="form-control bg-transparent @error('password') is-invalid @enderror"
                    aria-describedby="basic-addon2"
                    wire:model.defer="password"
                    type="password"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                />
                <span style="cursor: pointer;" class="input-group-text" id="basic-addon2" data-kt-password-meter-control="visibility">
                    <i class="ki-outline ki-eye-slash fs-2"></i>
                    <i class="ki-outline ki-eye fs-2 d-none"></i>
                </span>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
            <div>
                @if(session()->has('message'))
                    <span class="text-danger">{{ session('message') }}</span>
                @endif
            </div>

        </div>

        <div class="d-grid mb-10">
            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                <!--begin::Indicator label (ocultar cuando cargando)-->
                <span class="indicator-label" wire:loading.remove wire:target="iniciarSesion">
                    Ingresar
                </span>

                <span class="indicator-progress" wire:loading wire:target="iniciarSesion">
                    Cargando...
                    <span>
                        <x-spinner style="width: 20px; height: 20px;"/>
                    </span>
                </span>
            </button>
        </div>

    </form>
</div>
