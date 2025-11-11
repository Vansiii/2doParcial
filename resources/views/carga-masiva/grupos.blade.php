@extends('layouts.app')

@section('title', 'Carga Masiva de Grupos')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>Carga Masiva de Grupos
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('resultados'))
                        @php $resultados = session('resultados'); @endphp
                        
                        @if($resultados['fallidos'] > 0)
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h6>
                                <ul class="mb-0">
                                    @foreach($resultados['errores'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    @endif

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

                    <!-- Instrucciones -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 border-left-warning">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">
                                        <i class="fas fa-info-circle me-2"></i>Instrucciones
                                    </h5>
                                    <ol class="mb-0">
                                        <li class="mb-2">Descargue la plantilla de ejemplo haciendo clic en el botón "Descargar Plantilla"</li>
                                        <li class="mb-2">Complete los datos de los grupos en el archivo Excel o CSV</li>
                                        <li class="mb-2">Especifique la gestión y período académico para cada grupo</li>
                                        <li class="mb-2">Guarde el archivo y súbalo usando el formulario de carga</li>
                                        <li class="mb-0">Después asigne materias y docentes usando "Gestionar Grupos"</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4 border-left-info">
                                <div class="card-body">
                                    <h5 class="card-title text-info">
                                        <i class="fas fa-table me-2"></i>Formato del Archivo
                                    </h5>
                                    <p><strong>Columnas requeridas:</strong></p>
                                    <ul class="mb-3">
                                        <li><code>sigla</code> - Código del grupo (máx. 3 caracteres)</li>
                                        <li><code>periodo_gestion</code> - Año del período (ej: 2025)</li>
                                        <li><code>periodo_numero</code> - Número del período (1 o 2)</li>
                                    </ul>
                                    <p><strong>Períodos disponibles:</strong></p>
                                    <ul class="mb-0">
                                        @foreach($periodos->take(5) as $periodo)
                                            <li>
                                                <span class="badge {{ $periodo->activo ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $periodo->gestion }}/{{ $periodo->periodo }}
                                                </span>
                                                @if($periodo->activo) <small class="text-success">(Activo)</small> @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de descarga de plantilla -->
                    <div class="d-flex justify-content-center mb-4">
                        <a href="{{ route('carga-masiva.grupos.plantilla') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-download me-2"></i>Descargar Plantilla de Ejemplo
                        </a>
                    </div>

                    <hr>

                    <!-- Formulario de carga -->
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <form action="{{ route('carga-masiva.grupos.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="mb-4">
                                    <label for="archivo" class="form-label">
                                        <i class="fas fa-file-excel me-2"></i>Seleccione el archivo Excel o CSV
                                    </label>
                                    <input type="file" 
                                           class="form-control @error('archivo') is-invalid @enderror" 
                                           id="archivo" 
                                           name="archivo" 
                                           accept=".xlsx,.xls,.csv"
                                           required>
                                    @error('archivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Formatos permitidos: Excel (.xlsx, .xls) o CSV (.csv). Tamaño máximo: 10MB
                                    </small>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Importante:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Las <strong>siglas</strong> deben ser únicas dentro del mismo período (máx. 3 caracteres)</li>
                                        <li>El <strong>periodo_gestion</strong> debe ser un año válido (ej: 2025)</li>
                                        <li>El <strong>periodo_numero</strong> debe ser <code>1</code> o <code>2</code></li>
                                        <li>El período académico especificado debe existir en el sistema</li>
                                        <li>Los grupos duplicados (misma sigla en el mismo período) serán omitidos</li>
                                        <li>Esta carga solo crea los grupos. Las materias y docentes se asignan después</li>
                                    </ul>
                                </div>

                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="submit" class="btn btn-warning btn-lg text-dark">
                                        <i class="fas fa-upload me-2"></i>Cargar Grupos
                                    </button>
                                    <a href="{{ route('carga-masiva.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-arrow-left me-2"></i>Volver
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de ayuda adicional -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Ejemplo de Archivo
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Su archivo debe tener este formato:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>sigla</th>
                                    <th>periodo_gestion</th>
                                    <th>periodo_numero</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>F1</td>
                                    <td>2025</td>
                                    <td>2</td>
                                </tr>
                                <tr>
                                    <td>SZ</td>
                                    <td>2025</td>
                                    <td>2</td>
                                </tr>
                                <tr>
                                    <td>CI</td>
                                    <td>2025</td>
                                    <td>2</td>
                                </tr>
                                <tr>
                                    <td>I2</td>
                                    <td>2025</td>
                                    <td>2</td>
                                </tr>
                                <tr>
                                    <td>SF</td>
                                    <td>2025</td>
                                    <td>2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> Puede crear múltiples grupos para el mismo período. Después de la carga, vaya a "Gestión de Grupos" para asignar materias y docentes a cada grupo.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
