@extends('layouts.app')

@section('title', 'Registrar Semestre')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Registrar Nuevo Período Académico
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('semestres.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="gestion" class="form-label">
                                    Gestión (Año) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('gestion') is-invalid @enderror" 
                                       id="gestion" 
                                       name="gestion" 
                                       value="{{ old('gestion', date('Y')) }}"
                                       min="2020"
                                       max="2100"
                                       placeholder="Ej: 2024"
                                       required>
                                @error('gestion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Año de la gestión académica</small>
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
                                    <option value="1" {{ old('periodo') == 1 ? 'selected' : '' }}>1 (Primer Semestre)</option>
                                    <option value="2" {{ old('periodo') == 2 ? 'selected' : '' }}>2 (Segundo Semestre)</option>
                                </select>
                                @error('periodo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Semestre del año</small>
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
                                       value="{{ old('fechaini') }}"
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
                                       value="{{ old('fechafin') }}"
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
                                       {{ old('activo', true) ? 'checked' : '' }}>
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
                            La abreviatura se generará automáticamente como: <strong>[Período]-[Gestión]</strong>
                            <br>
                            <small>Ejemplo: 1-2024, 2-2024</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar
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
