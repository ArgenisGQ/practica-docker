# 🎓 UniNotas - Sistema de Gestión de Notas Universitarias

Proyecto universitario desarrollado para **Entrevista Técnica de Nivel Senior / Tech Lead**, enfocado en **DevOps (Docker Compose)**, **PHP 8.0 puro (sin frameworks ni Composer)** y **Microsoft SQL Server 2022**.

---

## 📌 1. Requisitos Previos

- **Docker Desktop** (versión 20.10+ con soporte para `docker compose` v2).
- **Git**.
- *(Opcional)* **DBeaver** o **Azure Data Studio** para inspección directa de la base de datos MS SQL Server.

---

## 🚀 2. Cómo Levantar el Proyecto

El proyecto está diseñado para desplegarse de manera 100% automatizada con un solo comando:

```bash
# 1. Clonar o ingresar a la carpeta del proyecto
cd "UNY PHP"

# 2. Copiar las variables de entorno de ejemplo
cp .env.example .env

# 3. Construir e iniciar los contenedores en segundo plano
docker compose up -d --build
```

El script de entrada personalizado en el contenedor de SQL Server esperará a que el motor esté listo e interpretará automáticamente los scripts de esquema, permisos de usuarios y semilla (100 alumnos realistas).

---

## 🌐 3. URLs y Credenciales de Acceso

