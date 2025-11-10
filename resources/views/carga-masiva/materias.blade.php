@extends('layouts.app')

@section('title', 'Carga Masiva de Materias')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-book me-2"></i>Carga Masiva de Materias
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
                            <div class="card mb-4 border-left-success">
                                <div class="card-body">
                                    <h5 class="card-title text-success">
                                        <i class="fas fa-info-circle me-2"></i>Instrucciones
                                    </h5>
                                    <ol class="mb-0">
                                        <li class="mb-2">Descargue la plantilla de ejemplo haciendo clic en el botón "Descargar Plantilla"</li>
                                        <li class="mb-2">Complete los datos de las materias en el archivo Excel o CSV</li>
                                        <li class="mb-2">Marque con "X" o "1" las carreras a las que pertenece cada materia</li>
                                        <li class="mb-2">Si desea asignar al período activo, marque la columna correspondiente</li>
                                        <li class="mb-0">Guarde el archivo y súbalo usando el formulario de carga</li>
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
                                        <li><code>sigla</code> - Código de la materia (máx. 6 caracteres)</li>
                                        <li><code>nombre</code> - Nombre completo (máx. 50 caracteres)</li>
                                        <li><code>nivel</code> - Nivel de la materia (número 1-10)</li>
                                    </ul>
                                    <p><strong>Columnas opcionales:</strong></p>
                                    <ul class="mb-3">
                                        <li><code>asignar_periodo_activo</code> - Marcar con X o 1</li>
                                    </ul>
                                    <p><strong>Columnas de carreras:</strong></p>
                                    <ul class="mb-0">
                                        @foreach($carreras as $carrera)
                                            <li><code>{{ $carrera->cod }}</code> - {{ $carrera->nombre }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de descarga de plantilla -->
                    <div class="d-flex justify-content-center mb-4">
                        <a href="{{ route('carga-masiva.materias.plantilla') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-download me-2"></i>Descargar Plantilla de Ejemplo
                        </a>
                    </div>

                    <hr>

                    <!-- Formulario de carga -->
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <form action="{{ route('carga-masiva.materias.store') }}" method="POST" enctype="multipart/form-data">
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
                                        <li>Las <strong>siglas</strong> deben ser únicas y se convertirán a MAYÚSCULAS</li>
                                        <li>El <strong>nivel</strong> debe ser un número entre 1 y 10</li>
                                        <li>Marque con "X" o "1" las carreras a las que pertenece cada materia</li>
                                        <li>Si marca "asignar_periodo_activo", la materia se registrará en el período académico actual</li>
                                        <li>Las materias duplicadas (misma sigla) serán omitidas</li>
                                    </ul>
                                </div>

                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-upload me-2"></i>Cargar Materias
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
                                    <th>nombre</th>
                                    <th>nivel</th>
                                    <th>asignar_periodo_activo</th>
                                    @foreach($carreras->take(4) as $carrera)
                                        <th>{{ $carrera->cod }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>INF220</td>
                                    <td>Base de Datos I</td>
                                    <td>2</td>
                                    <td>X</td>
                                    <td>1</td>
                                    <td>1</td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>MAT101</td>
                                    <td>Cálculo I</td>
                                    <td>1</td>
                                    <td>X</td>
                                    <td>1</td>
                                    <td></td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>PRG110</td>
                                    <td>Programación I</td>
                                    <td>1</td>
                                    <td>X</td>
                                    <td>1</td>
                                    <td>1</td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> Una materia puede pertenecer a múltiples carreras. Simplemente marque todas las columnas de carreras que correspondan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
