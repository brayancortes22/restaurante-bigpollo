@extends('layouts.app')

@section('title', 'Cocina KDS')

@section('styles')
<style>
    .kds-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 1rem 1.5rem;
    }

    .kds-stat-chip {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .kds-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }

    .kds-card {
        background: var(--surface-1);
        border: 2px solid var(--border-subtle);
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }

    .kds-card.pending {
        border-top: 6px solid var(--warning);
    }

    .kds-card.in_kitchen {
        border-top: 6px solid var(--primary);
    }

    .kds-card.ready {
        border-top: 6px solid var(--success);
    }

    .card-top {
        padding: 1.2rem;
        border-bottom: 1px solid var(--border-subtle);
        background: var(--surface-2);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-order-id {
        font-size: 1.15rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-table-badge {
        background: var(--surface-3);
        padding: 0.3rem 0.75rem;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #FFFFFF;
    }

    .timer-badge {
        font-size: 0.85rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .timer-badge.normal {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid var(--success-glow);
    }

    .timer-badge.warning {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid var(--warning-glow);
    }

    .timer-badge.critical {
        background: rgba(244, 63, 94, 0.2);
        color: var(--danger);
        border: 1px solid var(--danger-glow);
        animation: pulse-danger 1s infinite;
    }

    @keyframes pulse-danger {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.75; transform: scale(1.03); }
    }

    .items-list {
        padding: 1.2rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        flex: 1;
    }

    .item-row {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        border-bottom: 1px dashed var(--border-subtle);
        padding-bottom: 0.65rem;
    }

    .item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .item-qty-badge {
        background: #F59E0B;
        color: #000000;
        font-size: 0.85rem;
        font-weight: 800;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .item-title {
        font-size: 0.98rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .item-notes {
        font-size: 0.75rem;
        color: #F87171;
        font-weight: 600;
        margin-top: 2px;
    }

    .card-actions {
        padding: 1rem 1.2rem;
        background: var(--surface-2);
        border-top: 1px solid var(--border-subtle);
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        flex: 1;
        padding: 0.85rem;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-cook {
        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 12px var(--primary-glow);
    }

    .btn-cook:hover {
        transform: translateY(-2px);
    }

    .btn-ready {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 12px var(--success-glow);
    }

    .btn-ready:hover {
        transform: translateY(-2px);
    }

    .btn-served {
        background: var(--surface-3);
        color: #FFFFFF;
    }

    .btn-served:hover {
        background: #475569;
    }
</style>
@endsection

@section('content')
<div class="kds-header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <h2 style="font-size: 1.35rem; font-weight: 800;">👨‍🍳 Cocina KDS en Tiempo Real</h2>
        <div class="kds-stat-chip">
            <span class="pulse-dot"></span>
            <span id="active-tickets-count" style="color: var(--warning);">0 comandas activas</span>
        </div>
    </div>
    <div style="font-size: 0.85rem; color: var(--text-muted);">
        Actualización automática cada 4 segundos
    </div>
</div>

<div class="kds-grid" id="kds-container">
    <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 4rem 0;">
        Cargando comandas de cocina...
    </div>
</div>
@endsection

@section('scripts')
<script>
    let orders = [];

    async function loadKdsOrders() {
        try {
            const res = await fetch('/api/orders');
            const json = await res.json();
            // Filtrar solo las comandas pendientes, en preparación o listas
            orders = (json.data || []).filter(o => ['pending', 'in_kitchen', 'ready'].includes(o.status));
            renderKDS();
        } catch (e) {
            console.error('Error cargando KDS', e);
        }
    }

    function renderKDS() {
        const container = document.getElementById('kds-container');
        document.getElementById('active-tickets-count').innerText = `${orders.length} comandas activas`;

        if (orders.length === 0) {
            container.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 4rem 0;">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎉</div>
                    <div style="font-size: 1.15rem; font-weight: 700; color: #FFFFFF;">¡Todas las comandas están al día!</div>
                    <div style="font-size: 0.85rem; color: var(--text-dim); margin-top: 4px;">Esperando nuevos pedidos de meseros...</div>
                </div>
            `;
            return;
        }

        container.innerHTML = '';

        orders.forEach(order => {
            const card = document.createElement('div');
            card.className = `kds-card ${order.status}`;

            // Calcular tiempo transcurrido
            const created = new Date(order.created_at || Date.now());
            const minutesElapsed = Math.floor((Date.now() - created.getTime()) / 60000);

            let timerClass = 'normal';
            if (minutesElapsed >= 10 && minutesElapsed < 20) timerClass = 'warning';
            if (minutesElapsed >= 20) timerClass = 'critical';

            let actionButtons = '';
            if (order.status === 'pending') {
                actionButtons = `
                    <button class="action-btn btn-cook" onclick="updateOrderStatus(${order.id}, 'in_kitchen')">
                        <span>🔥</span> Preparar
                    </button>
                `;
            } else if (order.status === 'in_kitchen') {
                actionButtons = `
                    <button class="action-btn btn-ready" onclick="updateOrderStatus(${order.id}, 'ready')">
                        <span>✅</span> ¡Plato Listo!
                    </button>
                `;
            } else if (order.status === 'ready') {
                actionButtons = `
                    <button class="action-btn btn-served" onclick="updateOrderStatus(${order.id}, 'served')">
                        <span>🛎️</span> Entregado
                    </button>
                `;
            }

            const itemsHtml = (order.items || []).map(item => `
                <div class="item-row">
                    <div class="item-qty-badge">${item.quantity}</div>
                    <div>
                        <div class="item-title">${item.product_name || 'Producto'}</div>
                        ${item.notes ? `<div class="item-notes">Nota: ${item.notes}</div>` : ''}
                    </div>
                </div>
            `).join('');

            card.innerHTML = `
                <div>
                    <div class="card-top">
                        <div>
                            <div class="card-order-id">
                                <span>#${order.order_number}</span>
                                <span class="card-table-badge">${order.table ? order.table.table_number : 'Llevar'}</span>
                            </div>
                        </div>
                        <div class="timer-badge ${timerClass}">
                            <span>⏱️</span> <span>${minutesElapsed}m</span>
                        </div>
                    </div>

                    <div class="items-list">
                        ${itemsHtml}
                    </div>
                </div>

                <div class="card-actions">
                    ${actionButtons}
                </div>
            `;

            container.appendChild(card);
        });
    }

    async function updateOrderStatus(orderId, newStatus) {
        try {
            const res = await fetch(`/api/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });

            if (res.ok) {
                showToast(`Comanda actualizada a estado: ${newStatus}`, 'success');
                await loadKdsOrders();
            } else {
                showToast('Error actualizando estado en cocina', 'error');
            }
        } catch (e) {
            showToast('Error de comunicación', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadKdsOrders();
        // Polling cada 4 segundos
        setInterval(loadKdsOrders, 4000);
    });
</script>
@endsection
