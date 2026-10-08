<?php
/**
 * =============================================================================
 * UNINOTAS - CORE ROUTER (FRONT CONTROLLER)
 * =============================================================================
 * Enrutador de URL amigables sin frameworks.
 * Analiza la petición HTTP y direcciona al Controlador, Método y Parámetros.
 * Estructura esperada: http://dominio/controlador/metodo/parametro
 */

class Router {
    protected $currentController = 'AlumnoController';
    protected string $currentMethod = 'index';
    protected array $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Resolver el Controlador
        if (!empty($url[0])) {
            $rawName = ucfirst(strtolower($url[0]));
            $controllerName = $rawName . 'Controller';
            $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

            // Fallback bidireccional: Resolver tanto singular como plural (/alumnos -> AlumnoController o AlumnosController)
            if (!file_exists($controllerFile)) {
                if (str_ends_with(strtolower($rawName), 's')) {
                    $altName = ucfirst(substr(strtolower($rawName), 0, -1)) . 'Controller';
                } else {
                    $altName = $rawName . 'sController';
                }
                $altFile = __DIR__ . '/../controllers/' . $altName . '.php';
                if (file_exists($altFile)) {
                    $controllerName = $altName;
                    $controllerFile = $altFile;
                }
            }

            if (file_exists($controllerFile)) {
                $this->currentController = $controllerName;
                unset($url[0]);
            } else {
                http_response_code(404);
                echo "<h1>Error 404: Página no encontrada</h1><p>El controlador <strong>" . htmlspecialchars($controllerName) . "</strong> no existe.</p>";
                exit;
            }
        }

        // Cargar el archivo del controlador
        require_once __DIR__ . '/../controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController();

        // 2. Resolver el Método dentro del Controlador
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            } else {
                http_response_code(404);
                echo "<h1>Error 404: Acción no encontrada</h1><p>El método <strong>" . htmlspecialchars($url[1]) . "</strong> no existe en este controlador.</p>";
                exit;
            }
        }

        // 3. Reorganizar los parámetros restantes
        $this->params = $url ? array_values($url) : [];

        // 4. Ejecutar el callback del controlador invocando el método con los parámetros
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    /**
     * Parsea y sanitiza la URL de la petición HTTP.
     * 
     * @return array Segmentos de la URL
     */
    private function parseUrl(): array {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        return [];
    }
}
