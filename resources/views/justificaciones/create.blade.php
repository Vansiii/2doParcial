@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-medical me-2"></i>Nueva Justificación
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Importante:</strong> Complete todos los campos y adjunte un documento de respaldo 
                        (certificado médico, comprobante, etc.) en formato PDF, JPG o PNG (máximo 5MB).
                    </div>

                    <form action="{{ route('justificaciones.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_inicio" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>Fecha Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                       id="fecha_inicio" 
                                       name="fecha_inicio" 
                                       value="{{ old('fecha_inicio') }}"
                                       required>
                                @error('fecha_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="fecha_fin" class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>Fecha Fin <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_fin') is-invalid @enderror" 
                                       id="fecha_fin" 
                                       name="fecha_fin" 
                                       value="{{ old('fecha_fin') }}"
                                       required>
                                @error('fecha_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="motivo" class="form-label">
                                <i class="fas fa-tag me-1"></i>Motivo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('motivo') is-invalid @enderror" 
                                    id="motivo" 
                                    name="motivo" 
                                    required>
                                <option value="">Seleccione un motivo...</option>
                                <option value="Enfermedad" {{ old('motivo') == 'Enfermedad' ? 'selected' : '' }}>
                                    Enfermedad
                                </option>
                                <option value="Cita Médica" {{ old('motivo') == 'Cita Médica' ? 'selected' : '' }}>
                                    Cita Médica
                                </option>
                                <option value="Evento Institucional" {{ old('motivo') == 'Evento Institucional' ? 'selected' : '' }}>
                                    Evento Institucional
                                </option>
                                <option value="Trámite Personal" {{ old('motivo') == 'Trámite Personal' ? 'selected' : '' }}>
                                    Trámite Personal
                                </option>
                                <option value="Emergencia Familiar" {{ old('motivo') == 'Emergencia Familiar' ? 'selected' : '' }}>
                                    Emergencia Familiar
                                </option>
                                <option value="Otro" {{ old('motivo') == 'Otro' ? 'selected' : '' }}>
                                    Otro
                                </option>
                            </select>
                            @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Descripción Detallada
                            </label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="4"
                                      placeholder="Describa detalladamente el motivo de su ausencia o retraso...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Opcional. Máximo 1000 caracteres.</small>
                        </div>

                        <div class="mb-4">
                            <label for="archivo" class="form-label">
                                <i class="fas fa-paperclip me-1"></i>Documento de Respaldo <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('archivo') is-invalid @enderror" 
                                   id="archivo" 
                                   name="archivo" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            @error('archivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Formatos permitidos: PDF, JPG, PNG. Tamaño máximo: 5MB.
                            </small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('justificaciones.mis-justificaciones') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Enviar Justificación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Validar que fecha_fin >= fecha_inicio
    document.getElementById('fecha_inicio').addEventListener('change', function() {
        document.getElementById('fecha_fin').min = this.value;
    });
</script>
@endsection
