<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h1 class="card-title">✏️ Editar Estudiante: <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido'], ENT_QUOTES, 'UTF-8') ?></h1>
        <a href="/alumnos" class="btn btn-secondary">← Volver al Listado</a>
    </div>

    <form action="/alumnos/update/<?= $alumno['id'] ?>" method="POST" id="alumnoForm">
        <div class="form-row">
            <div class="form-group">
                <label for="cedula" class="form-label">Cédula de Identidad (Formato V-########): *</label>
                <input type="text" id="cedula" name="cedula" class="form-control" 
                       value="<?= htmlspecialchars($alumno['cedula'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (!empty($errors['cedula'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['cedula'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="form-error" id="cedula_js_error"></div>
            </div>

            <div class="form-group">
                <label for="carrera_id" class="form-label">Carrera Universitaria: *</label>
                <select id="carrera_id" name="carrera_id" class="form-control" required>
                    <option value="">-- Seleccione una carrera --</option>
                    <?php foreach ($carreras as $carrera): ?>
                        <option value="<?= $carrera['id'] ?>" 
                            <?= (string)($alumno['carrera_id'] ?? '') === (string)$carrera['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($carrera['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['carrera_id'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['carrera_id'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre(s): *</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($alumno['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (!empty($errors['nombre'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="apellido" class="form-label">Apellido(s): *</label>
                <input type="text" id="apellido" name="apellido" class="form-control" 
                       value="<?= htmlspecialchars($alumno['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (!empty($errors['apellido'])): ?>
                    <div class="form-error"><?= htmlspecialchars($errors['apellido'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <fieldset style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: 600; color: var(--primary-color);">📝 Evaluaciones (Cortes 0 - 20 Pts)</legend>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nota_corte1" class="form-label">Nota Corte 1 (0-20): *</label>
                    <input type="number" step="0.01" min="0" max="20" id="nota_corte1" name="nota_corte1" class="form-control" 
                           value="<?= htmlspecialchars($alumno['nota_corte1'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (!empty($errors['nota_corte1'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['nota_corte1'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div class="form-error" id="nota1_js_error"></div>
                </div>

                <div class="form-group">
                    <label for="nota_corte2" class="form-label">Nota Corte 2 (0-20): *</label>
                    <input type="number" step="0.01" min="0" max="20" id="nota_corte2" name="nota_corte2" class="form-control" 
                           value="<?= htmlspecialchars($alumno['nota_corte2'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (!empty($errors['nota_corte2'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['nota_corte2'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div class="form-error" id="nota2_js_error"></div>
                </div>

                <div class="form-group">
                    <label for="nota_corte3" class="form-label">Nota Corte 3 (0-20): *</label>
                    <input type="number" step="0.01" min="0" max="20" id="nota_corte3" name="nota_corte3" class="form-control" 
                           value="<?= htmlspecialchars($alumno['nota_corte3'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (!empty($errors['nota_corte3'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['nota_corte3'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div class="form-error" id="nota3_js_error"></div>
                </div>
            </div>

            <!-- Preview Total en Tiempo Real (JS) -->
            <div style="margin-top: 0.5rem; background: #f8fafc; padding: 0.75rem; border-radius: 6px; display: flex; align-items: center; justify-content: space-between;">
                <span>Total Acumulado Calculado:</span>
                <span id="nota_total_preview" class="grade-badge grade-fail">0.00</span>
            </div>
        </fieldset>

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="/alumnos" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">🔄 Actualizar Estudiante</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
