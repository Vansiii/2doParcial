@extends('layouts.app')

@section('styles')
<style>
    .time-slot {
        height: 60px;
        border: 1px solid #e0e0e0;
        padding: 5px;
        font-size: 0.85rem;
    }
    .schedule-header {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        padding: 10px;
    }
    .alert-sm {
        font-size: 0.85rem;
    }
    .aula-select option:disabled {
        background-color: #f8d7da;
        font-style: italic;
    }
    .aula-select optgroup {
        font-weight: bold;
        font-size: 0.9rem;
    }
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }
    #aulas_disponibles_0,
    #aulas_disponibles_1,
    #aulas_disponibles_2,
    #aulas_disponibles_3,
    #aulas_disponibles_4,
    #aulas_disponibles_5,
    #aulas_disponibles_6,
    #aulas_disponibles_7 {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus me-2"></i>Asignar Horario Manualmente
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
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

                    <!-- Formulario de asignación -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Nuevo Horario</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('horarios.guardar') }}" method="POST">
                                @csrf
                                
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="sigla_materia" class="form-label">
                                            Materia <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('sigla_materia') is-invalid @enderror" 
                                                id="sigla_materia" 
                                                name="sigla_materia" required>
                                            <option value="">Seleccione</option>
                                            @foreach($materias as $materia)
                                                <option value="{{ $materia->sigla }}" 
                                                    {{ old('sigla_materia') == $materia->sigla ? 'selected' : '' }}>
                                                    {{ $materia->sigla }} - {{ $materia->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('sigla_materia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label for="id_grupo" class="form-label">
                                            Grupo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('id_grupo') is-invalid @enderror" 
                                                id="id_grupo" 
                                                name="id_grupo" required>
                                            <option value="">Seleccione</option>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo->id }}" 
                                                    {{ old('id_grupo') == $grupo->id ? 'selected' : '' }}>
                                                    Grupo {{ $grupo->sigla }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_grupo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label class="form-label">
                                            Horarios y Aulas por Día <span class="text-danger">*</span>
                                        </label>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Seleccione los días y configure el horario y aula específico para cada uno
                                        </div>
                                    </div>
                                </div>

                                <div id="dias-container" class="row g-3">
                                    @foreach($dias as $dia)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card border-secondary">
                                                <div class="card-body">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input dia-checkbox" 
                                                               type="checkbox" 
                                                               name="dias_seleccionados[]" 
                                                               value="{{ $dia->id }}"
                                                               id="dia_check_{{ $dia->id }}"
                                                               onchange="toggleDiaFields({{ $dia->id }})"
                                                               {{ in_array($dia->id, old('dias_seleccionados', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold" for="dia_check_{{ $dia->id }}">
                                                            <i class="fas fa-calendar-day me-1"></i>{{ $dia->nombre }}
                                                        </label>
                                                    </div>
                                                    
                                                    <div id="fields_{{ $dia->id }}" style="display: {{ in_array($dia->id, old('dias_seleccionados', [])) ? 'block' : 'none' }};">
                                                        <div class="mb-2">
                                                            <label for="horaini_{{ $dia->id }}" class="form-label small">
                                                                <i class="fas fa-clock me-1"></i>Hora Inicio
                                                            </label>
                                                            <input type="time" 
                                                                   class="form-control form-control-sm hora-field" 
                                                                   id="horaini_{{ $dia->id }}" 
                                                                   name="horaini[{{ $dia->id }}]" 
                                                                   data-dia="{{ $dia->id }}"
                                                                   value="{{ old('horaini.'.$dia->id) }}"
                                                                   onchange="verificarDisponibilidadAulas({{ $dia->id }})">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="horafin_{{ $dia->id }}" class="form-label small">
                                                                <i class="fas fa-clock me-1"></i>Hora Fin
                                                            </label>
                                                            <input type="time" 
                                                                   class="form-control form-control-sm hora-field" 
                                                                   id="horafin_{{ $dia->id }}" 
                                                                   name="horafin[{{ $dia->id }}]" 
                                                                   data-dia="{{ $dia->id }}"
                                                                   value="{{ old('horafin.'.$dia->id) }}"
                                                                   onchange="verificarDisponibilidadAulas({{ $dia->id }})">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="nroaula_{{ $dia->id }}" class="form-label small">
                                                                <i class="fas fa-door-open me-1"></i>Aula
                                                                <span id="loading_{{ $dia->id }}" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                                            </label>
                                                            <div id="aulas_disponibles_{{ $dia->id }}" class="alert alert-info alert-sm p-2 mb-2" style="display: none; font-size: 0.85rem;">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                <span id="mensaje_disponibilidad_{{ $dia->id }}">Seleccione horario para ver disponibilidad</span>
                                                            </div>
                                                            <select class="form-select form-select-sm aula-select" 
                                                                    id="nroaula_{{ $dia->id }}" 
                                                                    name="nroaula[{{ $dia->id }}]"
                                                                    data-dia="{{ $dia->id }}">
                                                                <option value="">Seleccione horario primero</option>
                                                            </select>
                                                            <div id="info_aula_{{ $dia->id }}" class="mt-1" style="font-size: 0.75rem; display: none;">
                                                                <!-- Información detallada del aula seleccionada -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @error('dias_seleccionados')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror

                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Los docentes asignados se obtienen automáticamente del grupo seleccionado.
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Guardar Horario
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Filtros para ver horarios existentes -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrar Horarios Existentes</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('horarios.asignar') }}" class="row g-3">
                                <div class="col-md-4">
                                    <select class="form-select" name="sigla_materia">
                                        <option value="">Todas las materias</option>
                                        @foreach($materias as $materia)
                                            <option value="{{ $materia->sigla }}" 
                                                {{ request('sigla_materia') == $materia->sigla ? 'selected' : '' }}>
                                                {{ $materia->sigla }} - {{ $materia->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="id_grupo">
                                        <option value="">Todos los grupos</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}" 
                                                {{ request('id_grupo') == $grupo->id ? 'selected' : '' }}>
                                                Grupo {{ $grupo->sigla }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de horarios -->
                    @if($horarios->count() > 0)
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Horarios Encontrados ({{ $horarios->count() }})
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Materia</th>
                                                <th>Grupo</th>
                                                <th>Aula</th>
                                                <th>Horario</th>
                                                <th>Días</th>
                                                <th>Docentes</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($horarios as $horario)
                                                <tr>
                                                    <td>
                                                        @if($horario->materias->first())
                                                            <span class="badge bg-info">
                                                                {{ $horario->materias->first()->sigla }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($horario->grupo)
                                                            Grupo {{ $horario->grupo->sigla }}
                                                        @else
                                                            <span class="text-muted">Sin grupo</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($horario->aula)
                                                            {{ $horario->aula->nroaula }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-primary me-1"></i>
                                                        {{ \Carbon\Carbon::parse($horario->horaini)->format('H:i') }} - 
                                                        {{ \Carbon\Carbon::parse($horario->horafin)->format('H:i') }}
                                                    </td>
                                                    <td>
                                                        @foreach($horario->dias as $dia)
                                                            <span class="badge bg-secondary">{{ substr($dia->nombre, 0, 3) }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @php
                                                            // Obtener docente de grupo_materia
                                                            $materia = $horario->materias->first();
                                                            $docenteHorario = null;
                                                            if ($materia && $horario->grupo) {
                                                                $gm = \App\Models\GrupoMateria::where('id_grupo', $horario->grupo->id)
                                                                    ->where('sigla_materia', $materia->sigla)
                                                                    ->first();
                                                                $docenteHorario = $gm ? $gm->docente : null;
                                                            }
                                                        @endphp
                                                        
                                                        @if($docenteHorario)
                                                            <small class="d-block">{{ $docenteHorario->nombre }}</small>
                                                        @else
                                                            <span class="text-muted">Sin docente</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('horarios.destroy', $horario->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('¿Está seguro de eliminar este horario?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDiaFields(diaId) {
    const checkbox = document.getElementById('dia_check_' + diaId);
    const fields = document.getElementById('fields_' + diaId);
    const nroaula = document.getElementById('nroaula_' + diaId);
    const horaini = document.getElementById('horaini_' + diaId);
    const horafin = document.getElementById('horafin_' + diaId);
    const disponibilidadDiv = document.getElementById('aulas_disponibles_' + diaId);
    
    if (checkbox.checked) {
        fields.style.display = 'block';
        nroaula.required = true;
        horaini.required = true;
        horafin.required = true;
    } else {
        fields.style.display = 'none';
        nroaula.required = false;
        horaini.required = false;
        horafin.required = false;
        nroaula.value = '';
        horaini.value = '';
        horafin.value = '';
        if (disponibilidadDiv) disponibilidadDiv.style.display = 'none';
    }
}

// Verificar disponibilidad de aulas en tiempo real
async function verificarDisponibilidadAulas(diaId) {
    const horaIni = document.getElementById('horaini_' + diaId).value;
    const horaFin = document.getElementById('horafin_' + diaId).value;
    const selectAula = document.getElementById('nroaula_' + diaId);
    const loading = document.getElementById('loading_' + diaId);
    const disponibilidadDiv = document.getElementById('aulas_disponibles_' + diaId);
    const mensajeDisp = document.getElementById('mensaje_disponibilidad_' + diaId);
    
    // Validar que ambas horas estén completas
    if (!horaIni || !horaFin) {
        selectAula.innerHTML = '<option value="">Seleccione horario primero</option>';
        if (disponibilidadDiv) disponibilidadDiv.style.display = 'none';
        return;
    }
    
    // Validar que hora fin sea mayor que hora inicio
    if (horaIni >= horaFin) {
        selectAula.innerHTML = '<option value="">Hora fin debe ser posterior a hora inicio</option>';
        if (disponibilidadDiv) disponibilidadDiv.style.display = 'none';
        return;
    }
    
    // Mostrar loading
    if (loading) loading.style.display = 'inline-block';
    selectAula.disabled = true;
    
    try {
        const response = await fetch('{{ route("horarios.verificar-disponibilidad") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                dia_id: diaId,
                hora_ini: horaIni,
                hora_fin: horaFin
            })
        });
        
        if (!response.ok) throw new Error('Error al verificar disponibilidad');
        
        const aulas = await response.json();
        
        // Contar aulas disponibles y ocupadas
        const disponibles = aulas.filter(a => a.disponible).length;
        const ocupadas = aulas.filter(a => !a.disponible).length;
        
        // Actualizar mensaje de disponibilidad
        if (disponibles > 0) {
            mensajeDisp.innerHTML = `<strong>${disponibles}</strong> aula(s) disponible(s) - <span class="text-danger">${ocupadas}</span> ocupada(s)`;
            disponibilidadDiv.className = 'alert alert-success alert-sm p-2 mb-2';
        } else {
            mensajeDisp.innerHTML = `<strong>¡Sin aulas disponibles!</strong> Todas las aulas están ocupadas en este horario`;
            disponibilidadDiv.className = 'alert alert-danger alert-sm p-2 mb-2';
        }
        disponibilidadDiv.style.display = 'block';
        
        // Actualizar select con opciones
        selectAula.innerHTML = '<option value="">Seleccione un aula</option>';
        
        // Primero agregar aulas disponibles (en verde)
        const aulasDisponibles = aulas.filter(a => a.disponible);
        if (aulasDisponibles.length > 0) {
            const optgroupDisp = document.createElement('optgroup');
            optgroupDisp.label = '✓ Disponibles (' + aulasDisponibles.length + ')';
            
            aulasDisponibles.forEach(aula => {
                const option = document.createElement('option');
                option.value = aula.nroaula;
                option.textContent = `${aula.nroaula} - Cap: ${aula.capacidad} - ${aula.modulo}`;
                option.style.color = '#28a745';
                option.style.fontWeight = 'bold';
                optgroupDisp.appendChild(option);
            });
            
            selectAula.appendChild(optgroupDisp);
        }
        
        // Luego agregar aulas ocupadas (en rojo, deshabilitadas)
        const aulasOcupadas = aulas.filter(a => !a.disponible);
        if (aulasOcupadas.length > 0) {
            const optgroupOcup = document.createElement('optgroup');
            optgroupOcup.label = '✗ Ocupadas (' + aulasOcupadas.length + ')';
            
            aulasOcupadas.forEach(aula => {
                const option = document.createElement('option');
                option.value = aula.nroaula;
                option.textContent = `${aula.nroaula} - OCUPADA por ${aula.conflicto.materia} (${aula.conflicto.grupo})`;
                option.style.color = '#dc3545';
                option.disabled = true;
                optgroupOcup.appendChild(option);
            });
            
            selectAula.appendChild(optgroupOcup);
        }
        
        selectAula.disabled = false;
        
        // Si hay al menos una disponible, habilitar y sugerir la primera
        if (disponibles > 0) {
            selectAula.value = aulasDisponibles[0].nroaula;
        }
        
    } catch (error) {
        console.error('Error:', error);
        selectAula.innerHTML = '<option value="">Error al cargar aulas</option>';
        if (disponibilidadDiv) {
            disponibilidadDiv.className = 'alert alert-danger alert-sm p-2 mb-2';
            disponibilidadDiv.style.display = 'block';
            mensajeDisp.textContent = 'Error al verificar disponibilidad';
        }
    } finally {
        if (loading) loading.style.display = 'none';
        selectAula.disabled = false;
    }
}

// Validación del formulario antes de enviar
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('horarios.guardar') }}"]');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('.dia-checkbox:checked');
            
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos un día');
                return false;
            }
            
            let valid = true;
            checkboxes.forEach(function(checkbox) {
                const diaId = checkbox.value;
                const nroaula = document.getElementById('nroaula_' + diaId);
                const horaini = document.getElementById('horaini_' + diaId);
                const horafin = document.getElementById('horafin_' + diaId);
                
                if (!nroaula.value) {
                    valid = false;
                    alert('Debe seleccionar un aula para ' + checkbox.labels[0].textContent.trim());
                    return false;
                }
                
                if (!horaini.value || !horafin.value) {
                    valid = false;
                    alert('Debe completar hora de inicio y fin para ' + checkbox.labels[0].textContent.trim());
                    return false;
                }
                
                if (horaini.value >= horafin.value) {
                    valid = false;
                    alert('La hora de fin debe ser posterior a la hora de inicio para ' + checkbox.labels[0].textContent.trim());
                    return false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
@endsection

