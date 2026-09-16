@extends('layouts.app')

@section('title', 'Caja & Facturación DIAN')

@section('styles')
<style>
    .pos-grid {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .pos-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Shift Banner */
    .shift-banner {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .shift-status-pill {
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .shift-status-pill.open {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid var(--success-glow);
    }

    .shift-status-pill.closed {
        background: rgba(244, 63, 94, 0.15);
        color: var(--danger);
        border: 1px solid var(--danger-glow);
    }

    /* Orders Table */
    .orders-section {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 18px;
        padding: 1.5rem;
    }

    .order-row {
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.2s;
    }

    .order-row:hover {
        border-color: var(--border-highlight);
        transform: translateY(-2px);
    }

    .order-row.selected {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.12);
        box-shadow: 0 0 16px var(--primary-glow);
    }

    /* Checkout Details */
    .checkout-panel {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 1.5rem;
        position: sticky;
        top: 85px;
        height: fit-content;
    }

    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin: 1.2rem 0;
    }

    .method-btn {
        padding: 0.85rem 0.5rem;
        border-radius: 12px;
        background: var(--surface-2);
        border: 2px solid var(--border-subtle);
        color: var(--text-main);
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }

    .method-btn.active {
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.2);
        color: #FFFFFF;
    }

    .dian-invoice-box {
        margin-top: 1.25rem;
        padding: 1.2rem;
        background: rgba(16, 185, 129, 0.08);
        border: 1px dashed var(--success);
        border-radius: 14px;
    }

    .cufe-box {
        font-family: monospace;
        font-size: 0.72rem;
        word-break: break-all;
        background: var(--surface-3);
        padding: 0.5rem;
        border-radius: 6px;
        margin-top: 0.5rem;
        color: #E2E8F0;
    }

    /* Modal styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal-card {
        background: var(--surface-1);
        border: 1px solid var(--border-highlight);
        border-radius: 20px;
        padding: 2rem;
        width: 100%;
        max-width: 440px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
</style>
@endsection

@section('content')
<!-- Shift Header -->
<div class="shift-banner">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
            <span style="font-size: 1.15rem; font-weight: 800;">Turno de Caja POS</span>
            <span id="shift-badge" class="shift-status-pill closed">Cerrada</span>
        </div>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Base actual: <span id="shift-base" class="tabular-nums" style="color: #F59E0B; font-weight: 700;">$ 0</span>
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button id="open-shift-btn" class="method-btn" style="background: var(--primary); color: #fff; padding: 0.6rem 1.2rem;" onclick="openShiftModal()">
            Abrir Turno
        </button>
        <button id="close-shift-btn" class="method-btn" style="background: var(--surface-3); padding: 0.6rem 1.2rem; display: none;" onclick="closeShiftModal()">
            Arqueo Z / Cerrar
        </button>
    </div>
</div>

<div class="pos-grid">
    <!-- Orders to Pay -->
    <div class="orders-section">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem;">Comandas Pendientes de Pago</h3>
        <div id="orders-list">
            <div style="color: var(--text-muted); font-size: 0.88rem; padding: 2rem 0; text-align: center;">
                Cargando comandas...
            </div>
        </div>
    </div>

    <!-- Settlement Panel -->
    <div class="checkout-panel">
        <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem;">Detalle de Liquidación</h3>
        <div id="selected-order-info" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
            Selecciona una comanda a la izquierda para cobrar o emitir factura DIAN.
        </div>

        <div id="checkout-content" style="display: none;">
            <div style="background: var(--surface-2); padding: 1rem; border-radius: 14px; margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; font-size: 0.88rem;">
                    <span>Subtotal:</span>
                    <span id="co-subtotal" class="tabular-nums">$ 0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; font-size: 0.88rem;">
                    <span>Impoconsumo (8%):</span>
                    <span id="co-tax" class="tabular-nums">$ 0</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; color: #F59E0B; margin-top: 0.5rem; border-top: 1px solid var(--border-subtle); padding-top: 0.5rem;">
                    <span>Total a Cobrar:</span>
                    <span id="co-total" class="tabular-nums">$ 0</span>
                </div>
            </div>

            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted);">Método de Pago:</label>
            <div class="payment-methods-grid">
                <button class="method-btn active" onclick="setPaymentMethod('cash', this)">💵 Efectivo</button>
                <button class="method-btn" onclick="setPaymentMethod('card', this)">💳 Tarjeta</button>
                <button class="method-btn" onclick="setPaymentMethod('transfer', this)">📱 Nequi/Trf</button>
            </div>

            <button id="pay-order-btn" class="send-order-btn" style="background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); margin-bottom: 0.75rem;" onclick="payOrder()">
                <span>💰</span> Registrar Pago
            </button>

            <!-- Factus DIAN Box -->
            <div id="dian-box" class="dian-invoice-box" style="display: none;">
                <div style="font-weight: 800; font-size: 0.92rem; color: var(--success); display: flex; align-items: center; gap: 0.4rem;">
                    <span>🏛️</span> Factura Electrónica DIAN
                </div>
                <div id="dian-bill-num" style="font-size: 0.85rem; font-weight: 700; margin-top: 0.35rem; color: #fff;"></div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">CUFE (Firma Electrónica):</div>
                <div id="dian-cufe" class="cufe-box"></div>
                <a id="dian-qr-link" href="#" target="_blank" style="display: inline-block; margin-top: 0.75rem; font-size: 0.8rem; font-weight: 700; color: var(--primary);">
                    🔍 Consultar en Catálogo DIAN →
                </a>
            </div>

            <button id="dian-emit-btn" class="send-order-btn" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);" onclick="emitDianInvoice()">
                <span>📄</span> Emitir Factura DIAN con Factus
            </button>
        </div>
    </div>
</div>

<!-- Modal Apertura de Caja -->
<div id="open-modal" class="modal-overlay">
    <div class="modal-card">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 1rem;">Apertura de Turno POS</h3>
        <label style="font-size: 0.85rem; color: var(--text-muted);">Base Inicial en Efectivo (COP):</label>
        <input type="number" id="open-base-input" value="150000" style="width: 100%; padding: 0.85rem; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--border-subtle); color: #fff; font-size: 1.1rem; font-weight: 700; margin: 0.5rem 0 1.25rem;">
        <div style="display: flex; gap: 0.5rem;">
            <button class="method-btn" style="flex: 1;" onclick="closeModal('open-modal')">Cancelar</button>
            <button class="method-btn" style="flex: 1; background: var(--primary); color: #fff;" onclick="submitOpenShift()">Aperturar</button>
        </div>
    </div>
</div>

<!-- Modal Arqueo Z -->
<div id="close-modal" class="modal-overlay">
    <div class="modal-card">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Cierre de Turno y Arqueo Z</h3>
        <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem;">Ingresa el efectivo total contado físicamente en la caja para la conciliación contable.</p>
        <label style="font-size: 0.85rem; color: var(--text-muted);">Efectivo Físico Contado (COP):</label>
        <input type="number" id="close-cash-input" style="width: 100%; padding: 0.85rem; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--border-subtle); color: #fff; font-size: 1.1rem; font-weight: 700; margin: 0.5rem 0 1.25rem;">
        <div style="display: flex; gap: 0.5rem;">
            <button class="method-btn" style="flex: 1;" onclick="closeModal('close-modal')">Cancelar</button>
            <button class="method-btn" style="flex: 1; background: var(--danger); color: #fff;" onclick="submitCloseShift()">Cerrar Caja Z</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentShift = null;
    let selectedOrder = null;
    let currentMethod = 'cash';

    async function init() {
        await loadShiftStatus();
        await loadOrders();
    }

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
                badge.innerText = 'Abierta';
                base.innerText = formatCOP(currentShift.opening_amount);
                openBtn.style.display = 'none';
                closeBtn.style.display = 'block';
            } else {
                badge.className = 'shift-status-pill closed';
                badge.innerText = 'Cerrada';
                base.innerText = '$ 0';
                openBtn.style.display = 'block';
                closeBtn.style.display = 'none';
            }
        } catch (e) {
            console.error('Error turno', e);
        }
    }

    async function loadOrders() {
        try {
            const res = await fetch('/api/orders');
            const json = await res.json();
            const container = document.getElementById('orders-list');
            
            const unpaidOrders = (json.data || []).filter(o => o.status !== 'paid' && o.status !== 'cancelled');

            if (unpaidOrders.length === 0) {
                container.innerHTML = `
                    <div style="color: var(--text-muted); font-size: 0.88rem; padding: 2rem 0; text-align: center;">
                        No hay comandas pendientes de pago en este momento.
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
                    statusBadge = '<span style="background: rgba(16, 185, 129, 0.2); color: #34D399; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 0.72rem;">🛎️ Listo para Servir</span>';
                } else if (ord.status === 'served') {
                    statusBadge = '<span style="background: rgba(59, 130, 246, 0.2); color: #93C5FD; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 0.72rem;">🍽️ Consumiendo en Mesa</span>';
                } else {
                    statusBadge = '<span style="background: rgba(245, 158, 11, 0.2); color: #FBBF24; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 0.72rem;">👨‍🍳 En Cocina</span>';
                }

                if (isDelayed) {
                    statusBadge += ` <span style="background: rgba(239, 68, 68, 0.25); color: #FCA5A5; border: 1px solid rgba(239, 68, 68, 0.5); padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 0.72rem;">⚠️ +${minsElapsed}m Retraso</span>`;
                }

                row.innerHTML = `
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>#${ord.order_number}</span>
                            <span>${statusBadge}</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">
                            ${ord.table ? 'Mesa ' + ord.table.table_number : 'Para Llevar / Domicilio'} · ${minsElapsed} min transcurridos
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 800; font-size: 1.1rem; color: #FEF08A;" class="tabular-nums">${formatCOP(ord.total)}</div>
                        <div style="font-size: 0.72rem; color: var(--text-dim);">${(ord.items || []).length} ítems</div>
                    </div>
                `;
                container.appendChild(row);
            });
        } catch (e) {
            console.error('Error comandas', e);
        }
    }

    function selectOrder(order) {
        selectedOrder = order;
        document.querySelectorAll('.order-row').forEach(r => r.classList.remove('selected'));
        event.currentTarget.classList.add('selected');

        document.getElementById('checkout-content').style.display = 'block';
        document.getElementById('selected-order-info').innerText = `Comanda #${order.order_number} - Mesa ${order.table ? order.table.table_number : 'Llevar'}`;
        document.getElementById('co-subtotal').innerText = formatCOP(order.subtotal);
        document.getElementById('co-tax').innerText = formatCOP(order.tax);
        document.getElementById('co-total').innerText = formatCOP(order.total);

        // Dian check
        const dianBox = document.getElementById('dian-box');
        if (order.electronic_invoice && order.electronic_invoice.status === 'sent_valid') {
            dianBox.style.display = 'block';
            document.getElementById('dian-bill-num').innerText = `Factura DIAN: ${order.electronic_invoice.bill_number}`;
            document.getElementById('dian-cufe').innerText = order.electronic_invoice.cufe;
            document.getElementById('dian-qr-link').href = order.electronic_invoice.qr_url;
            document.getElementById('dian-emit-btn').style.display = 'none';
        } else {
            dianBox.style.display = 'none';
            document.getElementById('dian-emit-btn').style.display = 'block';
        }
    }

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
                body: JSON.stringify({ status: 'paid' })
            });

            if (res.ok) {
                showToast(`¡Comanda #${selectedOrder.order_number} cobrada con éxito!`, 'success');
                selectedOrder = null;
                document.getElementById('checkout-content').style.display = 'none';
                await loadOrders();
            } else {
                showToast('Error al procesar cobro', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    async function emitDianInvoice() {
        if (!selectedOrder) return;

        const btn = document.getElementById('dian-emit-btn');
        btn.disabled = true;
        btn.innerText = 'Emitiendo ante la DIAN vía Factus...';

        try {
            const res = await fetch(`/api/orders/${selectedOrder.id}/invoice`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json();

            if (res.ok && json.data.electronic_invoice.status === 'sent_valid') {
                showToast('¡Factura Electrónica aprobada por la DIAN!', 'success');
                selectOrder(json.data);
            } else {
                showToast(json.data?.electronic_invoice?.error_message || 'Error emitiendo factura DIAN', 'error');
            }
        } catch (e) {
            showToast('Error comunicando con Factus', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>📄</span> Emitir Factura DIAN con Factus';
        }
    }

    // Modales
    function openShiftModal() { document.getElementById('open-modal').style.display = 'flex'; }
    function closeShiftModal() { 
        document.getElementById('close-modal').style.display = 'flex';
        document.getElementById('close-cash-input').value = currentShift?.expected_cash || 0;
    }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    async function submitOpenShift() {
        const amount = document.getElementById('open-base-input').value;
        try {
            const res = await fetch('/api/cash-shifts/open', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ opening_amount: parseFloat(amount) })
            });

            if (res.ok) {
                showToast('Turno de caja aperturado con base inicial', 'success');
                closeModal('open-modal');
                await loadShiftStatus();
            } else {
                const err = await res.json();
                showToast(err.message || 'Error abriendo caja', 'error');
            }
        } catch (e) {
            showToast('Error en petición', 'error');
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
                showToast(`¡Caja Z cerrada! ${diffMsg}`, 'success');
                closeModal('close-modal');
                await loadShiftStatus();
            } else {
                showToast('Error cerrando caja', 'error');
            }
        } catch (e) {
            showToast('Error en petición', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', init);
</script>
@endsection
