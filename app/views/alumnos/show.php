<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h1 class="card-title">📄 Expediente Académico del Estudiante</h1>
        <a href="/alumnos" class="btn btn-secondary">← Volver al Listado</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Cédula de Identidad:</p>
            <p style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">
                <?= htmlspecialchars($alumno['cedula'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Nombre Completo:</p>
            <p style="font-size: 1.2rem; font-weight: 600;">
                <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Carrera Universitaria:</p>
            <p style="font-size: 1.1rem; font-weight: 500;">
                🎓 <?= htmlspecialchars($alumno['carrera_nombre'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div>
            <p style="color: var(--text-secondary); font-size: 0.85rem;">Fecha de Registro:</p>
            <p style="font-size: 0.95rem; color: var(--text-primary);">
                📅 <?= date('d/m/Y h:i A', strtotime($alumno['fecha_registro'])) ?>
            </p>
        </div>
    </div>

    <!-- Desglose de Evaluaciones -->
    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem; color: var(--primary-color); font-size: 1.1rem;">📊 Calificaciones Obtenidas</h3>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
            <div style="background: #ffffff; padding: 1rem; border-radius: 6px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.8rem; color: var(--text-secondary); display: block;">Nota Corte 1</span>
                <strong style="font-size: 1.4rem; color: var(--primary-color);"><?= number_format($alumno['nota_corte1'], 2) ?></strong>
                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;">/ 20.00 pts</span>
            </div>

            <div style="background: #ffffff; padding: 1rem; border-radius: 6px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.8rem; color: var(--text-secondary); display: block;">Nota Corte 2</span>
                <strong style="font-size: 1.4rem; color: var(--primary-color);"><?= number_format($alumno['nota_corte2'], 2) ?></strong>
                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;">/ 20.00 pts</span>
            </div>

            <div style="background: #ffffff; padding: 1rem; border-radius: 6px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.8rem; color: var(--text-secondary); display: block;">Nota Corte 3</span>
                <strong style="font-size: 1.4rem; color: var(--primary-color);"><?= number_format($alumno['nota_corte3'], 2) ?></strong>
                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;">/ 20.00 pts</span>
            </div>
        </div>

        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="font-weight: 600; font-size: 1rem;">Puntaje Total Acumulado (Columna Calculada PERSISTED):</span>
                <p style="font-size: 0.8rem; color: var(--text-secondary);">Generado directamente por el motor de MS SQL Server 2022</p>
            </div>
            
            <?php $total = (float) $alumno['total']; ?>
            <div style="text-align: right;">
                <span class="grade-badge <?= $total >= 30.0 ? 'grade-pass' : 'grade-fail' ?>" style="font-size: 1.2rem; padding: 0.4rem 1rem;">
                    <?= number_format($total, 2) ?> / 60.00 pts
                </span>
                <span style="display: block; font-size: 0.85rem; font-weight: 600; margin-top: 0.3rem; color: <?= $total >= 30.0 ? 'var(--success-text)' : 'var(--error-text)' ?>;">
                    ESTADO: <?= $total >= 30.0 ? 'APROBADO' : 'REPROBADO' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Botones de Acción según Rol -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <a href="/alumnos" class="btn btn-secondary">← Volver al Listado</a>

        <?php if ($userRole === 'admin'): ?>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/alumnos/edit/<?= $alumno['id'] ?>" class="btn btn-primary">✏️ Editar Estudiante</a>
                <a href="/alumnos/delete/<?= $alumno['id'] ?>" 
                   class="btn btn-danger btn-delete-confirm" 
                   data-name="<?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido'], ENT_QUOTES, 'UTF-8') ?>">🗑️ Eliminar</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
