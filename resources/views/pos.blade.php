@extends('layouts.app')

@section('title', 'Caja POS & Facturación DIAN')

@section('styles')
    <link rel="stylesheet" href="/css/pos.css">
@endsection

@section('content')
<!-- Banner de Turno de Caja -->
<div class="shift-banner">
    <div>
        <div class="shift-info-box">
            <span class="shift-title">Turno de Caja POS</span>
            <span id="shift-badge" class="shift-status-pill closed">Cerrada</span>
        </div>
        <div class="shift-base-text">
            Base actual en caja: <span id="shift-base" class="shift-base-amount tabular-nums">$ 0</span>
        </div>
    </div>
    <div class="shift-actions">
        <button type="button" id="open-shift-btn" class="btn-shift-action btn-open-shift" onclick="openShiftModal()">
            🔓 Abrir Turno
        </button>
        <button type="button" id="close-shift-btn" class="btn-shift-action btn-close-shift" style="display: none;" onclick="closeShiftModal()">
            🔒 Arqueo Z / Cerrar
        </button>
    </div>
</div>

<div class="pos-grid">
    <!-- Columna Izquierda: Comandas Pendientes de Pago -->
    <div class="orders-section">
        <div class="orders-section-header">
            <h3 class="orders-section-title">
                <span>🧾</span> Comandas Pendientes de Pago
            </h3>
        </div>
        <div id="orders-list">
            <div class="cart-empty-state">
                ⏳ Cargando comandas activas...
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Panel de Liquidación & Auditoría de Consumo -->
    <div class="checkout-panel">
        <h3 class="checkout-title">Detalle de Liquidación</h3>
        <div id="selected-order-info" class="checkout-subtitle">
            Selecciona una comanda a la izquierda para verificar consumos y cobrar.
        </div>

        <div id="checkout-content" style="display: none;">
            <!-- 1. Comprobación y Desglose de Consumo en Mesa (Auditoría Anti-Fuga) -->
            <div class="consumption-box">
                <div class="consumption-header">
                    <span>📦 Platos y Bebidas Consumidos:</span>
                    <span style="font-size: 0.75rem; color: #10B981;">✓ Verificado</span>
                </div>
                <div id="consumption-items-list" class="consumption-items-list">
                    <!-- Inyectado dinámicamente vía pos.js -->
                </div>

                <!-- Botón de Auditoría para Agregar Ítems Omitidos por el Mesero -->
                <button type="button" class="btn-audit-addition" onclick="openAuditModal()">
                    <span>➕</span> ¿El cliente consumió algo no anotado? (Adicionar)
                </button>
            </div>

            <!-- 2. Totales Financieros -->
            <div class="pos-totals-box">
                <div class="pos-totals-row">
                    <span>Subtotal:</span>
                    <span id="co-subtotal" class="tabular-nums">$ 0</span>
                </div>
                <div class="pos-totals-row">
                    <span>Impoconsumo (8%):</span>
                    <span id="co-tax" class="tabular-nums">$ 0</span>
                </div>
                <div class="pos-grand-total">
                    <span>Total a Cobrar:</span>
                    <span id="co-total" class="tabular-nums">$ 0</span>
                </div>
            </div>

            <!-- 3. Selección de Medio de Pago -->
            <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.4rem;">
                Método de Pago:
            </label>
            <div class="payment-methods-grid">
                <button type="button" class="method-btn active" onclick="setPaymentMethod('cash', this)">💵 Efectivo</button>
                <button type="button" class="method-btn" onclick="setPaymentMethod('card', this)">💳 Tarjeta</button>
                <button type="button" class="method-btn" onclick="setPaymentMethod('transfer', this)">📱 Nequi/Trf</button>
            </div>

            <button type="button" id="pay-order-btn" class="btn-pay-order" onclick="payOrder()">
                <span>💰</span> Registrar Pago
            </button>

            <!-- Caja de Factura Electrónica DIAN -->
            <div id="dian-box" class="dian-invoice-box" style="display: none;">
                <div class="dian-title-row">
                    <span>🏛️</span> Factura Electrónica DIAN Timbrada
                </div>
                <div id="dian-bill-num" style="font-size: 0.85rem; font-weight: 800; margin-top: 0.35rem; color: #FFFFFF;"></div>
                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 0.4rem;">CUFE Oficial:</div>
                <div id="dian-cufe" class="cufe-box"></div>
                <a id="dian-qr-link" href="#" target="_blank" style="display: inline-block; margin-top: 0.65rem; font-size: 0.8rem; font-weight: 800; color: #FFD200; text-decoration: none;">
                    🔍 Consultar en Catálogo DIAN ➔
                </a>
            </div>

            <button type="button" id="dian-emit-btn" class="btn-emit-dian" onclick="emitDianInvoice()">
                <span>📄</span> Emitir Factura DIAN con Factus
            </button>
        </div>
    </div>
</div>

<!-- Modal de Auditoría de Ítems Omitidos en Caja -->
<div id="audit-modal" class="modal-backdrop-custom">
    <div class="modal-box-custom">
        <div class="modal-header-row">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #FFFFFF;">
                    ➕ Adicionar Artículo a la Cuenta
                </h3>
                <div style="font-size: 0.8rem; color: #FEF08A; margin-top: 3px;" id="audit-modal-target-order">
                    Comanda #...
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeAuditModal()">&times;</button>
        </div>

        <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.85rem;">
            Busca y agrega cualquier plato, porción o bebida que el comensal haya consumido antes de cerrar la factura.
        </p>

        <input 
            type="text" 
            id="auditSearchInput" 
            class="audit-search-input" 
            placeholder="🔍 Buscar plato, cerveza, gaseosa, porción..." 
            autocomplete="off"
        >

        <div id="auditProductsContainer" class="audit-products-scroll">
            <!-- Inyectado vía pos.js -->
        </div>

        <button type="button" class="btn-shift-action btn-close-shift" style="width: 100%;" onclick="closeAuditModal()">
            Cerrar Ventana
        </button>
    </div>
</div>

<!-- Modal Apertura de Turno POS -->
<div id="open-modal" class="modal-backdrop-custom">
    <div class="modal-box-custom" style="max-width: 440px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem; color: #FFFFFF;">🔓 Apertura de Turno POS</h3>
        <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem;">Ingresa la base inicial en efectivo para cambio.</p>
        <input type="number" id="open-base-input" value="150000" class="audit-search-input" style="font-size: 1.2rem; font-weight: 800; color: #FEF08A;">
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn-shift-action btn-close-shift" style="flex: 1;" onclick="closeModal('open-modal')">Cancelar</button>
            <button type="button" class="btn-shift-action btn-open-shift" style="flex: 1;" onclick="submitOpenShift()">Aperturar</button>
        </div>
    </div>
</div>

<!-- Modal Arqueo Z -->
<div id="close-modal" class="modal-backdrop-custom">
    <div class="modal-box-custom" style="max-width: 440px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem; color: #FFFFFF;">🔒 Cierre de Turno y Arqueo Z</h3>
        <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem;">Ingresa el efectivo total contado físicamente en la gaveta.</p>
        <input type="number" id="close-cash-input" class="audit-search-input" style="font-size: 1.2rem; font-weight: 800; color: #FEF08A;">
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn-shift-action btn-close-shift" style="flex: 1;" onclick="closeModal('close-modal')">Cancelar</button>
            <button type="button" class="btn-shift-action btn-open-shift" style="flex: 1; background: #DC2626;" onclick="submitCloseShift()">Cerrar Caja Z</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/js/pos.js"></script>
@endsection
