<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestión Académica')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .navbar {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .border-left-secondary {
            border-left: 0.25rem solid #858796 !important;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    @auth
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="text-center py-4">
                <h4 class="text-white mb-0">
                    <i class="fas fa-graduation-cap"></i> Sistema
                </h4>
                <small class="text-white-50">Gestión Académica</small>
            </div>
            
            <nav class="nav flex-column px-2">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                <!-- Sección de Gestión -->
                <div class="mt-3">
                    <small class="text-white-50 px-3">GESTIONAR DOCENTES</small>
                </div>
                
                <a class="nav-link {{ request()->routeIs('docentes.*') ? 'active' : '' }}" href="{{ route('docentes.index') }}">
                    <i class="fas fa-chalkboard-teacher"></i> Docentes
                </a>
                <div class="mt-3">
                    <small class="text-white-50 px-3">GESTIONAR MATERIAS</small>
                </div>                
                <a class="nav-link {{ request()->routeIs('materias.*') ? 'active' : '' }}" href="{{ route('materias.index') }}">
                    <i class="fas fa-book"></i> Materias
                </a>
                <div class="mt-3">
                    <small class="text-white-50 px-3">GESTIONAR CLASES</small>
                </div>                
                <a class="nav-link {{ request()->routeIs('aulas.*') ? 'active' : '' }}" href="{{ route('aulas.index') }}">
                    <i class="fas fa-door-open"></i> Aulas
                </a>
                
                <a class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}" href="{{ route('grupos.index') }}">
                    <i class="fas fa-users"></i> Grupos
                </a>

                <a class="nav-link {{ request()->routeIs('modulos.*') ? 'active' : '' }}" href="{{ route('modulos.index') }}">
                    <i class="fas fa-building"></i> Módulos
                </a>                
                @endif
                
                <!-- Sección de Horarios -->
                <div class="mt-3">
                    <small class="text-white-50 px-3">GESTIONAR HORARIOS</small>
                </div>
                
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador'))
                <a class="nav-link {{ request()->routeIs('semestres.*') ? 'active' : '' }}" href="{{ route('semestres.index') }}">
                    <i class="fas fa-calendar-alt"></i> Semestres
                </a>                

                <a class="nav-link {{ request()->routeIs('horarios.asignar') ? 'active' : '' }}" href="{{ route('horarios.asignar') }}">
                    <i class="fas fa-calendar-plus"></i> Asignar Horario
                </a>
                @endif
                
                <a class="nav-link {{ request()->routeIs('horarios.docente') ? 'active' : '' }}" href="{{ route('horarios.docente') }}">
                    <i class="fas fa-calendar-alt"></i> Por Docente
                </a>
                
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Coordinador') || auth()->user()->hasRole('Docente'))
                <a class="nav-link {{ request()->routeIs('horarios.grupo') ? 'active' : '' }}" href="{{ route('horarios.grupo') }}">
                    <i class="fas fa-calendar"></i> Por Grupo
                </a>
                @endif
                
                <!-- Configuración -->
                <div class="mt-3">
                    <small class="text-white-50 px-3">GESTIONAR PERFIL</small>
                </div>
                
                <a class="nav-link {{ request()->routeIs('change-password') ? 'active' : '' }}" href="{{ route('change-password') }}">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </a>
                
                <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.2);">
                
                <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Navbar -->
            <nav class="navbar navbar-expand navbar-light bg-white mb-4">
                <div class="container-fluid">
                    <div class="d-none d-sm-inline-block ms-auto">
                        <span class="text-gray-600">
                            <i class="fas fa-user"></i> {{ auth()->user()->nombre }}
                            @if(auth()->user()->roles->isNotEmpty())
                                <span class="badge bg-primary">{{ auth()->user()->roles->first()->descripcion }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </nav>
            
            <!-- Page Content -->
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    @else
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>
    @endauth
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
