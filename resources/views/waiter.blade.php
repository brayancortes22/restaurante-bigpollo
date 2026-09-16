@extends('layouts.app')

@section('title', 'Comandero Mesero')

@section('styles')
    <link rel="stylesheet" href="/css/waiter.css?v={{ time() }}">
@endsection

@section('content')
<!-- Banner de Modo Adición -->
<div id="additionBanner" class="addition-mode-banner">
    <div>
        <span>🟢 MODO ADICIÓN ACTIVO:</span>
        <span id="additionTableLabel">Mesa X</span>
    </div>
    <button type="button" class="btn-cancel-addition" onclick="cancelAdditionMode()">
        ✕ Cancelar Adición
    </button>
</div>

<div class="waiter-layout">
    <!-- Columna Principal: Mesas, Buscador Táctil y Catálogo -->
    <div>
        <!-- Salón de Mesas -->
        <div class="tables-section">
            <div class="tables-header">
                <h2 class="tables-title">
                    <span>🍽️</span> Salón de Mesas Big Pollo
                </h2>
                <span id="selectedTableBadge" class="selected-table-badge">
                    Ninguna seleccionada
                </span>
            </div>
            <div class="table-cards-grid" id="tablesContainer">
                <div class="cart-empty-state">
                    ⏳ Cargando mesas del restaurante...
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda Rápida Táctil (Optimización Hora Pico) -->
        <div class="waiter-search-box">
            <span class="search-icon-symbol">🔍</span>
            <input 
                type="text" 
                id="waiterSearchInput" 
                class="waiter-search-input" 
                placeholder="Buscar plato, bebida, combo o acompañamiento..." 
                autocomplete="off"
            >
            <button type="button" id="searchClearBtn" class="search-clear-btn" title="Limpiar búsqueda">✕</button>
        </div>

        <!-- Categorías del Menú -->
        <div class="category-tabs" id="categoriesContainer"></div>

        <!-- Catálogo de Platos con Botones Táctiles Anti-Error -->
        <div class="products-grid" id="productsContainer"></div>
    </div>

    <!-- Columna Lateral: Panel de Carrito Desktop -->
    <div class="cart-panel-desktop">
        <div class="cart-panel" id="desktopCartPanel">
            <div class="cart-header">
                <h3 class="cart-title">
                    <span>🛒</span> Comanda Actual
                </h3>
                <span id="cartCountBadge" class="cart-count-badge">0 ítems</span>
            </div>

            <div id="cartItemsContainer" class="cart-items-scroll">
                <div class="cart-empty-state">
                    Selecciona una mesa y agrega platos a la orden
                </div>
            </div>

            <div class="cart-summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="summarySubtotal">$ 0</span>
                </div>
                <div class="summary-row">
                    <span>Impoconsumo (8%):</span>
                    <span id="summaryTax">$ 0</span>
                </div>
                <div class="summary-total-row">
                    <span>Total:</span>
                    <span id="summaryTotal">$ 0 COP</span>
                </div>
            </div>

            <label class="consent-label">
                <input type="checkbox" id="customerConsentCheck" checked class="consent-checkbox">
                <span>Autorización Habeas Data conforme a Ley 1581 para factura electrónica DIAN.</span>
            </label>

            <button type="button" id="btnSendOrder" class="btn-send-order" disabled onclick="submitOrder()">
                <span>🚀</span> Enviar a Cocina (KDS)
            </button>
        </div>
    </div>
</div>

<!-- Mobile Floating Cart Bar -->
<div id="mobileFloatingCart" class="mobile-floating-cart-bar" onclick="openMobileCartModal()">
    <div class="floating-cart-info">
        <span style="font-size: 1.4rem;">🛍️</span>
        <div>
            <div style="font-size: 0.9rem; font-weight: 800;" id="mCartCount">0 platos</div>
            <div style="font-size: 0.78rem; color: #FEF08A; font-weight: 700;" id="mCartTotal">$ 0 COP</div>
        </div>
    </div>
    <div class="floating-cart-btn-view">
        Ver Comanda ➔
    </div>
</div>

<!-- Modal Móvil de Carrito (Bottom Sheet) -->
<div class="modal-backdrop-custom" id="mobileCartModal">
    <div class="modal-box-custom">
        <div class="modal-header-row">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #FFFFFF;">🛒 Comanda para Salón</h3>
            <button type="button" class="modal-close-btn" onclick="closeMobileCartModal()">&times;</button>
        </div>
        <div id="mCartItemsContainer" class="cart-items-scroll"></div>
        <div class="cart-summary-box">
            <div class="summary-total-row">
                <span>Total:</span>
                <span id="mSummaryTotal">$ 0 COP</span>
            </div>
        </div>
        <button type="button" class="btn-send-order" onclick="submitOrder()">
            <span>🚀</span> Enviar a Cocina (KDS)
        </button>
    </div>
</div>

<!-- Modal de Detalle de Mesa Ocupada -->
<div class="modal-backdrop-custom" id="occupiedTableModal">
    <div class="modal-box-custom">
        <div class="modal-header-row">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #FFFFFF;" id="occModalTitle">
                    🍗 Mesa Ocupada
                </h3>
                <div style="font-size: 0.8rem; color: #F59E0B; margin-top: 3px;" id="occModalTime">
                    ⏱️ Tiempo de atención: Calculando...
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeOccupiedTableModal()">&times;</button>
        </div>

        <div style="font-size: 0.85rem; font-weight: 800; color: #FFD200; margin-bottom: 0.5rem;">
            📦 Platos en Comanda Activa:
        </div>
        <div id="occItemsList" class="occ-items-scroll"></div>

        <div class="summary-total-row" style="margin-bottom: 1.25rem;">
            <span>Total Acumulado:</span>
            <span id="occModalTotal">$ 0 COP</span>
        </div>

        <!-- Botones de Operaciones de Mesa -->
        <div class="table-ops-grid">
            <button type="button" class="btn-op-addition" onclick="startAdditionMode()">
                ➕ Adicionar Platos
            </button>
            <button type="button" class="btn-op-transfer" onclick="showTransferControls()">
                🔄 Cambiar Mesa
            </button>
            <button type="button" class="btn-op-merge" onclick="showMergeControls()">
                🔗 Unir Mesas
            </button>
            <button type="button" class="btn-op-release" onclick="releaseCurrentTable()">
                ❌ Liberar Mesa
            </button>
        </div>

        <!-- Contenedor Desplegable: Cambiar Mesa -->
        <div id="transferContainer" class="op-control-box">
            <label class="op-control-label">Seleccione mesa libre de destino:</label>
            <div class="op-select-row">
                <select id="transferTargetSelect" class="op-select-input"></select>
                <button type="button" class="btn-op-confirm" onclick="executeTransfer()">Mover</button>
            </div>
        </div>

        <!-- Contenedor Desplegable: Unir Mesas -->
        <div id="mergeContainer" class="op-control-box">
            <label class="op-control-label">Seleccione mesa a unir:</label>
            <div class="op-select-row">
                <select id="mergeTargetSelect" class="op-select-input"></select>
                <button type="button" class="btn-op-confirm" onclick="executeMerge()">Unir</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/js/waiter.js?v={{ time() }}"></script>
@endsection
