-- Agregar campos necesarios a la tabla Horario para que funcione con el sistema
ALTER TABLE Horario 
ADD COLUMN NroAula INT,
ADD COLUMN ID_Grupo INT;

-- Agregar las foreign keys
ALTER TABLE Horario
ADD CONSTRAINT fk_horario_aula FOREIGN KEY (NroAula) REFERENCES Aula(NroAula) ON UPDATE CASCADE ON DELETE SET NULL,
ADD CONSTRAINT fk_horario_grupo FOREIGN KEY (ID_Grupo) REFERENCES Grupo(ID) ON UPDATE CASCADE ON DELETE SET NULL;

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_horario_aula ON Horario(NroAula);
CREATE INDEX idx_horario_grupo ON Horario(ID_Grupo);
