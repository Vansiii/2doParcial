@extends('layouts.app')

@section('title', 'Editar Semestre')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Período Académico
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('semestres.update', $semestre->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="gestion" class="form-label">
                                    Gestión (Año) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('gestion') is-invalid @enderror" 
                                       id="gestion" 
                                       name="gestion" 
                                       value="{{ old('gestion', $semestre->gestion) }}"
                                       min="2020"
                                       max="2100"
                                       required>
                                @error('gestion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="periodo" class="form-label">
                                    Período <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('periodo') is-invalid @enderror" 
                                        id="periodo" 
                                        name="periodo" 
                                        required>
                                    <option value="">Seleccione</option>
                                    <option value="1" {{ old('periodo', $semestre->periodo) == 1 ? 'selected' : '' }}>1 (Primer Semestre)</option>
                                    <option value="2" {{ old('periodo', $semestre->periodo) == 2 ? 'selected' : '' }}>2 (Segundo Semestre)</option>
                                </select>
                                @error('periodo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="fechaini" class="form-label">
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fechaini') is-invalid @enderror" 
                                       id="fechaini" 
                                       name="fechaini" 
                                       value="{{ old('fechaini', $semestre->fechaini) }}"
                                       required>
                                @error('fechaini')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="fechafin" class="form-label">
                                    Fecha de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fechafin') is-invalid @enderror" 
                                       id="fechafin" 
                                       name="fechafin" 
                                       value="{{ old('fechafin', $semestre->fechafin) }}"
                                       required>
                                @error('fechafin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="activo" 
                                       name="activo" 
                                       value="1"
                                       {{ old('activo', $semestre->activo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo">
                                    <strong>Marcar como período activo</strong>
                                </label>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Al marcar como activo, se desactivarán automáticamente todos los demás períodos
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            La abreviatura se actualizará automáticamente como: <strong>[Período]-[Gestión]</strong>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Actualizar
                            </button>
                            <a href="{{ route('semestres.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
