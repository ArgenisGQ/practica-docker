<?php
/**
 * =============================================================================
 * UNINOTAS - FRONT CONTROLLER (PUBLIC INDEX)
 * =============================================================================
 * Punto de entrada único a toda la aplicación web.
 * Inicializa la sesión, carga las clases base del Core y ejecuta el Router.
 */

// Reporte de errores según el entorno (.env)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar la sesión global si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar Clases Core de la arquitectura MVC
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Router.php';

// Inicializar el Enrutador
$app = new Router();
