/**
 * Big Pollo — Editor Visual de Plano del Salón (floor_plan.js)
 * Drag & Drop con snap to grid, CRUD de mesas, persistencia vía API REST.
 * Separación estricta de responsabilidades (SRP). Sin hardcoding de IDs.
 */

// ── Estado del editor ──────────────────────────────────────────────────────
let fpTables       = [];     // array de mesas cargadas desde la API
let isDirty        = false;  // hay cambios sin guardar
let snapEnabled    = true;   // snap a cuadrícula activo
let selectedShape  = 'square';

// Mesa actualmente seleccionada en el modal de edición
let fpSelectedTable = null;

const SNAP_GRID_PERCENT = 2;  // 2% de paso de cuadrícula

// ── Bootstrap ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    await loadFpTables();
    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '¿Salir sin guardar los cambios del plano?';
        }
    });
});

/* ============================================================
   CARGA Y RENDERIZADO
   ============================================================ */
async function loadFpTables() {
    try {
        const res  = await fetch('/api/tables');
        const data = await res.json();
        fpTables   = data.data || [];
        renderFpTables();
    } catch (e) {
        console.error('Error cargando mesas del plano:', e);
        showFpToast('Error al cargar las mesas. Recarga la página.', 'error');
    }
}

function renderFpTables() {
    const layer = document.getElementById('fpTablesLayer');
    if (!layer) return;
    layer.innerHTML = '';

    fpTables.forEach(table => {
        const el = createFpTableElement(table);
        layer.appendChild(el);
    });

    const countEl = document.getElementById('fpTableCount');
    if (countEl) countEl.innerText = `${fpTables.length} mesa${fpTables.length !== 1 ? 's' : ''} en el plano`;
}

function createFpTableElement(table) {
    const shape    = table.shape || 'square';
    const isActive = table.status === 'occupied';

    const el = document.createElement('div');
    el.className = [
        'fp-table',
        `fp-table--${shape}`,
        isActive ? 'fp-table--active' : 'fp-table--free',
    ].join(' ');

    el.dataset.tableId = table.id;
    el.style.cssText   = `left:${table.pos_x ?? 10}%; top:${table.pos_y ?? 10}%;`;
    el.title           = `${table.table_number} — ${table.capacity} personas — ${table.zone ?? ''}`;

    el.innerHTML = `
        <div class="fp-table-num">${table.table_number.replace('Mesa ', '')}</div>
        <div class="fp-table-cap">${table.capacity}p</div>
        ${isActive ? '<div class="fp-table-active-dot">🟠</div>' : ''}
    `;

    // Clic para abrir modal de edición
    el.addEventListener('click', (e) => {
        if (el.classList.contains('fp-is-dragging')) return;
        openFpTableModal(table);
    });

    // Drag & Drop libre en el lienzo
    setupFpDrag(el, table);

    return el;
}

/* ============================================================
   DRAG & DROP EN EL LIENZO DEL EDITOR
   ============================================================ */
