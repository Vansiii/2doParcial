-- 1. Insertar Roles
INSERT INTO Rol (Descripcion) VALUES 
('Administrador'),
('Autoridad'),
('Coordinador'),
('Docente')
ON CONFLICT DO NOTHING;

-- 2. Insertar Permisos básicos
INSERT INTO Permiso (Descripcion) VALUES 
('Gestionar Usuarios'),
('Gestionar Docentes'),
('Gestionar Horarios'),
('Ver Reportes'),
('Gestionar Materias'),
('Gestionar Asistencias')
ON CONFLICT DO NOTHING;

-- 3. Asignar permisos a roles
-- Administrador tiene todos los permisos
INSERT INTO Rol_Permisos (ID_Rol, ID_Permiso)
SELECT r.ID, p.ID
FROM Rol r, Permiso p
WHERE r.Descripcion = 'Administrador'
ON CONFLICT DO NOTHING;

-- Coordinador puede gestionar docentes y horarios
INSERT INTO Rol_Permisos (ID_Rol, ID_Permiso)
SELECT r.ID, p.ID
FROM Rol r, Permiso p
WHERE r.Descripcion = 'Coordinador' 
AND p.Descripcion IN ('Gestionar Docentes', 'Gestionar Horarios', 'Ver Reportes')
ON CONFLICT DO NOTHING;

-- Autoridad puede ver reportes
INSERT INTO Rol_Permisos (ID_Rol, ID_Permiso)
SELECT r.ID, p.ID
FROM Rol r, Permiso p
WHERE r.Descripcion = 'Autoridad' 
AND p.Descripcion IN ('Ver Reportes')
ON CONFLICT DO NOTHING;

-- 4. Insertar usuarios de prueba
-- IMPORTANTE: Todas las contraseñas son "password123" (hasheadas con bcrypt)
-- Hash bcrypt de "password123": $2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi

-- Usuario Administrador
INSERT INTO Usuario (Nombre, Correo, Telefono, Passw) VALUES 
('Admin Sistema', 'admin@sistema.com', 71234567, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi')
ON CONFLICT (Correo) DO NOTHING;

-- Usuario Coordinador
INSERT INTO Usuario (Nombre, Correo, Telefono, Passw) VALUES 
('María Coordinadora', 'coordinador@sistema.com', 72345678, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi')
ON CONFLICT (Correo) DO NOTHING;

-- Usuario Autoridad
INSERT INTO Usuario (Nombre, Correo, Telefono, Passw) VALUES 
('Carlos Autoridad', 'autoridad@sistema.com', 73456789, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi')
ON CONFLICT (Correo) DO NOTHING;

-- Usuarios Docentes
INSERT INTO Usuario (Nombre, Correo, Telefono, Passw) VALUES 
('Juan Docente Pérez', 'docente1@sistema.com', 74567890, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi'),
('Ana Profesora García', 'docente2@sistema.com', 75678901, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi'),
('Pedro Profesor López', 'docente3@sistema.com', 76789012, '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYVr0u12UJi')
ON CONFLICT (Correo) DO NOTHING;

-- 5. Asignar roles a usuarios
-- Asignar rol Administrador
INSERT INTO Rol_Usuario (ID_Usuario, ID_Rol, Detalle)
SELECT u.ID, r.ID, 'Administrador principal del sistema'
FROM Usuario u, Rol r
WHERE u.Correo = 'admin@sistema.com' AND r.Descripcion = 'Administrador'
ON CONFLICT DO NOTHING;

-- Asignar rol Coordinador
INSERT INTO Rol_Usuario (ID_Usuario, ID_Rol, Detalle)
SELECT u.ID, r.ID, 'Coordinador académico'
FROM Usuario u, Rol r
WHERE u.Correo = 'coordinador@sistema.com' AND r.Descripcion = 'Coordinador'
ON CONFLICT DO NOTHING;

-- Asignar rol Autoridad
INSERT INTO Rol_Usuario (ID_Usuario, ID_Rol, Detalle)
SELECT u.ID, r.ID, 'Autoridad académica'
FROM Usuario u, Rol r
WHERE u.Correo = 'autoridad@sistema.com' AND r.Descripcion = 'Autoridad'
ON CONFLICT DO NOTHING;

-- Asignar rol Docente a los docentes
INSERT INTO Rol_Usuario (ID_Usuario, ID_Rol, Detalle)
SELECT u.ID, r.ID, 'Docente de planta'
FROM Usuario u, Rol r
WHERE u.Correo IN ('docente1@sistema.com', 'docente2@sistema.com', 'docente3@sistema.com') 
AND r.Descripcion = 'Docente'
ON CONFLICT DO NOTHING;

-- 6. Insertar días de la semana
INSERT INTO Dia (ID, Descripcion) VALUES 
(1, 'Lunes'),
(2, 'Martes'),
(3, 'Miércoles'),
(4, 'Jueves'),
(5, 'Viernes'),
(6, 'Sábado'),
(7, 'Domingo')
ON CONFLICT DO NOTHING;

-- Verificar datos insertados
SELECT 'Usuarios creados:' as info;
SELECT ID, Nombre, Correo FROM Usuario;

SELECT 'Roles asignados:' as info;
SELECT u.Nombre, r.Descripcion as Rol
FROM Usuario u
JOIN Rol_Usuario ru ON u.ID = ru.ID_Usuario
JOIN Rol r ON ru.ID_Rol = r.ID;
