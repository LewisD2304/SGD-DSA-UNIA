<div wire:ignore.self class="modal fade" id="modal-responder-documento" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered mw-900px">
		<div class="modal-content">

			<div class="modal-header">
				<h3 class="fw-bold my-0">
					Responder documento
				</h3>

				<div
					class="btn btn-icon btn-sm btn-active-icon-primary icon-rotate-custom"
					data-bs-dismiss="modal"
					aria-label="Close"
				>
					<i class="ki-outline ki-cross fs-1"></i>
				</div>
			</div>

			<form autocomplete="off" novalidate class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit="guardarRespuesta">

				<div class="modal-body px-5">
					<div class="d-flex flex-column px-5 px-lg-10">

						<!-- INFORMACIÓN DEL DOCUMENTO (SOLO LECTURA) -->
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
										id="numeroDocumentoRO"
										value="{{ $numeroDocumento }}"
										readonly
										disabled
									/>
									<label for="numeroDocumentoRO">Número documento</label>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="folioDocumentoRO"
										value="{{ $folioDocumento }}"
										readonly
										disabled
									/>
									<label for="folioDocumentoRO">Folio</label>
								</div>
							</div>
						</div>

						<!-- Asunto -->
						<div class="mb-3">
							<div class="form-floating">
								<textarea
									class="form-control bg-light"
									id="asuntoDocumentoRO"
									readonly
									disabled
									style="height: 80px"
								>{{ $asuntoDocumento }}</textarea>
								<label for="asuntoDocumentoRO">Asunto</label>
							</div>
						</div>

						<!-- Observación -->
						<div class="mb-3">
							<div class="form-floating">
								<textarea
									class="form-control bg-light"
									id="observacionDocumentoRO"
									readonly
									disabled
									style="height: 80px"
								>{{ $observacionDocumento }}</textarea>
								<label for="observacionDocumentoRO">Observación</label>
							</div>
						</div>

						<!-- Tipo de Documento, Remitente, Oficina, Destino -->
						<div class="row g-3 mb-3">
							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="tipoDocumentoRO"
										value="{{ $tipoDocumentoNombre }}"
										readonly
										disabled
									/>
									<label for="tipoDocumentoRO">Tipo de Documento</label>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="areaRemitenteRO"
										value="{{ $areaRemitenteNombre }}"
										readonly
										disabled
									/>
									<label for="areaRemitenteRO">Remitente</label>
								</div>
							</div>
						</div>

						<div class="row g-3 mb-3">
							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="oficinaNombreRO"
										value="{{ $oficinaNombre }}"
										readonly
										disabled
									/>
									<label for="oficinaNombreRO">Oficina</label>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-floating">
									<input
										type="text"
										class="form-control bg-light"
										id="areaDestinoRO"
										value="{{ $areaDestinoNombre }}"
										readonly
										disabled
									/>
									<label for="areaDestinoRO">Destino</label>
								</div>
							</div>
						</div>

						<!-- Archivos existentes (solo visualización) -->
						@if(!empty($archivosExistentes) && count($archivosExistentes) > 0)
						<div class="mb-3">
							<label class="form-label fw-bold">Archivos del documento</label>
							<div class="d-flex flex-column gap-2">
								@foreach($archivosExistentes as $archivo)
								<div class="d-flex align-items-center p-3 bg-light-primary rounded">
									<i class="ki-outline ki-file fs-2x text-primary me-3"></i>
									<div class="flex-grow-1">
										<div class="fw-bold text-gray-800">{{ $archivo->nombre_archivo }}</div>
										<div class="text-muted fs-7">{{ number_format($archivo->tamano_archivo / 1024, 2) }} KB</div>
									</div>
									<a
										href="{{ route('archivo.ver', ['path' => $archivo->ruta_archivo]) }}"
										target="_blank"
										class="btn btn-sm btn-icon btn-light-primary"
										title="Descargar archivo"
									>
										<i class="ki-outline ki-download fs-3"></i>
									</a>
								</div>
								@endforeach
							</div>
						</div>
						@endif

						<!-- SEPARADOR -->
						<div class="separator separator-dashed my-5"></div>

						<!-- SECCIÓN DE RESPUESTA -->
						<div class="fw-bold text-dark mb-3">
							<i class="ki-outline ki-send me-2"></i> Respuesta
						</div>

						<!-- Enviar a (área de destino) -->
						<div class="mb-3">
							<label for="idAreaRespuesta" class="form-label required">
								Enviar a
							</label>
							<div wire:ignore>
								<select
									class="form-select"
									id="idAreaRespuesta"
									wire:model="idAreaRespuesta"
								>
									<option value="">Seleccione un área de destino</option>
									@foreach($areas as $area)
										<option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
									@endforeach
								</select>
							</div>
							@error('idAreaRespuesta')
								<div class="text-danger fs-7 mt-1">{{ $message }}</div>
							@enderror
						</div>

						<!-- Comentario -->
						<div class="mb-3">
							<div class="form-floating">
								<textarea
									class="form-control @error('comentarioRespuesta') is-invalid @enderror"
									id="comentarioRespuesta"
									placeholder="Comentario"
									wire:model="comentarioRespuesta"
									maxlength="500"
									style="height: 100px"
								></textarea>
								<label for="comentarioRespuesta">Comentario (Opcional)</label>
								@error('comentarioRespuesta')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>

						<!-- Adjuntar archivos -->
						<div class="mb-3">
							<label class="form-label">Adjuntar archivos de respuesta (Opcional)</label>
							<input
								type="file"
								class="form-control @error('archivosRespuesta.*') is-invalid @enderror"
								wire:model="archivosRespuesta"
								multiple
								accept=".pdf,.png,.jpg,.jpeg"
							/>
							<div class="form-text">
								Máximo 10 archivos. Formatos permitidos: PDF, PNG, JPEG. Tamaño máximo por archivo: 10 MB.
							</div>
							@error('archivosRespuesta')
								<div class="text-danger fs-7 mt-1">{{ $message }}</div>
							@enderror
							@error('archivosRespuesta.*')
								<div class="text-danger fs-7 mt-1">{{ $message }}</div>
							@enderror
						</div>

						<!-- Lista de archivos seleccionados -->
						@if(!empty($archivosRespuesta) && count($archivosRespuesta) > 0)
						<div class="mb-3">
							<label class="form-label fw-bold">Archivos seleccionados</label>
							<div class="d-flex flex-column gap-2">
								@foreach($archivosRespuesta as $index => $archivo)
								<div class="d-flex align-items-center p-3 bg-light rounded">
									<i class="ki-outline ki-file fs-2x text-gray-600 me-3"></i>
									<div class="flex-grow-1">
										<div class="fw-bold text-gray-800">{{ $archivo->getClientOriginalName() }}</div>
										<div class="text-muted fs-7">{{ number_format($archivo->getSize() / 1024, 2) }} KB</div>
									</div>
									<button
										type="button"
										wire:click="eliminarArchivoRespuesta({{ $index }})"
										class="btn btn-sm btn-icon btn-light-danger"
										title="Eliminar archivo"
									>
										<i class="ki-outline ki-trash fs-3"></i>
									</button>
								</div>
								@endforeach
							</div>
						</div>
						@endif

					</div>
				</div>

				<div class="modal-footer flex-center">
					<button
						type="button"
						class="btn btn-light me-3"
						data-bs-dismiss="modal"
						wire:click="limpiarModalResponder"
					>
						Cancelar
					</button>

					<button
						type="submit"
						class="btn btn-primary"
						wire:loading.attr="disabled"
					>
						<span wire:loading.remove wire:target="guardarRespuesta">
							<i class="ki-outline ki-check fs-2"></i>
							Enviar respuesta
						</span>
						<span wire:loading wire:target="guardarRespuesta">
							<span class="spinner-border spinner-border-sm align-middle me-2"></span>
							Procesando...
						</span>
					</button>
				</div>

			</form>

		</div>
	</div>
</div>

@script
<script>
	// Inicialización de Select2 para idAreaRespuesta
	$(document).ready(function() {
		$('#idAreaRespuesta').select2({
			placeholder: 'Seleccione un área de destino',
			allowClear: true,
			dropdownParent: $('#modal-responder-documento'),
			width: '100%'
		});

		$('#idAreaRespuesta').on('change', function() {
			@this.set('idAreaRespuesta', $(this).val());
		});
	});

	// Escuchar evento para abrir modal
	window.addEventListener('abrir_modal_responder_documento', () => {
		$('#modal-responder-documento').modal('show');

		// Reinicializar Select2 cuando se abre el modal
		setTimeout(() => {
			$('#idAreaRespuesta').select2({
				placeholder: 'Seleccione un área de destino',
				allowClear: true,
				dropdownParent: $('#modal-responder-documento'),
				width: '100%'
			});
		}, 300);
	});

	// Escuchar evento para cerrar modal
	window.addEventListener('cerrar_modal_responder_documento', () => {
		$('#modal-responder-documento').modal('hide');
	});

	// Escuchar errores de validación
	window.addEventListener('errores_validacion_respuesta', () => {
		// El modal permanece abierto para que el usuario corrija los errores
	});
</script>
@endscript
