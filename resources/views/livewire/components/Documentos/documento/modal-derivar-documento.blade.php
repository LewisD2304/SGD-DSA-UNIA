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

						<!-- Número documento y Folio -->
						<div class="row g-3 mb-3">
							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="numeroDocumento"
										readonly
										value="{{ $numeroDocumento }}"
									/>
									<label for="numeroDocumento">
										Número documento
									</label>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="folioDocumento"
										readonly
										value="{{ $folioDocumento }}"
									/>
									<label for="folioDocumento">
										Folio
									</label>
								</div>
							</div>
						</div>

						<!-- Asunto -->
						<div class="mb-3">
							<div class="form-floating">
								<textarea
									class="form-control bg-light"
									id="asuntoDocumento"
									readonly
									style="height: 80px"
								>{{ $asuntoDocumento }}</textarea>
								<label for="asuntoDocumento">
									Asunto
								</label>
							</div>
						</div>

						<!-- Área actual destino -->
						<div class="mb-3">
							<div class="form-floating">
								<input
									type="text"
									class="form-control bg-light"
									id="idAreaDestino"
									readonly
									value="{{ $modeloDocumento?->areaDestino->nombre_area ?? 'Sin área' }}"
								/>
								<label for="idAreaDestino">
									Área actual
								</label>
							</div>
						</div>

						<!-- Derivar a área (select de áreas) -->
						<div class="mb-3">
							<div class="form-floating">
								<select
									class="form-select @if ($errors->has('idAreaDerivar')) is-invalid @elseif($idAreaDerivar) is-valid @endif"
									id="idAreaDerivar"
									wire:model.live="idAreaDerivar"
								>
									<option value="">Seleccione un área de destino</option>
									@foreach($areas as $area)
										<option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
									@endforeach
								</select>
								<label for="idAreaDerivar">
									Derivar a <span class="text-danger">*</span>
								</label>
								@error('idAreaDerivar')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<!-- Observaciones -->
						<div class="mb-3">
							<div class="form-floating">
								<textarea
									class="form-control text-uppercase @if ($errors->has('observacionesDerivar')) is-invalid @elseif($observacionesDerivar) is-valid @endif"
									id="observacionesDerivar"
									placeholder="Observaciones"
									wire:model.live="observacionesDerivar"
									maxlength="200"
									style="height: 100px"
								></textarea>
								<label for="observacionesDerivar">
									Observaciones
								</label>
								@error('observacionesDerivar')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
								<small class="text-muted">{{ strlen($observacionesDerivar) }}/200 caracteres</small>
							</div>
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
