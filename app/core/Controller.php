<?php
/**
 * =============================================================================
 * UNINOTAS - CORE CONTROLLER BASE
 * =============================================================================
 * Clase abstracta principal para todos los controladores MVC.
 * Proporciona servicios comunes de renderizado de vistas, instanciado de modelos,
 * redirecciones y control de acceso RBAC.
 */

abstract class Controller {

    /**
     * Instancia un modelo por nombre de clase.
     * 
     * @param string $modelName Nombre de la clase del modelo
     * @return object Instancia del modelo requerido
     */
    public function model(string $modelName) {
        $modelFile = __DIR__ . '/../models/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $modelName();
        }
        throw new Exception("El modelo '{$modelName}' no fue encontrado en {$modelFile}.");
    }

    /**
     * Carga y renderiza una vista inyectando variables extraídas del array de datos.
     * 
     * @param string $viewName Ruta relativa dentro de la carpeta views (ej: 'alumnos/index')
     * @param array $data Variables a pasar a la vista
     */
    public function view(string $viewName, array $data = []): void {
        // Extrae las claves del arreglo asociativo como variables independientes
        extract($data);

        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("La vista '{$viewName}' no fue encontrada en {$viewFile}.");
        }
    }

    /**
     * Redirecciona a una URL interna del sistema de manera segura.
     * 
     * @param string $url Ruta de destino (ej: '/alumnos')
     */
    public function redirect(string $url): void {
        header("Location: " . $url);
        exit;
    }

    /**
     * Middleware simple: Garantiza que el usuario esté autenticado.
     */
    protected function requireAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $_SESSION['flash_error'] = "Debe iniciar sesión para acceder al sistema.";
            $this->redirect('/auth/login');
        }
    }

    /**
     * Middleware RBAC: Garantiza que el usuario tenga rol 'admin'.
     */
    protected function requireAdmin(): void {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['flash_error'] = "Acceso Denegado: Su usuario (Solo Lectura) no posee permisos de escritura.";
            $this->redirect('/alumnos');
        }
    }

    /**
     * Helper de Sanitización HTML contra ataques XSS.
     * 
     * @param string|null $string Texto a limpiar
     * @return string Texto escapado en UTF-8
     */
    public static function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
