<?php
/**
 * =============================================================================
 * UNINOTAS - ALUMNOS CONTROLLER ALIAS
 * =============================================================================
 * Alias para garantizar compatibilidad total con rutas plurales (/alumnos).
 * Hereda todas las acciones y métodos de AlumnoController.
 */

require_once __DIR__ . '/AlumnoController.php';

class AlumnosController extends AlumnoController {
}
