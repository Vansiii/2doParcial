@extends('layouts.app')

@section('title', 'Detalle del Semestre')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Detalle del Semestre
                    </h4>
                    <a href="{{ route('semestres.edit', $semestre->id) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Abreviatura:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-info fs-5">
                                <i class="fas fa-calendar me-1"></i>{{ $semestre->abreviatura }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Periodo:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $semestre->periodo }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Fecha de Inicio:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($semestre->fechaini)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Fecha de Fin:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($semestre->fechafin)->format('d/m/Y') }}
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">
                        <i class="fas fa-book me-2 text-primary"></i>Materias Asociadas
                    </h5>
                    @if($semestre->materias->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($semestre->materias as $materia)
                                <li class="list-group-item px-0">
                                    <i class="fas fa-book me-2 text-success"></i>
                                    <strong>{{ $materia->sigla }}</strong> - {{ $materia->nombre }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay materias asociadas a este semestre
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('semestres.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
