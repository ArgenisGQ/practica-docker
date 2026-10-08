<?php
/**
 * =============================================================================
 * UNINOTAS - ALUMNO CONTROLLER
 * =============================================================================
 * Controlador encargado del CRUD completo de alumnos y notas.
 * Aplica reglas de validación en el servidor y verificación estricta de permisos RBAC.
 */

require_once __DIR__ . '/../core/Controller.php';

class AlumnoController extends Controller {

    private Alumno $alumnoModel;
    private Carrera $carreraModel;

    public function __construct() {
        // Garantizar que solo usuarios autenticados accedan al módulo de alumnos
        $this->requireAuth();
        
        $this->alumnoModel = $this->model('Alumno');
        $this->carreraModel = $this->model('Carrera');
    }

    /**
     * Listado paginado de alumnos con soporte de búsqueda (Index).
     */
    public function index(): void {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $paginationData = $this->alumnoModel->getPaginated($page, 10, $search);

        $this->view('alumnos/index', [
            'title' => 'Listado de Estudiantes - UniNotas',
            'alumnos' => $paginationData['data'],
            'pagination' => $paginationData,
            'userRole' => $_SESSION['user_role'] ?? 'viewer',
            'userName' => $_SESSION['user_name'] ?? 'Usuario'
        ]);
    }

    /**
     * Muestra la vista detallada de un alumno (Show).
     * 
     * @param string|int $id ID del alumno
     */
    public function show($id = null): void {
        if (!$id || !is_numeric($id)) {
            $_SESSION['flash_error'] = "ID de alumno no válido.";
            $this->redirect('/alumnos');
        }

        $alumno = $this->alumnoModel->getById((int) $id);
        if (!$alumno) {
            $_SESSION['flash_error'] = "El alumno solicitado no existe.";
            $this->redirect('/alumnos');
        }

        $this->view('alumnos/show', [
            'title' => 'Detalle del Estudiante - ' . $alumno['nombre'] . ' ' . $alumno['apellido'],
            'alumno' => $alumno,
            'userRole' => $_SESSION['user_role'] ?? 'viewer'
        ]);
    }

    /**
     * Muestra el formulario para registrar un nuevo alumno (Create).
     * Requiere permisos de Administrador (uni_write).
     */
    public function create(): void {
        $this->requireAdmin();

        $carreras = $this->carreraModel->getAll();

        $this->view('alumnos/create', [
            'title' => 'Registrar Nuevo Estudiante - UniNotas',
            'carreras' => $carreras,
            'errors' => [],
            'old' => []
        ]);
    }

    /**
     * Procesa y almacena en BD un nuevo alumno (Store).
     * Requiere permisos de Administrador (uni_write).
     */
    public function store(): void {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alumnos/create');
        }

