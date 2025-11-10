@extends('layouts.app')

@section('title', 'Carga Masiva de Usuarios')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-upload me-2"></i>Carga Masiva de Usuarios
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
                            <div class="card mb-4 border-left-primary">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-info-circle me-2"></i>Instrucciones
                                    </h5>
                                    <ol class="mb-0">
                                        <li class="mb-2">Descargue la plantilla de ejemplo haciendo clic en el botón "Descargar Plantilla"</li>
                                        <li class="mb-2">Complete los datos de los usuarios en el archivo Excel o CSV</li>
                                        <li class="mb-2">Marque con "X" o "1" los roles que desea asignar a cada usuario</li>
                                        <li class="mb-2">Guarde el archivo y súbalo usando el formulario de carga</li>
                                        <li class="mb-0">Revise los resultados de la carga y corrija errores si es necesario</li>
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
                                        <li><code>nombre</code> - Nombre completo (máx. 40 caracteres)</li>
                                        <li><code>ci</code> - Cédula de Identidad (número)</li>
                                        <li><code>correo</code> - Email (máx. 40 caracteres)</li>
                                        <li><code>telefono</code> - Teléfono (número, mín. 8 dígitos)</li>
                                    </ul>
                                    <p><strong>Columnas de roles (opcional):</strong></p>
                                    <ul class="mb-0">
                                        <li><code>docente</code> - Marcar con X o 1</li>
                                        <li><code>coordinador</code> - Marcar con X o 1</li>
                                        <li><code>autoridad</code> - Marcar con X o 1</li>
                                        <li><code>administrador</code> - Marcar con X o 1</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de descarga de plantilla -->
                    <div class="d-flex justify-content-center mb-4">
                        <a href="{{ route('carga-masiva.usuarios.plantilla') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-download me-2"></i>Descargar Plantilla de Ejemplo
                        </a>
                    </div>

                    <hr>

                    <!-- Formulario de carga -->
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <form action="{{ route('carga-masiva.usuarios.store') }}" method="POST" enctype="multipart/form-data">
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
                                        <li><strong>El CÓDIGO se genera automáticamente</strong> - No es necesario incluirlo en el archivo</li>
                                        <li>La contraseña predeterminada será el número de CI de cada usuario</li>
                                        <li>Los usuarios duplicados (mismo CI o correo) serán omitidos</li>
                                        <li>Al menos un rol debe estar marcado para cada usuario</li>
                                        <li>Verifique que los datos sean correctos antes de cargar</li>
                                    </ul>
                                </div>

                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-upload me-2"></i>Cargar Usuarios
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
                                    <th>nombre</th>
                                    <th>ci</th>
                                    <th>correo</th>
                                    <th>telefono</th>
                                    <th>coordinador</th>
                                    <th>autoridad</th>
                                    <th>docente</th>
                                    <th>administrador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Juan Pérez García</td>
                                    <td>6306497</td>
                                    <td>juan.perez@uagrm.edu.bo</td>
                                    <td>70123456</td>
                                    <td></td>
                                    <td>X</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>María López</td>
                                    <td>4910167</td>
                                    <td>maria.lopez@uagrm.edu.bo</td>
                                    <td>71234567</td>
                                    <td>X</td>
                                    <td>X</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Carlos Gómez</td>
                                    <td>5289856</td>
                                    <td>carlos.gomez@uagrm.edu.bo</td>
                                    <td>72345678</td>
                                    <td></td>
                                    <td>X</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> Puede marcar múltiples roles para un mismo usuario (por ejemplo, un usuario puede ser tanto Coordinador como Autoridad).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
