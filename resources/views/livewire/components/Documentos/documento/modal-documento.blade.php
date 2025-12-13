<div wire:ignore.self class="modal fade" id="modal-documento" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered mw-650px">
		<div class="modal-content">

			<div class="modal-header placeholder-glow">
				<h3 class="fw-bold my-0">
					{{ $tituloModal }}
				</h3>

				<div
					class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
					data-bs-dismiss="modal"
					aria-label="Close"
				>
					<i class="ki-outline ki-cross fs-1"></i>
				</div>
			</div>

			<form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarDocumento">

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
										class="form-control text-uppercase @if ($errors->has('numeroDocumento')) is-invalid @elseif($numeroDocumento) is-valid @endif"
										id="numeroDocumento"
										autocomplete="off"
									placeholder="Ej: CARTA MULTIPLE Nº 004-2025-UNIA-VRA/DSA"
									wire:model.live="numeroDocumento"
									maxlength="100"
									/>
									<label for="numeroDocumento">
										Número documento <span class="text-danger">*</span>
									</label>
									@error('numeroDocumento')
										<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="number"
										class="form-control @if ($errors->has('folioDocumento')) is-invalid @elseif($folioDocumento) is-valid @endif"
										id="folioDocumento"
										autocomplete="off"
										placeholder="Folio"
										wire:model.live="folioDocumento"
										min="1"
										step="1"
									/>
									<label for="folioDocumento">
										Folio <span class="text-danger">*</span>
									</label>
									@error('folioDocumento')
										<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<!-- Asunto -->
						<div class="mb-3">
							<div class="form-floating">
								<textarea
									class="form-control text-uppercase @if ($errors->has('asuntoDocumento')) is-invalid @elseif($asuntoDocumento) is-valid @endif"
									id="asuntoDocumento"
									autocomplete="off"
									placeholder="Asunto"
									wire:model.live="asuntoDocumento"
									maxlength="200"
									style="height: 100px"
									@keypress="if (!/[A-Za-z0-9\s,.]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') event.preventDefault()"
								></textarea>
								<label for="asuntoDocumento">
									Asunto <span class="text-danger">*</span>
								</label>
								@error('asuntoDocumento')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

<!-- Área remitente (solo en edición) -->
					@if ($modoModal === 2)
						<div class="mb-3">
							<div class="form-floating">
								<input
									type="text"
									class="form-control bg-light"
									id="areaRemitente"
									readonly
									value="{{ auth()->user()->persona->area->nombre_area ?? 'Sin área asignada' }}"
								/>
								<label for="areaRemitente">
									Remitente (Área)
								</label>
							</div>
						</div>
					@endif

						<!-- Destino (select de áreas) -->
						<div class="mb-3">
							<div class="form-floating">
								<select
									class="form-select @if ($errors->has('idAreaDestino')) is-invalid @elseif($idAreaDestino) is-valid @endif"
									id="idAreaDestino"
									wire:model.live="idAreaDestino"
								>
									<option value="">Seleccione un destino</option>
									@foreach($areas as $area)
										<option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
									@endforeach
								</select>
								<label for="idAreaDestino">
									Destino (Área) <span class="text-danger">*</span>
								</label>
								@error('idAreaDestino')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<!-- Fecha recepción -->
						<div class="mb-3">
							<div class="form-floating">
								<input
									type="date"
									class="form-control @if ($errors->has('fechaRecepcionDocumento')) is-invalid @elseif($fechaRecepcionDocumento) is-valid @endif"
									id="fechaRecepcionDocumento"
									autocomplete="off"
									placeholder="Fecha recepción"
									wire:model.live="fechaRecepcionDocumento"
								/>
								<label for="fechaRecepcionDocumento">
									Fecha recepción
								</label>
								@error('fechaRecepcionDocumento')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<!-- Adjuntar documento -->
						<div class="mb-3">
							<label for="archivoDocumento" class="form-label">
								Adjuntar documento (PDF, PNG, máx. 10MB) <span class="text-danger">*</span>
							</label>
							<input
								type="file"
								class="form-control @if ($errors->has('archivoDocumento')) is-invalid @elseif($archivoDocumento) is-valid @endif"
								id="archivoDocumento"
								accept=".pdf,.png,.jpg,.jpeg"
								wire:model="archivoDocumento"
							/>
							@error('archivoDocumento')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror

							@if($archivoDocumento)
								<div class="mt-2 text-muted">
									<i class="ki-outline ki-file fs-3 me-1"></i>
									<span>{{ $archivoDocumento->getClientOriginalName() }}</span>
									<span class="text-muted ms-1">({{ number_format($archivoDocumento->getSize() / 1024, 2) }} KB)</span>
								</div>
							@elseif($modoModal == 2 && $modeloDocumento && $modeloDocumento->ruta_documento)
								<div class="mt-2 text-muted">
									<i class="ki-outline ki-file-check fs-3 me-1 text-success"></i>
									<span>Archivo actual: {{ basename($modeloDocumento->ruta_documento) }}</span>
								</div>
							@endif

							<div wire:loading wire:target="archivoDocumento" class="mt-2 text-primary">
								<span class="spinner-border spinner-border-sm me-1"></span>
								Cargando archivo...
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
						<span wire:loading.remove wire:target="guardarDocumento">
							<i class="ki-outline ki-check-circle fs-3"></i>
							Guardar
						</span>
						<span wire:loading wire:target="guardarDocumento">
							Procesando...
							<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
						</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
