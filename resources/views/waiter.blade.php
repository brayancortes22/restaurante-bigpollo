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

<!-- Banner de Modo Merge (Drag-to-Merge activo) -->
<div id="mergeDragBanner" class="merge-drag-banner" style="display:none;">
    <span>🔗 Arrastra una mesa sobre otra para unirlas</span>
    <button type="button" class="btn-cancel-addition" onclick="cancelMergeDrag()">✕ Cancelar</button>
</div>

<div class="waiter-layout">
    <!-- Columna Principal: Selector de Vista, Mesas y Catálogo -->
    <div>
        <!-- Selector de Vista: Mapa / Lista -->
        <div class="view-toggle-bar">
            <button type="button" id="btnViewMap" class="view-toggle-btn active" onclick="switchView('map')">
                🗺️ Mapa del Salón
            </button>
            <button type="button" id="btnViewList" class="view-toggle-btn" onclick="switchView('list')">
                📋 Lista Rápida
            </button>
        </div>

        <!-- =============================================
             VISTA MAPA INTERACTIVO DEL SALÓN
             ============================================= -->
        <div id="floorMapView" class="floor-map-section">
            <div class="floor-map-header">
                <h2 class="tables-title"><span>🗺️</span> Plano del Salón — Big Pollo</h2>
                <span id="selectedTableBadge" class="selected-table-badge">Ninguna seleccionada</span>
            </div>

            <!-- Leyenda semáforo -->
            <div class="map-legend-bar">
                <span class="legend-chip legend-free">🟢 Libre</span>
                <span class="legend-chip legend-occupied">🟠 Ocupada</span>
                <span class="legend-chip legend-ready">🔆 Listo</span>
                <span class="legend-chip legend-merged">🔗 Unida</span>
                <span class="legend-chip legend-takeout">🥡 Mostrador</span>
            </div>

            <!-- Lienzo del Mapa (coordenadas pos_x/pos_y en %) -->
            <div id="floorMapCanvas" class="floor-map-canvas">
                <!-- Zonas arquitectónicas del local (decorativas) -->
                <div class="map-zone" style="left:0%;top:0%;width:62%;height:48%;">
                    <span class="map-zone-label">Salón Frente a la Calle</span>
                </div>
                <div class="map-zone map-zone-principal" style="left:0%;top:50%;width:52%;height:48%;">
                    <span class="map-zone-label">Salón Principal</span>
                </div>
                <div class="map-zone map-zone-kitchen" style="left:63%;top:50%;width:37%;height:48%;">
                    <span class="map-zone-label">🍗 Rotisería / Asador</span>
                </div>
                <div class="map-zone map-zone-cash" style="left:63%;top:0%;width:37%;height:48%;">
                    <span class="map-zone-label">💳 Zona Caja</span>
                </div>

                <!-- Botón especial: Mostrador / Para Llevar -->
                <div id="takeoutStation"
                     class="map-takeout-station"
                     style="left:65%;top:52%;"
                     onclick="selectTakeout()"
                     title="Pedido Para Llevar">
                    <div class="takeout-icon">🥡</div>
                    <div class="takeout-label">Para Llevar</div>
                </div>

                <!-- Mesas se renderizan aquí por JS -->
                <div id="mapTablesContainer"></div>

                <!-- Drop zone overlay para drag-to-merge -->
                <div id="mergeDropOverlay" class="merge-drop-overlay" style="display:none;"></div>
            </div>
        </div>

        <!-- =============================================
             VISTA LISTA RÁPIDA (fallback original)
             ============================================= -->
        <div id="listView" class="tables-section" style="display:none;">
            <div class="tables-header">
                <h2 class="tables-title"><span>🍽️</span> Salón de Mesas Big Pollo</h2>
                <span id="selectedTableBadge2" class="selected-table-badge">Ninguna seleccionada</span>
            </div>
            <div class="table-cards-grid" id="tablesContainer">
                <div class="cart-empty-state">⏳ Cargando mesas del restaurante...</div>
            </div>
        </div>

        <!-- Barra de Búsqueda Rápida Táctil -->
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

        <!-- Catálogo de Platos -->
        <div class="products-grid" id="productsContainer"></div>
    </div>

    <!-- Columna Lateral: Panel de Carrito Desktop -->
    <div class="cart-panel-desktop">
        <div class="cart-panel" id="desktopCartPanel">
            <div class="cart-header">
                <h3 class="cart-title"><span>🛒</span> Comanda Actual</h3>
                <span id="cartCountBadge" class="cart-count-badge">0 ítems</span>
            </div>

            <div id="cartItemsContainer" class="cart-items-scroll">
                <div class="cart-empty-state">Selecciona una mesa y agrega platos a la orden</div>
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
    <div class="floating-cart-btn-view">Ver Comanda ➔</div>
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
        <div id="transferContainer" class="op-control-box" style="display:none;">
            <label class="op-control-label">Seleccione mesa libre de destino:</label>
            <div class="op-select-row">
                <select id="transferTargetSelect" class="op-select-input"></select>
                <button type="button" class="btn-op-confirm" onclick="executeTransfer()">Mover</button>
            </div>
        </div>

        <!-- Contenedor Desplegable: Unir Mesas -->
        <div id="mergeContainer" class="op-control-box" style="display:none;">
            <label class="op-control-label">Seleccione mesa a unir:</label>
            <div class="op-select-row">
                <select id="mergeTargetSelect" class="op-select-input"></select>
                <button type="button" class="btn-op-confirm" onclick="executeMerge()">Unir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Unión de Mesas (Drag-to-Merge) -->
<div class="modal-backdrop-custom" id="mergeConfirmModal">
    <div class="modal-box-custom" style="max-width: 420px;">
        <div class="modal-header-row">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #FFFFFF;">🔗 Unir Mesas</h3>
            <button type="button" class="modal-close-btn" onclick="closeMergeConfirmModal()">&times;</button>
        </div>
        <p id="mergeConfirmText" style="color: #E2E8F0; font-size: 0.95rem; margin: 1rem 0 1.5rem; line-height: 1.5;"></p>
        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn-op-merge" style="flex:1;" onclick="confirmDragMerge()">
                ✅ Confirmar Unión
            </button>
            <button type="button" class="btn-op-release" style="flex:1;" onclick="closeMergeConfirmModal()">
                ✕ Cancelar
            </button>
        </div>
    </div>
</div>

<!-- Modal Para Llevar -->
<div class="modal-backdrop-custom" id="takeoutModal">
    <div class="modal-box-custom" style="max-width: 480px;">
        <div class="modal-header-row">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #FFFFFF;">🥡 Pedido Para Llevar</h3>
            <button type="button" class="modal-close-btn" onclick="closeTakeoutModal()">&times;</button>
        </div>
        <p style="color: #94A3B8; font-size: 0.88rem; margin-bottom: 1rem;">
            Registra el nombre del cliente para identificar su pedido en el mostrador.
        </p>
        <div style="margin-bottom: 1rem;">
            <label style="display:block; font-size:0.82rem; color:#FFD200; margin-bottom:4px; font-weight:700;">
                Nombre del Cliente (opcional):
            </label>
            <input type="text" id="takeoutCustomerName"
                   style="width:100%; padding:0.7rem; border-radius:8px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.07); color:#fff; font-size:0.95rem;"
                   placeholder="Ej: Carlos, Familia Pérez...">
        </div>
        <button type="button" class="btn-send-order" onclick="activateTakeoutMode()">
            🥡 Armar Pedido Para Llevar
        </button>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/js/waiter.js?v={{ time() }}"></script>
@endsection
