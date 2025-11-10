@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Marcar Asistencia
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
                            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Información del día -->
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    {{ $hoy->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                                </h5>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    <span id="hora-actual">{{ $hoy->format('H:i:s') }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de horarios del día -->
                    @if($horariosHoy->count() > 0)
                        <h6 class="mb-3">
                            <i class="fas fa-list me-2"></i>Mis Horarios de Hoy
                        </h6>
                        <div class="row">
                            @foreach($horariosHoy as $horario)
                                <div class="col-md-6 mb-3">
                                    <div class="card {{ $horario->asistencia_hoy ? 'border-success' : 'border-primary' }}">
                                        <div class="card-header {{ $horario->asistencia_hoy ? 'bg-success text-white' : 'bg-primary text-white' }}">
                                            <strong>{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</strong>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-2">
                                                <i class="fas fa-book me-2"></i>
                                                <strong>Materia:</strong> 
                                                @if($horario->materias->count() > 0)
                                                    {{ $horario->materias->first()->nombre }}
                                                    <span class="badge bg-info">{{ $horario->materias->first()->sigla }}</span>
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-users me-2"></i>
                                                <strong>Grupo:</strong> {{ $horario->grupo->sigla ?? 'N/A' }}
                                            </p>
                                            <p class="mb-3">
                                                <i class="fas fa-door-open me-2"></i>
                                                <strong>Aula:</strong> {{ $horario->aula->nroaula ?? 'N/A' }}
                                            </p>

                                            @if($horario->asistencia_hoy)
                                                <div class="alert alert-success mb-0">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    <strong>Asistencia registrada:</strong><br>
                                                    Hora: {{ $horario->asistencia_hoy->hora }}<br>
                                                    Tipo: <span class="badge bg-success">{{ $horario->asistencia_hoy->tipo }}</span>
                                                </div>
                                            @else
                                                <form action="{{ route('asistencias.marcar.post') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id_horario" value="{{ $horario->id }}">
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fas fa-check me-2"></i>Marcar Asistencia
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No tiene horarios asignados para el día de hoy.</p>
                        </div>
                    @endif

                    <!-- Botón para ver historial -->
                    <div class="mt-4">
                        <a href="{{ route('asistencias.mis-asistencias') }}" class="btn btn-info">
                            <i class="fas fa-history me-2"></i>Ver Mi Historial de Asistencias
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Actualizar hora en tiempo real
    function actualizarHora() {
        const ahora = new Date();
        const hora = ahora.getHours().toString().padStart(2, '0');
        const minutos = ahora.getMinutes().toString().padStart(2, '0');
        const segundos = ahora.getSeconds().toString().padStart(2, '0');
        document.getElementById('hora-actual').textContent = `${hora}:${minutos}:${segundos}`;
    }
    
    setInterval(actualizarHora, 1000);
</script>
@endsection
