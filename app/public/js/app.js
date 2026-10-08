/**
 * =============================================================================
 * UNINOTAS - JAVASCRIPT PRINCIPAL (VANILLA JS SIN LIBRERÍAS)
 * =============================================================================
 * Proporciona interactividad cliente-side:
 * - Confirmación de eliminación de registros
 * - Cálculo automático en tiempo real de la nota total
 * - Validaciones preventivas de formulario (Cédula V-######## y Rango 0-20)
 */

document.addEventListener('DOMContentLoaded', function() {

    // -------------------------------------------------------------------------
    // 1. CONFIRMACIÓN AL ELIMINAR ESTUDIANTE
    // -------------------------------------------------------------------------
    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const studentName = this.getAttribute('data-name') || 'este estudiante';
            const confirmed = confirm(`¿Está seguro de que desea eliminar a ${studentName} del sistema?\nEsta acción no se puede deshacer.`);
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });

    // -------------------------------------------------------------------------
    // 2. CÁLCULO EN TIEMPO REAL DEL TOTAL DE NOTAS EN CREAR / EDITAR
    // -------------------------------------------------------------------------
    const inputNota1 = document.getElementById('nota_corte1');
    const inputNota2 = document.getElementById('nota_corte2');
    const inputNota3 = document.getElementById('nota_corte3');
    const displayTotal = document.getElementById('nota_total_preview');

    function calculateTotal() {
        if (!displayTotal) return;

        const val1 = parseFloat(inputNota1?.value) || 0;
        const val2 = parseFloat(inputNota2?.value) || 0;
        const val3 = parseFloat(inputNota3?.value) || 0;

        const total = val1 + val2 + val3;
        displayTotal.textContent = total.toFixed(2);

        // Actualizar color según aprobación (Aprobado >= 30 acumulado en 60 o >= 10 en promedio)
        if (total >= 30) {
            displayTotal.className = 'grade-badge grade-pass';
        } else {
            displayTotal.className = 'grade-badge grade-fail';
        }
    }

    if (inputNota1 && inputNota2 && inputNota3) {
        inputNota1.addEventListener('input', calculateTotal);
        inputNota2.addEventListener('input', calculateTotal);
        inputNota3.addEventListener('input', calculateTotal);
        calculateTotal(); // Ejecutar al cargar la vista
    }

    // -------------------------------------------------------------------------
    // 3. VALIDACIÓN CLIENTE-SIDE PREVENTIVA EN FORMULARIOS
    // -------------------------------------------------------------------------
    const studentForm = document.getElementById('alumnoForm');
    if (studentForm) {
        studentForm.addEventListener('submit', function(e) {
            let isValid = true;
            const cedulaInput = document.getElementById('cedula');
            const cedulaError = document.getElementById('cedula_js_error');
            
            // Regex Cédula: V-12345678
            const cedulaRegex = /^V-\d{7,8}$/;
            if (cedulaInput && !cedulaRegex.test(cedulaInput.value.trim())) {
                if (cedulaError) {
                    cedulaError.textContent = 'Formato inválido. Debe ser como V-12345678 (ej: V-10234567).';
                }
                cedulaInput.style.borderColor = '#ef4444';
                isValid = false;
            } else if (cedulaError) {
                cedulaError.textContent = '';
                cedulaInput.style.borderColor = '#e2e8f0';
            }

            // Validar Rango de Notas (0 - 20)
            [inputNota1, inputNota2, inputNota3].forEach((input, index) => {
                if (input) {
                    const val = parseFloat(input.value);
                    const errorEl = document.getElementById(`nota${index + 1}_js_error`);
                    if (isNaN(val) || val < 0 || val > 20) {
                        if (errorEl) errorEl.textContent = 'La nota debe estar entre 0.00 y 20.00';
                        input.style.borderColor = '#ef4444';
                        isValid = false;
                    } else if (errorEl) {
                        errorEl.textContent = '';
                        input.style.borderColor = '#e2e8f0';
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrija los errores marcados en el formulario antes de enviar.');
            }
        });
    }
});
