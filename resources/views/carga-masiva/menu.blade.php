@extends('layouts.app')

@section('title', 'Carga Masiva de Datos')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-database me-2"></i>Carga Masiva de Datos
                    </h4>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h5 class="text-muted">Seleccione el tipo de datos que desea cargar masivamente</h5>
                    </div>

                    <div class="row g-4">
                        <!-- Opción: Usuarios -->
                        <div class="col-md-6">
                            <div class="card h-100 border-primary shadow-sm hover-shadow">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-users fa-4x text-primary"></i>
                                    </div>
                                    <h4 class="card-title mb-3">Usuarios</h4>
                                    <p class="card-text text-muted mb-4">
                                        Cargue múltiples usuarios con sus roles (Docentes, Coordinadores, Autoridades, Administradores)
                                    </p>
                                    <ul class="list-unstyled text-start mb-4">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Asignación automática de roles
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Validación de CI y correos
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Código generado automáticamente
                                        </li>
                                    </ul>
                                    <a href="{{ route('carga-masiva.usuarios') }}" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-upload me-2"></i>Cargar Usuarios
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Opción: Materias -->
                        <div class="col-md-6">
                            <div class="card h-100 border-success shadow-sm hover-shadow">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-book fa-4x text-success"></i>
                                    </div>
                                    <h4 class="card-title mb-3">Materias</h4>
                                    <p class="card-text text-muted mb-4">
                                        Cargue materias y asígnelas automáticamente a carreras y al período académico activo
                                    </p>
                                    <ul class="list-unstyled text-start mb-4">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Asignación a múltiples carreras
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Registro en período activo
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Validación de siglas y niveles
                                        </li>
                                    </ul>
                                    <a href="{{ route('carga-masiva.materias') }}" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-upload me-2"></i>Cargar Materias
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>Información Importante
                                </h6>
                                <ul class="mb-0">
                                    <li>Los archivos deben estar en formato Excel (.xlsx, .xls) o CSV (.csv)</li>
                                    <li>Cada opción tiene su propia plantilla con el formato correcto</li>
                                    <li>Los registros duplicados serán omitidos automáticamente</li>
                                    <li>Se generará un reporte detallado de los resultados de la carga</li>
                                    <li>Todas las operaciones quedan registradas en la bitácora del sistema</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
}
</style>
@endsection
