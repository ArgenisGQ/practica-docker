<?php
/**
 * =============================================================================
 * UNINOTAS - MODELO CARRERA
 * =============================================================================
 * Representa la entidad Carrera de la base de datos MS SQL Server.
 */

require_once __DIR__ . '/../core/Model.php';

class Carrera extends Model {

    /**
     * Obtiene el listado completo de carreras universitarias.
     * 
     * @return array Arreglo asociativo con id y nombre de cada carrera
     */
    public function getAll(): array {
        $sql = "SELECT id, nombre FROM dbo.Carreras ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una carrera por su ID.
     * 
     * @param int $id ID de la carrera
     * @return array|null Datos de la carrera o null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT id, nombre FROM dbo.Carreras WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
