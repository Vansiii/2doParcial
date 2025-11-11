@extends('layouts.app')

@section('title', 'Bitácora del Sistema')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-history"></i> Bitácora del Sistema
                        <span class="badge bg-light text-primary ms-2" id="badge-actualizado">
                            <i class="fas fa-sync-alt fa-spin"></i> Actualizando...
                        </span>
                    </h4>
                    <div>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalLimpiar">
                            <i class="fas fa-trash-alt"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportarBitacora()">
                            <i class="fas fa-download"></i> Exportar CSV
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Mensajes -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Filtros -->
                <form method="GET" action="{{ route('bitacora.index') }}" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label for="fecha_desde" class="form-label">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_hasta" class="form-label">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <select class="form-select form-select-sm" id="usuario" name="usuario">
                            <option value="">Todos</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" {{ request('usuario') == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="accion" class="form-label">Acción</label>
                        <input type="text" class="form-control form-control-sm" id="accion" name="accion" value="{{ request('accion') }}" placeholder="Buscar...">
                    </div>
                    <div class="col-md-2">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select form-select-sm" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="exitoso" {{ request('estado') == 'exitoso' ? 'selected' : '' }}>Exitoso</option>
                            <option value="fallido" {{ request('estado') == 'fallido' ? 'selected' : '' }}>Fallido</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </form>

                <!-- Estadísticas -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center py-2">
                                <h6 class="mb-1 text-muted">Total Registros</h6>
                                <h4 class="mb-0 text-primary"><i class="fas fa-clipboard-list me-1"></i>{{ $registros->total() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center py-2">
                                <h6 class="mb-1 text-muted">Hoy</h6>
                                <h4 class="mb-0 text-info"><i class="fas fa-calendar-day me-1"></i>{{ \App\Models\Bitacora::whereDate('fecha', today())->count() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center py-2">
                                <h6 class="mb-1 text-muted">Exitosos</h6>
                                <h4 class="mb-0 text-success"><i class="fas fa-check-circle me-1"></i>{{ \App\Models\Bitacora::where('estado', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body text-center py-2">
                                <h6 class="mb-1 text-muted">Fallidos</h6>
                                <h4 class="mb-0 text-danger"><i class="fas fa-times-circle me-1"></i>{{ \App\Models\Bitacora::where('estado', false)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Bitácora -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th width="150">Fecha/Hora</th>
                                <th width="180">Usuario</th>
                                <th width="120">IP</th>
                                <th width="200">Acción</th>
                                <th width="80" class="text-center">Estado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-bitacora">
                            @forelse($registros as $registro)
                                <tr data-registro-id="{{ $registro->id }}" class="registro-existente">
                                    <td class="small">{{ $registro->fecha->format('d/m/Y H:i:s') }}</td>
                                    <td class="small">{{ $registro->usuario ? $registro->usuario->nombre : 'Sistema' }}</td>
                                    <td class="small text-muted">{{ $registro->ip }}</td>
                                    <td class="small"><strong>{{ $registro->accion }}</strong></td>
                                    <td class="text-center">
                                        @if($registro->estado)
                                            <span class="badge bg-success">Exitoso</span>
                                        @else
                                            <span class="badge bg-danger">Fallido</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $registro->detalle }}</td>
                                </tr>
                            @empty
                                <tr id="sin-registros">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay registros en la bitácora</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $registros->firstItem() ?? 0 }} a {{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }} registros
                    </div>
                    <div>
                        {{ $registros->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Limpiar Bitácora -->
<div class="modal fade" id="modalLimpiar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Limpiar Bitácora</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('bitacora.limpiar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Esta acción eliminará registros antiguos de la bitácora y no se puede deshacer.
                    </div>
                    <div class="mb-3">
                        <label for="dias" class="form-label">Eliminar registros con más de:</label>
                        <select class="form-select" id="dias" name="dias" required>
                            <option value="30">30 días</option>
                            <option value="60">60 días</option>
                            <option value="90" selected>90 días</option>
                            <option value="180">180 días</option>
                            <option value="365">1 año</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Limpiar Bitácora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let ultimoId = {{ $registros->first()->id ?? 0 }};
let intervalId = null;
let actualizacionActiva = true;

// Función para obtener nuevos registros
function obtenerNuevosRegistros() {
    if (!actualizacionActiva) return;

    fetch(`{{ route('bitacora.obtener-nuevos') }}?ultimo_id=${ultimoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.registros && data.registros.length > 0) {
                // Actualizar el último ID
                ultimoId = data.ultimo_id;
                
                // Agregar nuevos registros al inicio de la tabla
                const tbody = document.getElementById('tabla-bitacora');
                const sinRegistros = document.getElementById('sin-registros');
                
                if (sinRegistros) {
                    sinRegistros.remove();
                }
                
                data.registros.forEach(registro => {
                    // Verificar que no exista ya
                    if (!document.querySelector(`tr[data-registro-id="${registro.id}"]`)) {
                        const row = crearFilaRegistro(registro);
                        tbody.insertBefore(row, tbody.firstChild);
                        
                        // Animación de aparición
                        setTimeout(() => {
                            row.classList.add('show');
                        }, 10);
                        
                        // Reproducir sonido de notificación (opcional)
                        reproducirNotificacion();
                    }
                });
                
                // Actualizar badge
                actualizarBadge('success', 'Actualizado');
            } else {
                actualizarBadge('secondary', 'Sin cambios');
            }
        })
        .catch(error => {
            console.error('Error al obtener registros:', error);
            actualizarBadge('danger', 'Error');
        });
}

// Crear fila de registro
function crearFilaRegistro(registro) {
    const row = document.createElement('tr');
    row.setAttribute('data-registro-id', registro.id);
    row.className = 'registro-nuevo';
    row.style.opacity = '0';
    row.style.transition = 'opacity 0.5s ease-in';
    
    row.innerHTML = `
        <td class="small">${registro.fecha}</td>
        <td class="small">${registro.usuario}</td>
        <td class="small text-muted">${registro.ip}</td>
        <td class="small"><strong>${registro.accion}</strong></td>
        <td class="text-center">
            <span class="badge bg-${registro.badge_class}">${registro.estado_texto}</span>
        </td>
        <td class="small text-muted">${registro.detalle}</td>
    `;
    
    return row;
}

// Actualizar badge de estado
function actualizarBadge(tipo, texto) {
    const badge = document.getElementById('badge-actualizado');
    
    if (tipo === 'success') {
        badge.className = 'badge bg-success ms-2';
        badge.innerHTML = '<i class="fas fa-check"></i> ' + texto;
    } else if (tipo === 'danger') {
        badge.className = 'badge bg-danger ms-2';
        badge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + texto;
    } else {
        badge.className = 'badge bg-secondary ms-2';
        badge.innerHTML = '<i class="fas fa-clock"></i> ' + texto;
    }
    
    // Volver a "actualizando" después de 2 segundos
    if (tipo !== 'danger') {
        setTimeout(() => {
            badge.className = 'badge bg-light text-primary ms-2';
            badge.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Actualizando...';
        }, 2000);
    }
}

// Reproducir sonido de notificación (opcional)
function reproducirNotificacion() {
    // Puedes agregar un audio si lo deseas
    // const audio = new Audio('/sounds/notification.mp3');
    // audio.play();
}

// Exportar bitácora
function exportarBitacora() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('bitacora.exportar') }}?${params.toString()}`;
}

// Pausar/Reanudar actualización
document.addEventListener('keydown', function(e) {
    if (e.key === 'p' && e.ctrlKey) {
        e.preventDefault();
        actualizacionActiva = !actualizacionActiva;
        
        const badge = document.getElementById('badge-actualizado');
        if (actualizacionActiva) {
            badge.className = 'badge bg-light text-primary ms-2';
            badge.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Actualizando...';
        } else {
            badge.className = 'badge bg-warning text-dark ms-2';
            badge.innerHTML = '<i class="fas fa-pause"></i> Pausado (Ctrl+P para reanudar)';
        }
    }
});

// Iniciar actualización automática cada 5 segundos
intervalId = setInterval(obtenerNuevosRegistros, 5000);

// Limpiar intervalo al salir
window.addEventListener('beforeunload', function() {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

// CSS para animaciones
const style = document.createElement('style');
style.textContent = `
    .registro-nuevo.show {
        opacity: 1 !important;
        background-color: #cfe2ff;
    }
    .registro-nuevo {
        transition: background-color 3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endsection
