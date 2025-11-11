@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-user-times me-2"></i>Gestionar Ausencias</h2>
                <a href="{{ route('asistencias.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtro de fecha -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-calendar-day me-2"></i>Seleccionar Fecha
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('asistencias.gestionar-ausencias') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha a revisar:</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha" 
                           name="fecha" 
                           value="{{ $fecha->format('Y-m-d') }}"
                           max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Buscar
                    </button>
                </div>
            </form>
            <div class="mt-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Mostrando horarios de: <strong>{{ $fecha->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</strong>
                </small>
            </div>
        </div>
    </div>

    <!-- Lista de horarios sin asistencia -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-exclamation-circle me-2"></i>
                Horarios sin Asistencia Marcada ({{ count($horariosSinAsistencia) }})
            </span>
            @if(count($horariosSinAsistencia) > 0)
                <button type="button" class="btn btn-warning btn-sm" onclick="marcarTodasAusencias()">
                    <i class="fas fa-check-double me-1"></i>Marcar Todas como Ausentes
                </button>
            @endif
        </div>
        <div class="card-body">
            @if(count($horariosSinAsistencia) > 0)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Los siguientes docentes <strong>NO han marcado asistencia</strong> en sus horarios del día seleccionado.
                    Puede marcarlos individualmente como ausentes o usar el botón para marcar todos a la vez.
                </div>

                <form id="form-ausencias-masivas" method="POST" action="{{ route('asistencias.marcar-ausencias-masivas') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">
                                        <input type="checkbox" class="form-check-input" id="select-all" 
                                               onchange="toggleSelectAll(this)">
                                    </th>
                                    <th style="width: 10%;">Horario</th>
                                    <th style="width: 25%;">Docente</th>
                                    <th style="width: 15%;">Grupo</th>
                                    <th style="width: 20%;">Materia</th>
                                    <th style="width: 10%;">Aula</th>
                                    <th style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($horariosSinAsistencia as $index => $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input ausencia-checkbox" 
                                               name="ausencias[{{ $index }}][selected]"
                                               data-index="{{ $index }}"
                                               onchange="toggleCheckbox(this, {{ $index }})">
                                        <input type="hidden" name="ausencias[{{ $index }}][id_horario]" value="{{ $item['horario']->id }}">
                                        <input type="hidden" name="ausencias[{{ $index }}][id_docente]" value="{{ $item['docente']->id }}">
                                        <input type="hidden" name="ausencias[{{ $index }}][fecha]" value="{{ $item['fecha'] }}">
                                    </td>
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($item['horario']->horaini)->format('H:i') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($item['horario']->horafin)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-circle me-1"></i>
                                        {{ $item['docente']->nombre }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $item['horario']->grupo->sigla }}</span>
                                    </td>
                                    <td>
                                        @if($item['horario']->materias->isNotEmpty())
                                            {{ $item['horario']->materias->first()->nombre }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-door-open me-1"></i>
                                        {{ $item['horario']->aula->nroaula ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <form method="POST" 
                                              action="{{ route('asistencias.marcar-ausencia') }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('¿Está seguro de marcar como AUSENTE a {{ $item['docente']->nombre }}?')">
                                            @csrf
                                            <input type="hidden" name="id_horario" value="{{ $item['horario']->id }}">
                                            <input type="hidden" name="id_docente" value="{{ $item['docente']->id }}">
                                            <input type="hidden" name="fecha" value="{{ $item['fecha'] }}">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-user-times me-1"></i>Ausente
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Nota:</strong> Al marcar como ausente, se creará un registro permanente en el sistema.
                    Asegúrese de que el docente realmente no asistió antes de confirmar.
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>¡Excelente!</strong> Todos los docentes han marcado su asistencia para los horarios del día seleccionado.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.ausencia-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

function toggleCheckbox(checkbox, index) {
    const selectAll = document.getElementById('select-all');
    const allCheckboxes = document.querySelectorAll('.ausencia-checkbox');
    const checkedCount = document.querySelectorAll('.ausencia-checkbox:checked').length;
    
    selectAll.checked = checkedCount === allCheckboxes.length;
}

function marcarTodasAusencias() {
    const checkboxes = document.querySelectorAll('.ausencia-checkbox');
    let hasSelection = false;
    
    checkboxes.forEach(cb => {
        if (cb.checked) {
            hasSelection = true;
        }
    });
    
    if (!hasSelection) {
        alert('Por favor, seleccione al menos un docente para marcar como ausente.');
        return;
    }
    
    const count = document.querySelectorAll('.ausencia-checkbox:checked').length;
    
    if (confirm(`¿Está seguro de marcar como AUSENTE a ${count} docente(s)?`)) {
        // Remover checkboxes no seleccionados del formulario
        const form = document.getElementById('form-ausencias-masivas');
        const allCheckboxes = document.querySelectorAll('.ausencia-checkbox');
        
        allCheckboxes.forEach((cb, index) => {
            if (!cb.checked) {
                // Remover los inputs hidden asociados
                const container = cb.closest('tr');
                const inputs = container.querySelectorAll('input[type="hidden"]');
                inputs.forEach(input => input.remove());
                cb.remove();
            }
        });
        
        form.submit();
    }
}
</script>
@endsection