- **URL de la Aplicación Web:** [http://localhost:8080](http://localhost:8080)

### 🔑 Credenciales de la Aplicación Web

| Rol | Usuario | Contraseña | Nivel de Acceso | Usuario BD Asociado |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | `admin` | `admin123` | **Acceso Total** (Crear, Leer, Editar, Eliminar) | `uni_write` |
| **Consultor** | `viewer` | `viewer123` | **Solo Lectura** (Consultar expediente e índex) | `uni_read` |

---

## 🗄️ 4. Credenciales y Conexión a Base de Datos (MS SQL Server 2022)

Para conectarte desde **DBeaver**, **Azure Data Studio** o **SQL Server Management Studio (SSMS)**:

- **Host / Server:** `localhost,1433` *(o `127.0.0.1,1433`)*
- **Base de Datos:** `UniNotasDB`
- **Trust Server Certificate:** `True` / `Yes`

### Cuentas SQL Configuradas:

1. **Usuario Administrador SQL (SA):**
   - **Login:** `sa`
   - **Password:** `SuperSecretPass123!`
   - **Rol:** `sysadmin`

2. **Usuario Lectura / Escritura (Aplicación Admin):**
   - **Login:** `uni_write`
   - **Password:** `WriteAll#2024`
   - **Roles en UniNotasDB:** `db_datareader`, `db_datawriter`

3. **Usuario Solo Lectura (Aplicación Viewer):**
   - **Login:** `uni_read`
   - **Password:** `ReadOnly#2024`
   - **Roles en UniNotasDB:** `db_datareader`

---

## 🛠️ 5. Comandos Útiles de Administración

```bash
# Ver estado de los contenedores y healthchecks
docker compose ps

# Ver logs en tiempo real de toda la infraestructura
docker compose logs -f

# Ver logs únicamente del servidor Web PHP
docker compose logs -f web

# Ver logs únicamente de SQL Server
docker compose logs -f sqlserver

# Detener los contenedores sin borrar volúmenes
docker compose down

# Detener y borrar volúmenes persistentes (para reiniciar la BD desde cero)
docker compose down -v
```

---

## 🏗️ 6. Arquitectura del Proyecto (MVC Puro PHP 8.0)

El proyecto implementa una arquitectura **MVC (Modelo-Vista-Controlador)** estricta sin librerías externas ni gestores de paquetes.

```
/
├── docker-compose.yml         # Orquestación de contenedores (web + sqlserver)
├── .env.example               # Plantilla de variables de entorno
├── README.md                  # Documentación técnica completa
├── app/                       # Código fuente PHP 8.0
│   ├── Dockerfile             # Imagen Apache + PHP 8.0 + pdo_sqlsrv
│   ├── public/                # DocumentRoot público
│   │   ├── index.php          # Front Controller & Router Bootstrap
│   │   ├── css/style.css      # Hoja de estilos Vanilla CSS
│   │   └── js/app.js          # JavaScript Vanilla (Cálculo en vivo y validación)
│   ├── config/
│   │   └── database.php       # Conexión PDO SQLSRV Dinámica según Rol
│   ├── core/
│   │   ├── Controller.php     # Clase base de Controladores (Render, RBAC, XSS)
│   │   ├── Model.php          # Clase base de Modelos (Instancia PDO)
│   │   └── Router.php         # Enrutador dinámico de URLs amigables
│   ├── controllers/
│   │   ├── AlumnoController.php # CRUD de Alumnos y Validaciones
│   │   └── AuthController.php   # Login, Logout y Gestión de Sesiones
│   ├── models/
│   │   ├── Alumno.php         # Operaciones SQL Server (Paginated, C/U/D)
│   │   └── Carrera.php        # Consulta de Carreras Universitarias
│   ├── views/                 # Plantillas HTML5
│   │   ├── layout/            # Header y Footer compartidos
│   │   ├── auth/              # Formulario de Login
│   │   └── alumnos/           # Vistas index, create, edit, show
│   └── .htaccess              # Reglas de mod_rewrite para Apache
└── db/                        # Base de datos MS SQL Server 2022
    ├── Dockerfile             # Imagen SQL Server 2022 personalizada
    ├── entrypoint.sh          # Script bash de inicio automatizado
    └── init/
        ├── 01_schema.sql      # DDL: Base de datos, Tablas y Columna Calculada PERSISTED
        ├── 02_users.sql       # DCL: Logins SQL uni_read y uni_write con RBAC
        └── 03_seed.sql        # DML: Semilla con 100 alumnos realistas
```

---

## 🎯 7. Notas Clave para el Entrevistador

1. **Aislamiento en Red Interna de Docker (`uninotas_net`):**
   - El servicio `web` interactúa con el contenedor `sqlserver` utilizando el nombre de host interno `sqlserver` (resolución DNS propia de Docker), sin emplear jamás `localhost`.
2. **Seguridad a Nivel de Base de Datos (RBAC en Conexión PDO):**
   - No solo se restringe la UI según el rol del usuario; la clase `Database.php` conmuta automáticamente las credenciales PDO. Si inicia sesión como `viewer`, PHP se conecta a SQL Server utilizando la cuenta `uni_read`. Si intenta ejecutar un `INSERT` o `UPDATE` mediante inyección o modificación de código, el propio motor de MS SQL Server rechazará la operación por falta de permisos (`db_datawriter`).
3. **Columna Calculada Persistida (`PERSISTED`):**
   - En la tabla `Alumnos`, el campo `total` está definido como `AS (nota_corte1 + nota_corte2 + nota_corte3) PERSISTED`. Esto garantiza que la suma se calcula de forma física en el disco al insertar/actualizar, optimizando el rendimiento de las lecturas y paginaciones.
4. **Prevención de SQL Injection y XSS:**
   - Todas las consultas del modelo `Alumno.php` utilizan **PDO Prepared Statements** con binding explícito de tipos.
   - Toda la salida en vistas se pasa a través de `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` o el helper `Controller::e()`.

---

## ❓ 8. Preguntas Probables del Entrevistador y Respuestas Modelo

### Q1: ¿Por qué no usar `localhost` para conectar PHP con SQL Server en Docker?
**Respuesta:** En Docker, cada contenedor se ejecuta en su propio namespace de red aislado. `localhost` o `127.0.0.1` dentro del contenedor `web` hace referencia al propio contenedor web. Para comunicar ambos contenedores, Docker proporciona una red virtual de puente (bridge network) con resolución DNS integrada que mapea el nombre del servicio `sqlserver` definido en `docker-compose.yml` a la IP privada interna asignada.

### Q2: ¿Cómo funciona la arquitectura MVC pura implementada sin frameworks?
**Respuesta:** Utiliza el patrón **Front Controller**. Apache redirige todas las peticiones a `public/index.php` usando `mod_rewrite`. `index.php` instancia la clase `Router`, la cual analiza la URL (ej: `/alumnos/edit/5`), resuelve el controlador (`AlumnoController`), el método (`edit`) y pasa los argumentos (`5`). El controlador consulta el modelo (`Alumno.php`), el cual ejecuta consultas PDO y retorna arreglos asociativos que finalmente son renderizados por la vista correspondiente (`views/alumnos/edit.php`).

### Q3: ¿Cómo garantiza la aplicación la prevención contra inyecciones SQL (SQL Injection)?
**Respuesta:** Utilizando exclusivamente **Sentencias Preparadas (Prepared Statements)** mediante PDO. Al compilar la consulta SQL por separado de los datos transmitidos (`bindValue` o `execute([params])`), el motor de la base de datos trata los valores suministrados estrictamente como literales de datos y jamás como código SQL ejecutable, anulando cualquier intento de inyección.

### Q4: ¿Qué ventaja ofrece declarar la columna `total` como `PERSISTED` en SQL Server?
**Respuesta:** Una columna calculada por defecto se evalúa en tiempo de ejecución cada vez que se realiza un `SELECT`. Al marcarla como `PERSISTED`, MS SQL Server calcula el valor en el momento del `INSERT` o `UPDATE` y lo almacena físicamente en las páginas de datos del disco. Esto permite indizar la columna y mejora drásticamente el rendimiento de lecturas y consultas de rango en grandes volúmenes de datos.

### Q5: ¿Cómo se implementa la seguridad por roles (RBAC) tanto en aplicación como en base de datos?
**Respuesta:** En la capa de aplicación, el controlador verifica el rol guardado en `$_SESSION['user_role']`. En la capa de infraestructura/BD, la fábrica de conexiones PDO (`Database::getConnection()`) conmuta las credenciales del DSN: los usuarios de rol 'admin' se conectan con la cuenta `uni_write` (con roles `db_datareader` y `db_datawriter`), mientras que usuarios 'viewer' se conectan con `uni_read` (rol exclusivo `db_datareader`). Así, la seguridad está blindada a nivel del motor SQL.

### Q6: ¿Por qué es necesario el indicador `TrustServerCertificate=true` en el DSN de PDO SQLSRV?
**Respuesta:** Microsoft ODBC Driver 18 habilita por defecto el cifrado TLS para todas las conexiones. En entornos de desarrollo en contenedores donde SQL Server genera un certificado autofirmado en lugar de uno emitido por una Autoridad Certificadora (CA) confiable, el cliente PDO rechazará la conexión a menos que se especifique `TrustServerCertificate=true` para confiar en el certificado del servidor local.

### Q7: ¿Cómo se asegura la salud e inicialización idónea de SQL Server antes de iniciar la app web?
**Respuesta:** En `docker-compose.yml`, el servicio `web` declara `depends_on: sqlserver` con `condition: service_healthy`. El servicio `sqlserver` ejecuta un `healthcheck` periódico utilizando el binario `sqlcmd` para enviar `SELECT 1`. De este modo, Apache/PHP no aceptará tráfico HTTP hasta que el motor de base de datos haya arrancado completamente y ejecutado los scripts SQL de inicio.

### Q8: ¿Cómo se maneja la paginación de manera eficiente en MS SQL Server 2022?
**Respuesta:** Se utiliza la sintaxis moderna de SQL Server `ORDER BY columna OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY`. A diferencia de subconsultas antiguas con `ROW_NUMBER()`, esta cláusula nativa optimiza la lectura en el plan de ejecución limitando los registros leídos del índice.

### Q9: ¿Qué medidas se aplicaron para evitar ataques XSS (Cross-Site Scripting)?
**Respuesta:** Todo valor dinámico impreso dentro de la estructura HTML en las vistas es procesado obligatoriamente por la función nativa `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. Esto convierte caracteres especiales como `<`, `>`, `&`, `"`, y `'` en sus entidades HTML correspondientes, impidiendo que código script inyectado por usuarios sea ejecutado por el navegador del cliente.

### Q10: ¿Qué buenas prácticas DevOps se aplicaron en los Dockerfiles?
**Respuesta:**
1. Separación estricta de responsabilidades entre contenedores (Web y BD).
2. Limpieza del caché de paquetes `apt-get clean && rm -rf /var/lib/apt/lists/*` para reducir el tamaño final de la imagen Docker.
3. Uso de archivos `.env` y variables de entorno para evitar almacenar secretos duros (hardcoded secrets) en los repositorios de código.
4. Normalización de saltos de línea LF con `sed` en `entrypoint.sh` para evitar errores de sintaxis POSIX al ejecutar en Linux.
