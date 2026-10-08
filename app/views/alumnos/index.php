<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">📚 Listado de Estudiantes y Calificaciones</h1>
        
        <?php if ($userRole === 'admin'): ?>
            <a href="/alumnos/create" class="btn btn-primary">
                ➕ Registrar Estudiante
            </a>
        <?php endif; ?>
    </div>

    <!-- Formulario de Búsqueda -->
    <form action="/alumnos" method="GET" class="search-form">
        <input type="text" name="search" class="form-control" 
               placeholder="Buscar por Cédula, Nombre, Apellido o Carrera..." 
               value="<?= htmlspecialchars($pagination['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
        <?php if (!empty($pagination['search'])): ?>
            <a href="/alumnos" class="btn btn-secondary">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla de Resultados -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Estudiante</th>
                    <th>Carrera</th>
                    <th>Corte 1</th>
                    <th>Corte 2</th>
                    <th>Corte 3</th>
                    <th>Total Acumulado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alumnos)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                            No se encontraron registros de estudiantes.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($alumno['cedula'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($alumno['carrera_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format($alumno['nota_corte1'], 2) ?></td>
                            <td><?= number_format($alumno['nota_corte2'], 2) ?></td>
                            <td><?= number_format($alumno['nota_corte3'], 2) ?></td>
                            <td>
                                <!-- Columna calculada PERSISTED proveniente directamente de SQL Server -->
                                <?php $total = (float) $alumno['total']; ?>
                                <span class="grade-badge <?= $total >= 30.0 ? 'grade-pass' : 'grade-fail' ?>">
                                    <?= number_format($total, 2) ?> / 60.00
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.3rem;">
                                    <!-- Ver detalle (Disponible para Admin y Viewer) -->
                                    <a href="/alumnos/show/<?= $alumno['id'] ?>" class="btn btn-secondary btn-sm" title="Ver Detalle">👁️ Ver</a>

                                    <?php if ($userRole === 'admin'): ?>
                                        <!-- Editar y Eliminar (Exclusivo de Admin) -->
                                        <a href="/alumnos/edit/<?= $alumno['id'] ?>" class="btn btn-primary btn-sm" title="Editar">✏️ Editar</a>
                                        
                                        <a href="/alumnos/delete/<?= $alumno['id'] ?>" 
                                           class="btn btn-danger btn-sm btn-delete-confirm" 
                                           data-name="<?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido'], ENT_QUOTES, 'UTF-8') ?>"
                                           title="Eliminar">🗑️</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Control de Paginación -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php 
                $searchQuery = !empty($pagination['search']) ? '&search=' . urlencode($pagination['search']) : '';
                $currentPage = $pagination['current_page'];
                $totalPages = $pagination['total_pages'];
            ?>

            <?php if ($currentPage > 1): ?>
                <a href="/alumnos?page=<?= $currentPage - 1 ?><?= $searchQuery ?>" class="page-link">&laquo; Anterior</a>
            <?php endif; ?>

            <?php 
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                for ($i = $startPage; $i <= $endPage; $i++): 
            ?>
                <a href="/alumnos?page=<?= $i ?><?= $searchQuery ?>" 
                   class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="/alumnos?page=<?= $currentPage + 1 ?><?= $searchQuery ?>" class="page-link">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
        <p style="text-align: center; font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.5rem;">
            Mostrando página <?= $currentPage ?> de <?= $totalPages ?> (Total: <?= $pagination['total_records'] ?> estudiantes)
        </p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
