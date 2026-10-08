<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="login-wrapper">
    <div class="card login-card">
        <div class="card-header">
            <h2 class="card-title">🔐 Iniciar Sesión en UniNotas</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="/auth/login" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Usuario:</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ej: admin o viewer" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Ingresar al Sistema</button>
        </form>

        <div style="margin-top: 1.5rem; background: #f1f5f9; padding: 1rem; border-radius: 6px; font-size: 0.85rem;">
            <p><strong>Credenciales de Prueba disponibles:</strong></p>
            <ul style="margin-left: 1.2rem; margin-top: 0.4rem;">
                <li><strong>Administrador:</strong> <code>admin</code> / <code>admin123</code> (Acceso C/U/D con usuario BD <code>uni_write</code>)</li>
                <li><strong>Consultor:</strong> <code>viewer</code> / <code>viewer123</code> (Acceso Solo Lectura con usuario BD <code>uni_read</code>)</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
