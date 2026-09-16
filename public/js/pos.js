/**
 * Restaurante Big Pollo — Caja POS, Auditoría & Facturación DIAN (pos.js)
 * Lógica modular para arqueo Z, comprobación de consumo y adición de ítems omitidos
 */

let currentShift = null;
let selectedOrder = null;
let currentMethod = 'cash';
let allMenuProducts = [];
let auditSearchTerm = '';

document.addEventListener('DOMContentLoaded', async () => {
    await init();
});

async function init() {
    await loadShiftStatus();
    await loadOrders();
    await preloadMenuForAudit();
    setupAuditSearch();
    // Sondeo de comandas cada 6 segundos
    setInterval(loadOrders, 6000);
}

function formatCOP(amount) {
    return '$ ' + Number(amount || 0).toLocaleString('es-CO');
}

/* --------------------------------------------------------------------------
   Gestión del Turno de Caja (Apertura / Arqueo Z)
   -------------------------------------------------------------------------- */
async function loadShiftStatus() {
    try {
        const res = await fetch('/api/cash-shifts/current');
        const data = await res.json();
        currentShift = data.shift;

        const badge = document.getElementById('shift-badge');
        const base = document.getElementById('shift-base');
        const openBtn = document.getElementById('open-shift-btn');
        const closeBtn = document.getElementById('close-shift-btn');

        if (data.is_open && currentShift) {
            badge.className = 'shift-status-pill open';
            badge.innerText = '🟢 Abierta';
            base.innerText = formatCOP(currentShift.opening_amount);
            openBtn.style.display = 'none';
            closeBtn.style.display = 'block';
        } else {
            badge.className = 'shift-status-pill closed';
            badge.innerText = '🔴 Cerrada';
            base.innerText = '$ 0';
            openBtn.style.display = 'block';
            closeBtn.style.display = 'none';
        }
    } catch (e) {
        console.error('Error turno', e);
    }
}

/* --------------------------------------------------------------------------
   Carga y Selección de Comandas de Salón
   -------------------------------------------------------------------------- */
async function loadOrders() {
    try {
        const res = await fetch('/api/orders');
        const json = await res.json();
        const container = document.getElementById('orders-list');
        
        const unpaidOrders = (json.data || []).filter(o => o.status !== 'paid' && o.status !== 'cancelled');

        if (unpaidOrders.length === 0) {
            container.innerHTML = `
                <div class="cart-empty-state" style="padding: 2.5rem 0;">
                    🍽️ No hay comandas pendientes de pago en este momento.
                </div>
            `;
            return;
        }

        container.innerHTML = '';
        unpaidOrders.forEach(ord => {
            const row = document.createElement('div');
            row.className = `order-row ${selectedOrder?.id === ord.id ? 'selected' : ''}`;
            row.onclick = () => selectOrder(ord);

            const minsElapsed = Math.round((new Date() - new Date(ord.created_at)) / 60000);
            const isDelayed = minsElapsed >= 20 && (ord.status === 'in_kitchen' || ord.status === 'pending');

            let statusBadge = '';
            if (ord.status === 'ready') {
                statusBadge = '<span class="status-tag ready">🛎️ Listo para Servir</span>';
            } else if (ord.status === 'served') {
                statusBadge = '<span class="status-tag served">🍽️ Consumiendo en Mesa</span>';
            } else {
                statusBadge = '<span class="status-tag cooking">👨‍🍳 En Cocina</span>';
            }

            if (isDelayed) {
                statusBadge += ` <span class="status-tag delayed">⚠️ +${minsElapsed}m Retraso</span>`;
            }

            row.innerHTML = `
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="order-row-num">#${ord.order_number}</span>
                        <span>${statusBadge}</span>
                    </div>
                    <div class="order-row-meta">
                        ${ord.table ? 'Mesa ' + ord.table.table_number : 'Para Llevar / Domicilio'} · ${minsElapsed} min transcurridos
                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="order-row-total">${formatCOP(ord.total)}</div>
                    <div class="order-row-items-count">${(ord.items || []).length} ítems en comanda</div>
                </div>
            `;
            container.appendChild(row);
        });
    } catch (e) {
        console.error('Error comandas', e);
    }
}

function formatTableLabel(table) {
    if (!table) return 'Para Llevar / Domicilio';
    const num = String(table.table_number || '');
    return num.toLowerCase().startsWith('mesa') ? num : `Mesa ${num}`;
}

