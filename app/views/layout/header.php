<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'UniNotas - Sistema de Gestión Académica', ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Hoja de Estilos Propia (Vanilla CSS) -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

    <!-- Barra de Navegación Superior -->
    <header class="navbar">
        <div class="navbar-container">
            <a href="/alumnos" class="navbar-brand">
                🎓 <span>UniNotas</span>
            </a>

            <?php if (!empty($_SESSION['logged_in'])): ?>
                <div class="navbar-user">
                    <span>Bienvenido, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></strong></span>
                    
                    <!-- Badge de Rol de Seguridad -->
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <span class="role-badge admin">Admin (uni_write)</span>
                    <?php else: ?>
                        <span class="role-badge viewer">Consultor (uni_read)</span>
                    <?php endif; ?>

                    <a href="/auth/logout" class="btn btn-secondary btn-sm">Cerrar Sesión</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenedor Principal de Contenido -->
    <main class="main-container">

        <!-- Mensajes Flash de Éxito o Error -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                ⚠️ <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
