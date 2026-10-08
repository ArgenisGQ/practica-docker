<?php
/**
 * =============================================================================
 * UNINOTAS - CORE MODEL BASE
 * =============================================================================
 * Clase base para todos los modelos del sistema.
 * Gestiona la conexión PDO Singleton hacia MS SQL Server según las credenciales del rol.
 */

require_once __DIR__ . '/../config/database.php';

abstract class Model {
    protected PDO $db;

    public function __construct() {
        // Obtiene la instancia PDO singleton configurada en config/database.php
        $this->db = Database::getConnection();
    }
}