function selectOrder(order) {
    selectedOrder = order;
    document.querySelectorAll('.order-row').forEach(r => r.classList.remove('selected'));
    if (window.event && window.event.currentTarget) window.event.currentTarget.classList.add('selected');

    document.getElementById('checkout-content').style.display = 'block';
    document.getElementById('selected-order-info').innerText = `Comanda #${order.order_number} — ${formatTableLabel(order.table)}`;
    
    // Renderizar Desglose de Consumo en Mesa (Auditoría de Caja)
    renderConsumptionDetails(order);

    document.getElementById('co-subtotal').innerText = formatCOP(order.subtotal);
    document.getElementById('co-tax').innerText = formatCOP(order.tax);
    document.getElementById('co-total').innerText = formatCOP(order.total);

    // Estado Factus DIAN
    const dianBox = document.getElementById('dian-box');
    const dianEmitBtn = document.getElementById('dian-emit-btn');
    if (order.electronic_invoice && order.electronic_invoice.status === 'sent_valid') {
        dianBox.style.display = 'block';
        document.getElementById('dian-bill-num').innerText = `Factura DIAN: ${order.electronic_invoice.bill_number}`;
        document.getElementById('dian-cufe').innerText = order.electronic_invoice.cufe;
        document.getElementById('dian-qr-link').href = order.electronic_invoice.qr_url;
        dianEmitBtn.style.display = 'none';
    } else {
        dianBox.style.display = 'none';
        dianEmitBtn.style.display = 'block';
    }
}

/* --------------------------------------------------------------------------
   Auditoría de Consumo de la Mesa & Comprobación de Ítems Omitidos
   -------------------------------------------------------------------------- */
function renderConsumptionDetails(order) {
    const list = document.getElementById('consumption-items-list');
    if (!list) return;

    if (!order.items || order.items.length === 0) {
        list.innerHTML = '<div style="color: var(--text-dim); text-align: center; padding: 0.5rem;">Sin platos registrados.</div>';
        return;
    }

    list.innerHTML = order.items.map(it => {
        const prodName = it.product_name || (it.product ? it.product.name : 'Plato');
        return `
            <div class="consumption-row">
                <div>
                    <span class="consumption-item-name">${it.quantity}x</span> ${prodName}
                    ${it.notes ? `<div style="font-size: 0.7rem; color: var(--text-dim);">${it.notes}</div>` : ''}
                </div>
                <div class="consumption-item-price">${formatCOP(it.subtotal)}</div>
            </div>
        `;
    }).join('');
}

async function preloadMenuForAudit() {
    try {
        const res = await fetch('/api/menu');
        const data = await res.json();
        allMenuProducts = [];
        (data.data || []).forEach(cat => {
            if (cat.products) {
                cat.products.forEach(p => {
                    allMenuProducts.push({
                        ...p,
                        category_name: cat.name
                    });
                });
            }
        });
    } catch (e) {
        console.error('Error precargando menú para caja', e);
    }
}

function openAuditModal() {
    if (!selectedOrder) {
        if (typeof showToast === 'function') showToast('Selecciona primero una comanda para auditar', 'info');
        return;
    }
    document.getElementById('audit-modal-target-order').innerText = `#${selectedOrder.order_number} (${selectedOrder.table ? 'Mesa ' + selectedOrder.table.table_number : 'Llevar'})`;
    document.getElementById('audit-modal').style.display = 'flex';
    document.getElementById('auditSearchInput').value = '';
    auditSearchTerm = '';
    renderAuditProducts();
}

function closeAuditModal() {
    document.getElementById('audit-modal').style.display = 'none';
}

function setupAuditSearch() {
    const input = document.getElementById('auditSearchInput');
    if (input) {
        input.addEventListener('input', (e) => {
            auditSearchTerm = e.target.value.trim().toLowerCase();
            renderAuditProducts();
        });
    }
}

function renderAuditProducts() {
    const container = document.getElementById('auditProductsContainer');
    if (!container) return;

    let filtered = allMenuProducts;
    if (auditSearchTerm.length > 0) {
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(auditSearchTerm) ||
            (p.category_name && p.category_name.toLowerCase().includes(auditSearchTerm))
        );
    }

    if (filtered.length === 0) {
        container.innerHTML = '<div style="color: var(--text-dim); text-align: center; padding: 2rem;">No se encontraron platos.</div>';
        return;
    }

    container.innerHTML = filtered.map(prod => `
        <div class="audit-product-card">
            <div>
                <div style="font-weight: 800; color: #FFFFFF; font-size: 0.88rem;">${prod.name}</div>
                <div style="font-size: 0.74rem; color: #FEF08A; font-weight: 700;">${formatCOP(prod.price)}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <input type="number" id="auditQty_${prod.id}" value="1" min="1" max="10" style="width: 44px; padding: 0.35rem; border-radius: 6px; background: var(--surface-1); border: 1px solid var(--border-subtle); color: #fff; text-align: center; font-weight: 800;">
                <button type="button" class="audit-btn-add-item" onclick="submitAuditItem(${prod.id}, '${prod.name.replace(/'/g, "\\'")}', ${prod.price})">
                    ➕ Adicionar
                </button>
            </div>
        </div>
    `).join('');
}

