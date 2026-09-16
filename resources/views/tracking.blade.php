@extends('layouts.app')

@section('title', 'Rastreo de Pedido en Vivo')

@section('styles')
<style>
    .track-wrapper {
        max-width: 600px;
        margin: 1.5rem auto 3rem auto;
        padding: 0 1rem;
    }

    .track-card {
        background: var(--surface-1);
        border: 2px solid var(--border-highlight);
        border-radius: 24px;
        padding: 2rem 1.75rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        position: relative;
        overflow: hidden;
    }

    .track-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #E52521, #FFD200, #E52521);
    }

    .track-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .track-order-badge {
        display: inline-block;
        background: rgba(229, 37, 33, 0.2);
        color: #FEF08A;
        border: 1px solid rgba(255, 210, 0, 0.4);
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.88rem;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
    }

    .track-status-highlight {
        font-size: 1.55rem;
        font-weight: 900;
        color: #FFFFFF;
        margin-bottom: 0.35rem;
    }

    .track-subtext {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    /* Línea de Tiempo Animada */
    .timeline-wrap {
        margin: 2rem 0;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .timeline-wrap::before {
        content: '';
        position: absolute;
        top: 15px;
        bottom: 15px;
        left: 20px;
        width: 3px;
        background: var(--surface-3);
    }

    .timeline-step {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        position: relative;
        z-index: 2;
    }

    .step-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--surface-2);
        border: 2px solid var(--border-subtle);
        color: var(--text-dim);
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s;
    }

    .timeline-step.active .step-circle {
        background: linear-gradient(135deg, #E52521 0%, #B91C1C 100%);
        border-color: #FFD200;
        color: #FFFFFF;
        box-shadow: 0 0 16px rgba(229, 37, 33, 0.6);
        animation: pulse-step 2s infinite ease-in-out;
    }

    .timeline-step.completed .step-circle {
        background: #10B981;
        border-color: #34D399;
        color: #FFFFFF;
    }

    @keyframes pulse-step {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .step-content h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #FFFFFF;
        margin-bottom: 0.2rem;
    }

    .step-content p {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    /* Detalle de Comanda */
    .order-summary-box {
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 1.25rem;
        margin-top: 1.5rem;
    }

    .summary-title {
        font-size: 0.88rem;
        font-weight: 800;
        color: #FFD200;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        padding: 0.4rem 0;
        color: var(--text-main);
        border-bottom: 1px dashed var(--border-subtle);
    }

    .item-row:last-child {
        border-bottom: none;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 2px solid rgba(255, 210, 0, 0.3);
        font-size: 1.1rem;
        font-weight: 900;
        color: #FEF08A;
    }

    .delivery-info-badge {
        background: rgba(255, 255, 255, 0.04);
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="track-wrapper">
    <div class="track-card">
        <div class="track-header">
            <div class="track-order-badge" id="orderBadge">
                🍗 Pedido #{{ $order_number }}
            </div>
            <div class="track-status-highlight" id="statusHighlight">
                Cargando estado...
            </div>
            <div class="track-subtext" id="statusSubtext">
                Consultando el asador de Big Pollo en tiempo real...
            </div>
        </div>

        <!-- Línea de Tiempo -->
        <div class="timeline-wrap" id="timelineContainer">
            <!-- Paso 1 -->
            <div class="timeline-step" id="step-pending">
                <div class="step-circle">📝</div>
                <div class="step-content">
                    <h3>1. Pedido Recibido</h3>
                    <p>La orden fue recibida y confirmada en el restaurante.</p>
                </div>
            </div>

            <!-- Paso 2 -->
            <div class="timeline-step" id="step-in_kitchen">
                <div class="step-circle">👨‍🍳</div>
                <div class="step-content">
                    <h3>2. En Cocina</h3>
                    <p>Dorando al carbón y preparando el pollo broaster crujiente.</p>
                </div>
            </div>

            <!-- Paso 3 -->
            <div class="timeline-step" id="step-ready">
                <div class="step-circle">📦</div>
                <div class="step-content">
                    <h3>3. Empacado & Listo</h3>
                    <p>Pedido empacado caliente con sus papas, arepas y salsas.</p>
                </div>
            </div>

            <!-- Paso 4 -->
            <div class="timeline-step" id="step-served">
                <div class="step-circle">🛵</div>
                <div class="step-content">
                    <h3>4. En Camino</h3>
                    <p>El repartidor de Big Pollo va en ruta hacia tu dirección.</p>
                </div>
            </div>

            <!-- Paso 5 -->
            <div class="timeline-step" id="step-paid">
                <div class="step-circle">🍗</div>
                <div class="step-content">
                    <h3>5. Entregado</h3>
                    <p>¡Buen provecho! Disfruta tu pollo asado o broaster.</p>
                </div>
            </div>
        </div>

        <!-- Resumen del Pedido -->
        <div class="order-summary-box">
            <div class="summary-title">
                <span>🛒</span> Detalle de tu Pedido
            </div>
            <div id="itemsContainer">
                <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 1rem;">
                    ⏳ Cargando platos...
                </div>
            </div>

            <div class="total-row">
                <span>Total a Pagar:</span>
                <span id="orderTotal">$ 0 COP</span>
            </div>

            <div class="delivery-info-badge" id="deliveryInfoBadge">
                <span>📍 Dirección de Entrega:</span>
                <strong id="deliveryAddressText" style="color: #FFFFFF;">Cargando...</strong>
            </div>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.78rem; color: var(--text-dim);">
            🔄 Esta pantalla se actualiza automáticamente cada 5 segundos.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const orderNumber = "{{ $order_number }}";

    document.addEventListener('DOMContentLoaded', () => {
        fetchTrackingData();
        setInterval(fetchTrackingData, 5000); // Polling cada 5 seg
    });

    async function fetchTrackingData() {
        try {
            const res = await fetch(`/api/tracking/${orderNumber}`);
            if (!res.ok) {
                document.getElementById('statusHighlight').innerText = 'Pedido no encontrado';
                document.getElementById('statusSubtext').innerText = 'Verifique el número de comanda ingresado.';
                return;
            }

            const data = await res.json();
            updateUI(data);
        } catch (error) {
            console.error('Error al rastrear pedido:', error);
        }
    }

    function updateUI(data) {
        document.getElementById('statusHighlight').innerText = data.status_label;
        document.getElementById('statusSubtext').innerText = `Tiempo transcurrido: ${data.minutes_elapsed} min | Registrado: ${data.created_at}`;

        // Actualizar línea de tiempo
        const stepsOrder = ['pending', 'in_kitchen', 'ready', 'served', 'paid'];
        const currentIdx = stepsOrder.indexOf(data.status);

        stepsOrder.forEach((step, idx) => {
            const el = document.getElementById(`step-${step}`);
            if (!el) return;

            el.classList.remove('active', 'completed');

            if (idx < currentIdx) {
                el.classList.add('completed');
            } else if (idx === currentIdx) {
                el.classList.add('active');
            }
        });

        // Items
        const itemsContainer = document.getElementById('itemsContainer');
        itemsContainer.innerHTML = data.items.map(item => `
            <div class="item-row">
                <span><b>${item.quantity}x</b> ${item.name}</span>
                <span>$ ${Number(item.subtotal).toLocaleString('es-CO')}</span>
            </div>
        `).join('');

        // Total y Dirección
        document.getElementById('orderTotal').innerText = `$ ${Number(data.total).toLocaleString('es-CO')} COP`;
        document.getElementById('deliveryAddressText').innerText = data.delivery_address || 'Entrega en Salón / Mostrador';
    }
</script>
@endsection
