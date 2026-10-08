-- =============================================================================
-- UNINOTAS - SCRIPT 02: CREACIÓN DE LOGINS Y USUARIOS CON ROLES DIFERENCIADOS
-- =============================================================================

SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

USE master;
GO

-- -----------------------------------------------------------------------------
-- 1. Login y Usuario SOLO LECTURA (uni_read)
-- -----------------------------------------------------------------------------
IF NOT EXISTS (SELECT name FROM sys.server_principals WHERE name = N'uni_read')
BEGIN
    CREATE LOGIN [uni_read] WITH PASSWORD = N'ReadOnly#2024', DEFAULT_DATABASE = [UniNotasDB], CHECK_EXPIRATION = OFF, CHECK_POLICY = OFF;
END
GO

USE UniNotasDB;
GO

IF NOT EXISTS (SELECT name FROM sys.database_principals WHERE name = N'uni_read')
BEGIN
    CREATE USER [uni_read] FOR LOGIN [uni_read];
END
GO

-- Asignación de rol db_datareader (Únicamente SELECT)
ALTER ROLE [db_datareader] ADD MEMBER [uni_read];
GO

-- -----------------------------------------------------------------------------
-- 2. Login y Usuario LECTURA / ESCRITURA (uni_write)
-- -----------------------------------------------------------------------------
USE master;
GO

IF NOT EXISTS (SELECT name FROM sys.server_principals WHERE name = N'uni_write')
BEGIN
    CREATE LOGIN [uni_write] WITH PASSWORD = N'WriteAll#2024', DEFAULT_DATABASE = [UniNotasDB], CHECK_EXPIRATION = OFF, CHECK_POLICY = OFF;
END
GO

USE UniNotasDB;
GO

IF NOT EXISTS (SELECT name FROM sys.database_principals WHERE name = N'uni_write')
BEGIN
    CREATE USER [uni_write] FOR LOGIN [uni_write];
END
GO

-- Asignación de roles db_datareader y db_datawriter (SELECT, INSERT, UPDATE, DELETE)
ALTER ROLE [db_datareader] ADD MEMBER [uni_write];
ALTER ROLE [db_datawriter] ADD MEMBER [uni_write];
GO
