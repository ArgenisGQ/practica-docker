<?php
/**
 * =============================================================================
 * UNINOTAS - CONFIGURACIÓN Y CONEXIÓN DE BASE DE DATOS (PDO SQLSRV)
 * =============================================================================
 * Clase de administración de conexiones PDO hacia Microsoft SQL Server 2022.
 * Implementa seguridad RBAC conectando con uni_write (admin) o uni_read (viewer).
 */

class Database {
    private static ?PDO $instance = null;

    /**
     * Obtiene la instancia singleton de la conexión PDO basada en el rol de la sesión activa.
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO {
        // Inicializar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Determinar rol de usuario desde la sesión (por defecto solo lectura por seguridad)
        $role = $_SESSION['user_role'] ?? 'viewer';

        // Obtener variables de entorno (con fallbacks predeterminados)
        $host = getenv('DB_HOST') ?: 'sqlserver';
        $port = getenv('DB_PORT') ?: '1433';
        $dbName = getenv('DB_NAME') ?: 'UniNotasDB';

        if ($role === 'admin') {
            $username = getenv('DB_USER_RW') ?: 'uni_write';
            $password = getenv('DB_PASSWORD_RW') ?: 'WriteAll#2024';
        } else {
            $username = getenv('DB_USER_RO') ?: 'uni_read';
            $password = getenv('DB_PASSWORD_RO') ?: 'ReadOnly#2024';
        }

        // Construir Data Source Name (DSN) para PDO SQLSRV en entorno Linux/Docker
        // TrustServerCertificate=true es vital para conexiones SSL autofirmadas de SQL Server
        $dsn = "sqlsrv:Server={$host},{$port};Database={$dbName};TrustServerCertificate=true";

        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // Retorna o reutiliza la conexión PDO activa
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log de error interno y mensaje amigable en producción
            error_log("Error de Conexión PDO SQL Server: " . $e->getMessage());
            die("Error de conexión a la base de datos MS SQL Server. Verifique los logs del contenedor.");
        }
    }
}