function setupFpDrag(el, tableRef) {
    let isDragging = false;
    let startX, startY, startLeft, startTop;

    el.addEventListener('pointerdown', (e) => {
        e.preventDefault();
        e.target.setPointerCapture(e.pointerId);
        const canvas = document.getElementById('fpCanvas');
        const rect   = canvas.getBoundingClientRect();
        startX    = e.clientX;
        startY    = e.clientY;
        startLeft = parseFloat(el.style.left);
        startTop  = parseFloat(el.style.top);
        isDragging = false;
    });

    el.addEventListener('pointermove', (e) => {
        e.preventDefault();
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        if (!isDragging && (Math.abs(dx) > 5 || Math.abs(dy) > 5)) {
            isDragging = true;
            el.classList.add('fp-is-dragging');
        }

        if (!isDragging) return;

        const canvas = document.getElementById('fpCanvas');
        const rect   = canvas.getBoundingClientRect();

        let newX = startLeft + (dx / rect.width) * 100;
        let newY = startTop  + (dy / rect.height) * 100;

        // Clamping para no salir del lienzo
        newX = Math.max(1, Math.min(97, newX));
        newY = Math.max(1, Math.min(97, newY));

        // Snap to grid
        if (snapEnabled) {
            newX = Math.round(newX / SNAP_GRID_PERCENT) * SNAP_GRID_PERCENT;
            newY = Math.round(newY / SNAP_GRID_PERCENT) * SNAP_GRID_PERCENT;
        }

        el.style.left = `${newX}%`;
        el.style.top  = `${newY}%`;

        // Actualizar en memoria
        const tableData = fpTables.find(t => t.id === tableRef.id);
        if (tableData) {
            tableData.pos_x = newX;
            tableData.pos_y = newY;
        }

        markDirty();
    });

    el.addEventListener('pointerup', (e) => {
        el.classList.remove('fp-is-dragging');
        el.releasePointerCapture(e.pointerId);
        isDragging = false;
    });

    el.addEventListener('pointercancel', () => {
        el.classList.remove('fp-is-dragging');
        isDragging = false;
    });
}

/* ============================================================
   FORM: NUEVA MESA
   ============================================================ */
function selectShape(shape) {
    selectedShape = shape;
    document.querySelectorAll('.fp-shape-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.shape === shape);
    });
}

async function addNewTable() {
    const number   = document.getElementById('newTableNumber').value.trim();
    const capacity = parseInt(document.getElementById('newTableCapacity').value);
    const zone     = document.getElementById('newTableZone').value;

    if (!number) {
        showFpToast('Ingresa el número o nombre de la mesa', 'error');
        return;
    }

    try {
        const res  = await fetch('/api/admin/tables', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                table_number: number,
                capacity,
                shape: selectedShape,
                zone,
                pos_x: 50,
                pos_y: 50,
            }),
        });
        const data = await res.json();
        if (res.ok) {
            showFpToast(data.message, 'success');
            document.getElementById('newTableNumber').value = '';
            await loadFpTables();
        } else {
            showFpToast(data.message || 'Error al agregar la mesa', 'error');
        }
    } catch (e) {
        showFpToast('Error de conexión al agregar mesa', 'error');
    }
}

/* ============================================================
   MODAL DE EDICIÓN DE MESA
   ============================================================ */
function openFpTableModal(table) {
    fpSelectedTable = table;
    document.getElementById('fpModalTitle').innerText = `🪑 ${table.table_number}`;
    document.getElementById('editTableNumber').value   = table.table_number;
    document.getElementById('editTableCapacity').value = table.capacity;
    document.getElementById('editTableZone').value     = table.zone || 'calle';

    // Deshabilitar eliminación si está ocupada
    const deleteBtn = document.getElementById('fpBtnDelete');
    if (deleteBtn) {
        deleteBtn.disabled = table.status === 'occupied';
        deleteBtn.title    = table.status === 'occupied'
            ? 'No se puede eliminar: mesa con comanda activa'
            : 'Eliminar esta mesa del plano';
    }

    document.getElementById('fpTableModal').style.display = 'flex';
}

function closeFpModal() {
    document.getElementById('fpTableModal').style.display = 'none';
    fpSelectedTable = null;
}

async function applyTableEdit() {
    if (!fpSelectedTable) return;

    const number   = document.getElementById('editTableNumber').value.trim();
    const capacity = parseInt(document.getElementById('editTableCapacity').value);
    const zone     = document.getElementById('editTableZone').value;

    if (!number) {
        showFpToast('El nombre de mesa no puede estar vacío', 'error');
        return;
    }

    // Actualizar en memoria primero (optimista)
    const tableData = fpTables.find(t => t.id === fpSelectedTable.id);
    if (tableData) {
        tableData.table_number = number;
        tableData.capacity     = capacity;
        tableData.zone         = zone;
    }

    markDirty();
    closeFpModal();
    renderFpTables();
    showFpToast('Cambios aplicados. Guarda el plano para persistirlos.', 'info');
}

