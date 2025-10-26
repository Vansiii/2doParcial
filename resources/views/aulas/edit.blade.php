@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Aula
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('aulas.update', $aula->nroaula) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nroaula" class="form-label">Número de Aula</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="nroaula" 
                                       value="{{ $aula->nroaula }}"
                                       disabled>
                                <small class="text-muted">El número de aula no se puede modificar (es la clave primaria)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="id_modulo" class="form-label">
                                    Módulo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('id_modulo') is-invalid @enderror" 
                                        id="id_modulo" 
                                        name="id_modulo"
                                        required>
                                    <option value="">Seleccione un módulo</option>
                                    @foreach($modulos as $modulo)
                                        <option value="{{ $modulo->codigo }}" 
                                            {{ (old('id_modulo', $aula->id_modulo) == $modulo->codigo) ? 'selected' : '' }}>
                                            Código {{ $modulo->codigo }} - {{ $modulo->ubicacion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_modulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="piso" class="form-label">
                                    Piso <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('piso') is-invalid @enderror" 
                                        id="piso" 
                                        name="piso"
                                        required>
                                    <option value="">Seleccione el piso</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" 
                                            {{ (old('piso', $aula->piso) == $i) ? 'selected' : '' }}>
                                            Piso {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('piso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="capacidad" class="form-label">
                                    Capacidad <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('capacidad') is-invalid @enderror" 
                                       id="capacidad" 
                                       name="capacidad" 
                                       value="{{ old('capacidad', $aula->capacidad) }}"
                                       min="1"
                                       max="200"
                                       required>
                                @error('capacidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Número de personas (1-200)</small>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('aulas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Actualizar Aula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