        $data = [
            'cedula' => trim($_POST['cedula'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'carrera_id' => trim($_POST['carrera_id'] ?? ''),
            'nota_corte1' => trim($_POST['nota_corte1'] ?? ''),
            'nota_corte2' => trim($_POST['nota_corte2'] ?? ''),
            'nota_corte3' => trim($_POST['nota_corte3'] ?? '')
        ];

        // Validaciones en el Servidor
        $errors = $this->validateAlumnoData($data);

        if (!empty($errors)) {
            $carreras = $this->carreraModel->getAll();
            $this->view('alumnos/create', [
                'title' => 'Registrar Nuevo Estudiante - UniNotas',
                'carreras' => $carreras,
                'errors' => $errors,
                'old' => $data
            ]);
            return;
        }

        // Guardar en la base de datos
        if ($this->alumnoModel->create($data)) {
            $_SESSION['flash_success'] = "Estudiante {$data['nombre']} {$data['apellido']} creado exitosamente.";
            $this->redirect('/alumnos');
        } else {
            $_SESSION['flash_error'] = "Error al intentar guardar el estudiante en MS SQL Server.";
            $this->redirect('/alumnos/create');
        }
    }

    /**
     * Muestra el formulario de edición de un alumno (Edit).
     * Requiere permisos de Administrador (uni_write).
     * 
     * @param string|int $id ID del alumno
     */
    public function edit($id = null): void {
        $this->requireAdmin();

        if (!$id || !is_numeric($id)) {
            $_SESSION['flash_error'] = "ID de alumno no válido.";
            $this->redirect('/alumnos');
        }

        $alumno = $this->alumnoModel->getById((int) $id);
        if (!$alumno) {
            $_SESSION['flash_error'] = "El alumno no existe.";
            $this->redirect('/alumnos');
        }

        $carreras = $this->carreraModel->getAll();

        $this->view('alumnos/edit', [
            'title' => 'Editar Estudiante - ' . $alumno['nombre'] . ' ' . $alumno['apellido'],
            'alumno' => $alumno,
            'carreras' => $carreras,
            'errors' => []
        ]);
    }

    /**
     * Actualiza los datos de un alumno existente (Update).
     * Requiere permisos de Administrador (uni_write).
     * 
     * @param string|int $id ID del alumno
     */
    public function update($id = null): void {
        $this->requireAdmin();

        if (!$id || !is_numeric($id) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/alumnos');
        }

        $alumnoId = (int) $id;
        $data = [
            'cedula' => trim($_POST['cedula'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'carrera_id' => trim($_POST['carrera_id'] ?? ''),
            'nota_corte1' => trim($_POST['nota_corte1'] ?? ''),
            'nota_corte2' => trim($_POST['nota_corte2'] ?? ''),
            'nota_corte3' => trim($_POST['nota_corte3'] ?? '')
        ];

        // Validaciones
        $errors = $this->validateAlumnoData($data, $alumnoId);

        if (!empty($errors)) {
            $carreras = $this->carreraModel->getAll();
            $data['id'] = $alumnoId;
            $this->view('alumnos/edit', [
                'title' => 'Editar Estudiante',
                'alumno' => $data,
                'carreras' => $carreras,
                'errors' => $errors
            ]);
            return;
        }

        if ($this->alumnoModel->update($alumnoId, $data)) {
            $_SESSION['flash_success'] = "Datos del estudiante actualizados correctamente.";
            $this->redirect('/alumnos/show/' . $alumnoId);
        } else {
            $_SESSION['flash_error'] = "Error al actualizar la información en la base de datos.";
            $this->redirect('/alumnos/edit/' . $alumnoId);
        }
    }

    /**
     * Elimina un registro de alumno (Delete).
     * Requiere permisos de Administrador (uni_write).
     * 
     * @param string|int $id ID del alumno
     */
    public function delete($id = null): void {
        $this->requireAdmin();

        if (!$id || !is_numeric($id)) {
            $_SESSION['flash_error'] = "ID inválido.";
            $this->redirect('/alumnos');
        }

        $alumnoId = (int) $id;
        if ($this->alumnoModel->delete($alumnoId)) {
            $_SESSION['flash_success'] = "Estudiante eliminado del sistema correctamente.";
        } else {
            $_SESSION['flash_error'] = "Error al intentar eliminar el registro de la base de datos.";
        }

        $this->redirect('/alumnos');
    }

    /**
     * Método auxiliar de validación de formulario en el servidor.
     * 
     * @param array $data Datos del formulario
     * @param int|null $currentId ID del alumno en caso de edición
     * @return array Arreglo de mensajes de error
     */
    private function validateAlumnoData(array $data, ?int $currentId = null): array {
        $errors = [];

        // 1. Cédula: formato V-######## (expresión regular)
        if (empty($data['cedula'])) {
            $errors['cedula'] = "La cédula es un campo obligatorio.";
        } elseif (!preg_match('/^V-\d{7,8}$/', $data['cedula'])) {
            $errors['cedula'] = "La cédula debe tener el formato V-12345678 (prefijo V- seguido de 7 a 8 dígitos).";
        } elseif ($this->alumnoModel->cedulaExists($data['cedula'], $currentId)) {
            $errors['cedula'] = "La cédula ingresada ya se encuentra registrada en el sistema.";
        }

        // 2. Nombre y Apellido
        if (empty($data['nombre'])) {
            $errors['nombre'] = "El nombre es obligatorio.";
        } elseif (strlen($data['nombre']) < 2 || strlen($data['nombre']) > 50) {
            $errors['nombre'] = "El nombre debe tener entre 2 y 50 caracteres.";
        }

        if (empty($data['apellido'])) {
            $errors['apellido'] = "El apellido es obligatorio.";
        } elseif (strlen($data['apellido']) < 2 || strlen($data['apellido']) > 50) {
            $errors['apellido'] = "El apellido debe tener entre 2 y 50 caracteres.";
        }

        // 3. Carrera
        if (empty($data['carrera_id']) || !is_numeric($data['carrera_id'])) {
            $errors['carrera_id'] = "Debe seleccionar una carrera válida.";
        } else {
            $carrera = $this->carreraModel->getById((int)$data['carrera_id']);
            if (!$carrera) {
                $errors['carrera_id'] = "La carrera seleccionada no existe.";
            }
        }

        // 4. Notas (Corte 1, 2 y 3 entre 0 y 20)
        for ($i = 1; $i <= 3; $i++) {
            $key = "nota_corte{$i}";
            if ($data[$key] === '' || !is_numeric($data[$key])) {
                $errors[$key] = "La Nota Corte {$i} es obligatoria y debe ser un valor numérico.";
            } else {
                $nota = (float) $data[$key];
                if ($nota < 0 || $nota > 20) {
                    $errors[$key] = "La Nota Corte {$i} debe estar comprendida entre 0.00 y 20.00.";
                }
            }
        }

        return $errors;
    }
}
