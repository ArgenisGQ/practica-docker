<?php
/**
 * =============================================================================
 * UNINOTAS - MODELO ALUMNO
 * =============================================================================
 * Encapsula la lógica de acceso a datos de los estudiantes y sus notas.
 * Utiliza Sentencias Preparadas (Prepared Statements) para prevenir SQL Injection.
 */

require_once __DIR__ . '/../core/Model.php';

class Alumno extends Model {

    /**
     * Obtiene el listado de alumnos paginado con filtro de búsqueda opcional.
     * 
     * @param int $page Número de página actual
     * @param int $limit Cantidad de registros por página
     * @param string $search Término de búsqueda (cédula, nombre, apellido, carrera)
     * @return array Estructura con datos de alumnos y metadatos de paginación
     */
    public function getPaginated(int $page = 1, int $limit = 10, string $search = ''): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;
        $search = trim($search);

        // Construcción de cláusula WHERE dinámica
        $whereSql = "";
        $params = [];

        if (!empty($search)) {
            $whereSql = "WHERE (a.cedula LIKE :search 
                            OR a.nombre LIKE :search 
                            OR a.apellido LIKE :search 
                            OR c.nombre LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // 1. Obtener total de registros que coinciden con el filtro
        $countSql = "SELECT COUNT(*) as total FROM dbo.Alumnos a 
                     INNER JOIN dbo.Carreras c ON a.carrera_id = c.id {$whereSql}";
        
        $stmtCount = $this->db->prepare($countSql);
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $totalRecords = (int) $stmtCount->fetch()['total'];

        // 2. Consulta de datos paginados utilizando OFFSET y FETCH NEXT (Sintaxis MS SQL Server)
        $dataSql = "SELECT a.id, a.cedula, a.nombre, a.apellido, a.carrera_id, 
                           c.nombre AS carrera_nombre,
                           a.nota_corte1, a.nota_corte2, a.nota_corte3, 
                           a.total, a.fecha_registro
                    FROM dbo.Alumnos a
                    INNER JOIN dbo.Carreras c ON a.carrera_id = c.id
                    {$whereSql}
                    ORDER BY a.id DESC
                    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";

        $stmtData = $this->db->prepare($dataSql);
        
        foreach ($params as $key => $val) {
            $stmtData->bindValue($key, $val);
        }
        $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmtData->execute();

        $data = $stmtData->fetchAll();
        $totalPages = (int) ceil($totalRecords / $limit);

        return [
            'data' => $data,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit,
            'search' => $search
        ];
    }

    /**
     * Obtiene los datos detallados de un alumno por su ID.
     * 
     * @param int $id ID del alumno
     * @return array|null Datos del alumno con el nombre de su carrera o null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT a.id, a.cedula, a.nombre, a.apellido, a.carrera_id, 
                       c.nombre AS carrera_nombre,
                       a.nota_corte1, a.nota_corte2, a.nota_corte3, 
                       a.total, a.fecha_registro
                FROM dbo.Alumnos a
                INNER JOIN dbo.Carreras c ON a.carrera_id = c.id
                WHERE a.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verifica si una cédula ya existe en la base de datos (excluyendo opcionalmente un ID).
     * 
     * @param string $cedula Cédula a consultar
     * @param int|null $excludeId ID a excluir (para ediciń)
     * @return bool True si existe, False si está disponible
     */
    public function cedulaExists(string $cedula, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM dbo.Alumnos WHERE cedula = :cedula";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cedula', $cedula);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return ((int) $stmt->fetch()['count']) > 0;
    }

    /**
     * Inserta un nuevo alumno en la base de datos.
     * Nota: La columna 'total' se calcula automáticamente por SQL Server (PERSISTED).
     * 
     * @param array $data Datos del alumno
     * @return bool True si se guardó correctamente
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO dbo.Alumnos 
                (cedula, nombre, apellido, carrera_id, nota_corte1, nota_corte2, nota_corte3) 
                VALUES 
                (:cedula, :nombre, :apellido, :carrera_id, :nota1, :nota2, :nota3)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cedula' => $data['cedula'],
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':carrera_id' => (int) $data['carrera_id'],
            ':nota1' => (float) $data['nota_corte1'],
            ':nota2' => (float) $data['nota_corte2'],
            ':nota3' => (float) $data['nota_corte3']
        ]);
    }

    /**
     * Actualiza los datos de un alumno existente.
     * 
     * @param int $id ID del alumno a actualizar
     * @param array $data Nuevos datos del alumno
     * @return bool True si se actualizó con éxito
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE dbo.Alumnos SET 
                    cedula = :cedula,
                    nombre = :nombre,
                    apellido = :apellido,
                    carrera_id = :carrera_id,
                    nota_corte1 = :nota1,
                    nota_corte2 = :nota2,
                    nota_corte3 = :nota3
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':cedula' => $data['cedula'],
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':carrera_id' => (int) $data['carrera_id'],
            ':nota1' => (float) $data['nota_corte1'],
            ':nota2' => (float) $data['nota_corte2'],
            ':nota3' => (float) $data['nota_corte3']
        ]);
    }

    /**
     * Elimina un alumno por su ID.
     * 
     * @param int $id ID del alumno
     * @return bool True si fue eliminado
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM dbo.Alumnos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