async function deleteSelectedTable() {
    if (!fpSelectedTable) return;
    if (fpSelectedTable.status === 'occupied') {
        showFpToast('No puedes eliminar una mesa con comanda activa', 'error');
        return;
    }
    if (!confirm(`¿Eliminar permanentemente la "${fpSelectedTable.table_number}" del plano?`)) return;

    try {
        const res  = await fetch(`/api/admin/tables/${fpSelectedTable.id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (res.ok) {
            showFpToast(data.message, 'success');
            closeFpModal();
            await loadFpTables();
        } else {
            showFpToast(data.message || 'Error al eliminar la mesa', 'error');
        }
    } catch (e) {
        showFpToast('Error de conexión al eliminar', 'error');
    }
}

/* ============================================================
   GUARDAR DISTRIBUCIÓN
   ============================================================ */
async function saveLayout() {
    const btn = document.getElementById('fpBtnSave');
    if (btn) { btn.disabled = true; btn.innerText = '⏳ Guardando...'; }

    const payload = {
        tables: fpTables.map(t => ({
            id:    t.id,
            pos_x: t.pos_x ?? 10,
            pos_y: t.pos_y ?? 10,
            zone:  t.zone  ?? 'calle',
            shape: t.shape ?? 'square',
        })),
    };

    try {
        const res  = await fetch('/api/tables/layout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (res.ok) {
            isDirty = false;
            setStatusBadge('saved');
            showFpToast('✅ ' + data.message, 'success');
        } else {
            showFpToast(data.message || 'Error al guardar distribución', 'error');
        }
    } catch (e) {
        showFpToast('Error de conexión al guardar', 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '💾 Guardar Distribución'; }
    }
}

/* ============================================================
   RESTAURAR DISTRIBUCIÓN ORIGINAL
   ============================================================ */
function confirmReset() {
    document.getElementById('fpResetModal').style.display = 'flex';
}

function closeResetModal() {
    document.getElementById('fpResetModal').style.display = 'none';
}

async function executeReset() {
    closeResetModal();
    try {
        const res  = await fetch('/api/tables/layout/reset', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (res.ok) {
            showFpToast(data.message, 'success');
            isDirty = false;
            setStatusBadge('saved');
            await loadFpTables();
        } else {
            showFpToast(data.message || 'Error al restaurar plano', 'error');
        }
    } catch (e) {
        showFpToast('Error de conexión al restaurar', 'error');
    }
}

/* ============================================================
   UTILIDADES
   ============================================================ */
function toggleSnap(enabled) {
    snapEnabled = enabled;
    showFpToast(enabled ? 'Snap activado: cuadrícula 2%' : 'Snap desactivado: posición libre', 'info');
}

function markDirty() {
    isDirty = true;
    setStatusBadge('unsaved');
}

function setStatusBadge(state) {
    const badge = document.getElementById('fpStatusBadge');
    if (!badge) return;
    if (state === 'saved') {
        badge.className = 'fp-status-badge fp-status-saved';
        badge.innerText = '✅ Guardado';
    } else {
        badge.className = 'fp-status-badge fp-status-unsaved';
        badge.innerText = '⚠️ Cambios sin guardar';
    }
}

function showFpToast(message, type = 'info') {
    // Reutilizar la función global de toast si existe (del layout)
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    // Fallback propio si no hay showToast global
    const toastId = `fp-toast-${Date.now()}`;
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.style.cssText = `
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        background: ${type === 'error' ? '#EF4444' : type === 'success' ? '#10B981' : '#3B82F6'};
        color: #fff; padding: 0.85rem 1.5rem; border-radius: 12px;
        font-weight: 700; font-size: 0.9rem; box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        animation: fadeInUp 0.3s ease;
        max-width: 380px;
    `;
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