async function submitAuditItem(productId, name, price) {
    if (!selectedOrder) return;
    const qtyInput = document.getElementById(`auditQty_${productId}`);
    const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;

    // Si tiene mesa asignada, usamos el endpoint de adición de mesa
    const tableId = selectedOrder.restaurant_table_id || (selectedOrder.table ? selectedOrder.table.id : null);

    if (!tableId) {
        if (typeof showToast === 'function') showToast('Esta orden no tiene mesa vinculada para adición automática', 'error');
        return;
    }

    try {
        const res = await fetch(`/api/tables/${tableId}/add-items`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                items: [{
                    product_id: productId,
                    quantity: qty,
                    notes: 'Adición por auditoría en caja'
                }]
            })
        });

        const data = await res.json();
        if (res.ok) {
            if (typeof showToast === 'function') {
                showToast(`¡Agregado ${qty}x ${name} a la cuenta!`, 'success');
            }
            closeAuditModal();
            // Refrescar comanda
            await loadOrders();
            // Volver a seleccionar la orden actualizada
            const updatedRes = await fetch(`/api/orders/${selectedOrder.id}`);
            const updatedJson = await updatedRes.json();
            if (updatedJson.data) {
                selectOrder(updatedJson.data);
            }
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Error al adicionar', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error de conexión', 'error');
    }
}

/* --------------------------------------------------------------------------
   Procesamiento de Pago y Emisión DIAN
   -------------------------------------------------------------------------- */
function setPaymentMethod(method, btn) {
    currentMethod = method;
    document.querySelectorAll('.payment-methods-grid .method-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

async function payOrder() {
    if (!selectedOrder) return;

    try {
        const res = await fetch(`/api/orders/${selectedOrder.id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ 
                status: 'paid',
                payment_method: currentMethod
            })
        });

        if (res.ok) {
            if (typeof showToast === 'function') showToast(`¡Comanda #${selectedOrder.order_number} cobrada con éxito (${currentMethod})!`, 'success');
            selectedOrder = null;
            document.getElementById('checkout-content').style.display = 'none';
            await loadOrders();
        } else {
            if (typeof showToast === 'function') showToast('Error al procesar cobro', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error de conexión', 'error');
    }
}

async function emitDianInvoice() {
    if (!selectedOrder) return;

    const btn = document.getElementById('dian-emit-btn');
    btn.disabled = true;
    btn.innerText = '⏳ Timbrando ante la DIAN vía Factus...';

    try {
        const res = await fetch(`/api/orders/${selectedOrder.id}/invoice`, {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        });

        const json = await res.json();

        if (res.ok && json.data.electronic_invoice.status === 'sent_valid') {
            if (typeof showToast === 'function') showToast('🎉 ¡Factura Electrónica aprobada por la DIAN!', 'success');
            selectOrder(json.data);
        } else {
            if (typeof showToast === 'function') showToast(json.data?.electronic_invoice?.error_message || 'Error emitiendo factura DIAN', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error comunicando con Factus', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>📄</span> Emitir Factura DIAN con Factus';
    }
}

/* --------------------------------------------------------------------------
   Modales de Apertura y Cierre Z
   -------------------------------------------------------------------------- */
function openShiftModal() { 
    document.getElementById('open-modal').style.display = 'flex'; 
}

function closeShiftModal() { 
    document.getElementById('close-modal').style.display = 'flex';
    document.getElementById('close-cash-input').value = currentShift?.expected_cash || 0;
}

function closeModal(id) { 
    document.getElementById(id).style.display = 'none'; 
}

async function submitOpenShift() {
    const amount = document.getElementById('open-base-input').value;
    try {
        const res = await fetch('/api/cash-shifts/open', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ opening_amount: parseFloat(amount) })
        });

        if (res.ok) {
            if (typeof showToast === 'function') showToast('Turno de caja aperturado con base inicial', 'success');
            closeModal('open-modal');
            await loadShiftStatus();
        } else {
            const err = await res.json();
            if (typeof showToast === 'function') showToast(err.message || 'Error abriendo caja', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error en petición', 'error');
    }
}

async function submitCloseShift() {
    const amount = document.getElementById('close-cash-input').value;
    try {
        const res = await fetch('/api/cash-shifts/close', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ actual_cash_counted: parseFloat(amount) })
        });

        if (res.ok) {
            const json = await res.json();
            const diff = json.shift.difference;
            const diffMsg = diff === 0 ? 'Cuadre exacto ($0)' : (diff > 0 ? `Sobrante: +${formatCOP(diff)}` : `Faltante: ${formatCOP(diff)}`);
            if (typeof showToast === 'function') showToast(`¡Caja Z cerrada! ${diffMsg}`, 'success');
            closeModal('close-modal');
            await loadShiftStatus();
        } else {
            if (typeof showToast === 'function') showToast('Error cerrando caja', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error en petición', 'error');
    }
}
