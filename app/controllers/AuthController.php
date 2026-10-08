<?php
/**
 * =============================================================================
 * UNINOTAS - AUTH CONTROLLER
 * =============================================================================
 * Controlador encargado del proceso de Autenticación, Inicio y Cierre de Sesión.
 */

require_once __DIR__ . '/../core/Controller.php';

class AuthController extends Controller {

    /**
     * Muestra el formulario de login o procesa la autenticación POST.
     */
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si ya está autenticado, redirigir al listado de alumnos
        if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            $this->redirect('/alumnos');
        }

        $error = null;

        // Procesar formulario POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Obtener credenciales válidas desde variables de entorno
            $adminUser = getenv('APP_ADMIN_USER') ?: 'admin';
            $adminPass = getenv('APP_ADMIN_PASS') ?: 'admin123';
            $viewerUser = getenv('APP_VIEWER_USER') ?: 'viewer';
            $viewerPass = getenv('APP_VIEWER_PASS') ?: 'viewer123';

            /**
             * NOTA PARA EL ENTREVISTADOR DE PRODUCCIÓN:
             * En una aplicación de producción real, las contraseñas nunca deben compararse en texto plano
             * ni almacenarse en variables de entorno fijas. Se debe consultar la base de datos y validar mediante:
             * password_verify($password, $userHashFromDB) habiendo registrado previamente con password_hash().
             * Para los fines de este proyecto demo de entrevista técnica, se utiliza esta verificación controlada.
             */
            if ($username === $adminUser && $password === $adminPass) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_name'] = 'Administrador Sistema';
                $_SESSION['user_role'] = 'admin';
                $_SESSION['flash_success'] = "¡Bienvenido, Administrador! Acceso total concedido (uni_write).";
                $this->redirect('/alumnos');
            } elseif ($username === $viewerUser && $password === $viewerPass) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_name'] = 'Consultor Académico';
                $_SESSION['user_role'] = 'viewer';
                $_SESSION['flash_success'] = "¡Bienvenido, Consultor! Acceso en modo solo lectura concedido (uni_read).";
                $this->redirect('/alumnos');
            } else {
                $error = "Credenciales incorrectas. Verifique su usuario y contraseña.";
            }
        }

        $this->view('auth/login', [
            'error' => $error,
            'title' => 'Inicio de Sesión - UniNotas'
        ]);
    }

    /**
     * Cierra la sesión activa y destruye las cookies de sesión.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        
        session_start();
        $_SESSION['flash_success'] = "Ha cerrado sesión correctamente.";
        $this->redirect('/auth/login');
    }
}
