<div wire:ignore.self class="modal fade" id="modal-derivar-documento" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered mw-650px">
		<div class="modal-content">

			<div class="modal-header">
				<h3 class="fw-bold my-0">
					Derivar documento
				</h3>

				<div
					class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
					data-bs-dismiss="modal"
					aria-label="Close"
				>
					<i class="ki-outline ki-cross fs-1"></i>
				</div>
			</div>

			<form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarDerivar">

				<div class="modal-body px-5">
					<div class="d-flex flex-column px-5 px-lg-10">

						<!-- INFORMACIÓN DEL DOCUMENTO -->
						<div class="fw-bold text-dark mb-3 mt-3">
							<i class="ki-outline ki-document me-2"></i> Información del documento
						</div>

						<!-- Expediente -->
						<div class="mb-3">
							<div class="fw-bold text-gray-600 mb-1">Expediente:</div>
							<div class="text-gray-800">{{ $modeloDocumento?->expediente_documento ?? 'N/A' }}</div>
						</div>

						<!-- Tipo de documento -->
						<div class="mb-3">
							<div class="fw-bold text-gray-600 mb-1">Tipo de documento:</div>
							<div class="text-gray-800">{{ $modeloDocumento?->tipoDocumento->descripcion_catalogo ?? 'N/A' }}</div>
						</div>

						<!-- Asunto -->
						<div class="mb-3">
							<div class="fw-bold text-gray-600 mb-1">Asunto:</div>
							<div class="text-gray-800">{{ $asuntoDocumento }}</div>
						</div>

						<!-- Remitente -->
						<div class="mb-3">
							<div class="fw-bold text-gray-600 mb-1">Remitente:</div>
							<div class="text-gray-800">{{ $modeloDocumento?->areaRemitente->nombre_area ?? 'N/A' }}</div>
						</div>

						<!-- Enviar a (select de áreas) -->
						<div class="mb-3">
							<label class="required fw-semibold fs-6 mb-2">Enviar a</label>
							<select
								wire:model="idAreaDerivar"
								class="form-select form-select-solid @if ($errors->has('idAreaDerivar')) is-invalid @endif"
							>
								<option value="">Seleccione un área de destino</option>
								@foreach($areas as $area)
									<option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
								@endforeach
							</select>
							@error('idAreaDerivar')
								<div class="text-danger fs-7 mt-1">{{ $message }}</div>
							@enderror
						</div>

					</div>
				</div>

				<div class="modal-footer flex-center border-0">
					<button
						type="button"
						class="btn btn-light"
						data-bs-dismiss="modal"
					>
						Cancelar
					</button>

					<button type="submit" class="btn btn-primary">
						<span wire:loading.remove wire:target="guardarDerivar">
							<i class="ki-outline ki-check-circle fs-3"></i>
							Derivar
						</span>
						<span wire:loading wire:target="guardarDerivar">
							Procesando...
							<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
						</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
