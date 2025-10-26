@extends('layouts.app')

@section('title', 'Editar Semestre')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Semestre
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('semestres.update', $semestre->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="abreviatura" class="form-label">
                                Abreviatura <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('abreviatura') is-invalid @enderror" 
                                   id="abreviatura" 
                                   name="abreviatura" 
                                   value="{{ old('abreviatura', $semestre->abreviatura) }}"
                                   maxlength="10"
                                   required>
                            @error('abreviatura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
