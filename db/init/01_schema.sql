-- =============================================================================
-- UNINOTAS - SCRIPT 01: CREACIÓN DE BASE DE DATOS Y ESQUEMA DE TABLAS
-- =============================================================================

SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

USE master;
GO

IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'UniNotasDB')
BEGIN
    CREATE DATABASE UniNotasDB;
END
GO

USE UniNotasDB;
GO

SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

-- -----------------------------------------------------------------------------
-- Tabla 1: Carreras Universitarias
-- -----------------------------------------------------------------------------
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Carreras]') AND type in (N'U'))
BEGIN
    CREATE TABLE dbo.Carreras (
        id INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        nombre NVARCHAR(100) NOT NULL UNIQUE
    );
END
GO

-- Insertar las 6 carreras obligatorias
IF NOT EXISTS (SELECT 1 FROM dbo.Carreras)
BEGIN
    INSERT INTO dbo.Carreras (nombre) VALUES 
    (N'Ingeniería de Sistemas'),
    (N'Ingeniería Civil'),
    (N'Medicina'),
    (N'Derecho'),
    (N'Administración de Empresas'),
    (N'Psicología');
END
GO

-- -----------------------------------------------------------------------------
-- Tabla 2: Alumnos y Evaluaciones (con columna calculada PERSISTED)
-- -----------------------------------------------------------------------------
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Alumnos]') AND type in (N'U'))
BEGIN
    CREATE TABLE dbo.Alumnos (
        id INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        cedula NVARCHAR(20) NOT NULL UNIQUE,
        nombre NVARCHAR(50) NOT NULL,
        apellido NVARCHAR(50) NOT NULL,
        carrera_id INT NOT NULL,
        nota_corte1 DECIMAL(4,2) NOT NULL CONSTRAINT CHK_Nota1 CHECK (nota_corte1 BETWEEN 0 AND 20),
        nota_corte2 DECIMAL(4,2) NOT NULL CONSTRAINT CHK_Nota2 CHECK (nota_corte2 BETWEEN 0 AND 20),
        nota_corte3 DECIMAL(4,2) NOT NULL CONSTRAINT CHK_Nota3 CHECK (nota_corte3 BETWEEN 0 AND 20),
        -- Columna calculada persisitida en disco para alto rendimiento de consultas
        total AS (nota_corte1 + nota_corte2 + nota_corte3) PERSISTED,
        fecha_registro DATETIME DEFAULT GETDATE(),
        
        -- Clave foránea referenciando a Carreras
        CONSTRAINT FK_Alumnos_Carrera FOREIGN KEY (carrera_id) REFERENCES dbo.Carreras(id) ON DELETE CASCADE
    );
END
GO
