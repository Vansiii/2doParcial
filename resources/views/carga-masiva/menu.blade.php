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
                        <div class="col-md-3">
                            <div class="card h-100 border-primary shadow-sm hover-shadow">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-users fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Usuarios</h5>
                                    <p class="card-text text-muted mb-3 small">
                                        Cargue múltiples usuarios con sus roles
                                    </p>
                                    <a href="{{ route('carga-masiva.usuarios') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-upload me-2"></i>Cargar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Opción: Materias -->
                        <div class="col-md-3">
                            <div class="card h-100 border-success shadow-sm hover-shadow">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-book fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Materias</h5>
                                    <p class="card-text text-muted mb-3 small">
                                        Cargue materias y asígnelas a carreras
                                    </p>
                                    <a href="{{ route('carga-masiva.materias') }}" class="btn btn-success w-100">
                                        <i class="fas fa-upload me-2"></i>Cargar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Opción: Grupos -->
                        <div class="col-md-3">
                            <div class="card h-100 border-warning shadow-sm hover-shadow">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-layer-group fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Grupos</h5>
                                    <p class="card-text text-muted mb-3 small">
                                        Cargue múltiples grupos por período
                                    </p>
                                    <a href="{{ route('carga-masiva.grupos') }}" class="btn btn-warning w-100 text-dark">
                                        <i class="fas fa-upload me-2"></i>Cargar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Opción: Horarios -->
                        <div class="col-md-3">
                            <div class="card h-100 border-info shadow-sm hover-shadow">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-clock fa-3x text-info"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Horarios</h5>
                                    <p class="card-text text-muted mb-3 small">
                                        Cargue horarios de materias por grupo
                                    </p>
                                    <a href="{{ route('carga-masiva.horarios') }}" class="btn btn-info w-100 text-white">
                                        <i class="fas fa-upload me-2"></i>Cargar
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
