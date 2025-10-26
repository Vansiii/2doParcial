-- ============================================================================
-- MIGRACIONES PENDIENTES PARA LA BASE DE DATOS
-- Ejecutar en Railway PostgreSQL
-- ============================================================================

-- 1. Agregar columnas faltantes a la tabla Horario
ALTER TABLE Horario 
ADD COLUMN IF NOT EXISTS NroAula INT,
ADD COLUMN IF NOT EXISTS ID_Grupo INT;

-- Agregar foreign keys para Horario
ALTER TABLE Horario
DROP CONSTRAINT IF EXISTS fk_horario_aula;

ALTER TABLE Horario
ADD CONSTRAINT fk_horario_aula 
FOREIGN KEY (NroAula) REFERENCES Aula(NroAula) ON DELETE SET NULL;

ALTER TABLE Horario
DROP CONSTRAINT IF EXISTS fk_horario_grupo;

ALTER TABLE Horario
ADD CONSTRAINT fk_horario_grupo 
FOREIGN KEY (ID_Grupo) REFERENCES Grupo(ID) ON DELETE SET NULL;

-- 2. Crear tabla pivot horario_docente (NO usar asistencia para asignación)
CREATE TABLE IF NOT EXISTS horario_docente (
    id_horario INT NOT NULL,
    id_usuario INT NOT NULL,
    PRIMARY KEY (id_horario, id_usuario),
    FOREIGN KEY (id_horario) REFERENCES Horario(ID) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(ID) ON DELETE CASCADE
);

-- Índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_horario_docente_horario ON horario_docente(id_horario);
CREATE INDEX IF NOT EXISTS idx_horario_docente_usuario ON horario_docente(id_usuario);

-- Comentarios explicativos
COMMENT ON TABLE horario_docente IS 'Tabla pivot para asignar docentes a horarios (planificación). La tabla asistencia es solo para registrar asistencias reales con fecha/hora.';
COMMENT ON COLUMN horario_docente.id_horario IS 'Referencia al horario planificado';
COMMENT ON COLUMN horario_docente.id_usuario IS 'Referencia al docente (usuario con rol Docente)';

-- ============================================================================
-- VERIFICACIÓN: Consultar estructura actualizada
-- ============================================================================

-- Ver columnas de Horario
SELECT column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_name = 'horario'
ORDER BY ordinal_position;

-- Ver estructura de horario_docente
SELECT column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_name = 'horario_docente'
ORDER BY ordinal_position;

-- Contar registros
SELECT 
    (SELECT COUNT(*) FROM horario) as total_horarios,
    (SELECT COUNT(*) FROM horario_docente) as total_asignaciones;
