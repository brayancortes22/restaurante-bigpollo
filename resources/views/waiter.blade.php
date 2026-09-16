@extends('layouts.app')

@section('title', 'Comandero Mesero')

@section('styles')
<style>
    .waiter-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .waiter-layout {
            grid-template-columns: 1fr;
        }

        .cart-panel-desktop {
            display: none;
        }

        .mobile-cart-drawer {
            display: block;
        }
    }

    /* Grilla de Mesas Táctil Compacta */
    .tables-section {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 18px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .table-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
        gap: 0.75rem;
    }

    .table-card {
        background: var(--surface-2);
        border: 2px solid var(--border-subtle);
        border-radius: 14px;
        padding: 0.85rem 0.65rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        user-select: none;
    }

    .table-card:hover {
        transform: translateY(-2px);
        border-color: #FFD200;
    }

    .table-card.selected {
        border-color: #E52521;
        box-shadow: 0 0 16px rgba(229, 37, 33, 0.5);
        background: rgba(229, 37, 33, 0.15);
    }

    .table-card.available {
        border-left: 5px solid var(--success);
    }

    .table-card.occupied {
        border-left: 5px solid #F59E0B;
        background: rgba(245, 158, 11, 0.08);
    }

    .table-card.ready-to-serve {
        border-color: #10B981 !important;
        animation: table-pulse-ready 1.5s infinite ease-in-out;
        box-shadow: 0 0 18px rgba(16, 185, 129, 0.6);
    }

    @keyframes table-pulse-ready {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.04); }
    }

    .table-num {
        font-size: 1.15rem;
        font-weight: 900;
        color: #FFFFFF;
    }

    .table-status-label {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 2px;
    }

    .available .table-status-label { color: #6EE7B7; }
    .occupied .table-status-label { color: #FBBF24; }

    /* Barra de Modo Adición */
    .addition-mode-banner {
        display: none;
        background: linear-gradient(90deg, #10B981, #059669);
        color: #FFFFFF;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        align-items: center;
        justify-content: space-between;
        font-weight: 800;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }

    /* Category Tabs */
    .category-tabs {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.4rem;
        margin-bottom: 1.25rem;
    }

    .cat-btn {
        padding: 0.6rem 1.1rem;
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        color: var(--text-muted);
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.84rem;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s;
    }

    .cat-btn.active {
        background: linear-gradient(135deg, #E52521 0%, #B91C1C 100%);
        color: #FFFFFF;
        border-color: #FFD200;
        box-shadow: 0 4px 12px rgba(229, 37, 33, 0.4);
    }

    /* Grilla de Platos Táctil */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }

    .product-card {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 1.1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        min-height: 140px;
    }

    .product-card:hover {
        transform: translateY(-2px);
        border-color: #FFD200;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    .product-name {
        font-size: 0.95rem;
        font-weight: 800;
        color: #FFFFFF;
        margin-bottom: 0.4rem;
        line-height: 1.3;
    }

    .product-price {
        font-size: 1.15rem;
        font-weight: 900;
        color: #FEF08A;
    }

    .add-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #E52521;
        color: #FFFFFF;
        border: 1px solid #FFD200;
        font-size: 1.25rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
    }

    .add-btn:active {
        transform: scale(0.92);
        background: #B91C1C;
    }

    /* Panel de Carrito Desktop & Modal Bottom Sheet */
    .cart-panel {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 1.25rem;
        position: sticky;
        top: 80px;
    }

    .cart-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0;
        border-bottom: 1px solid var(--border-subtle);
    }

    .qty-controls {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        color: #FFFFFF;
        font-weight: 800;
        cursor: pointer;
    }

    .btn-send-order {
        background: linear-gradient(135deg, #E52521 0%, #B91C1C 100%);
        color: #FFFFFF;
        border: 1px solid #FFD200;
        width: 100%;
        padding: 0.9rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 18px rgba(229, 37, 33, 0.4);
        margin-top: 1rem;
        transition: all 0.2s;
    }

    .btn-send-order:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
    }

    /* Mobile Floating Cart Bar (<= 1024px) */
    .mobile-floating-cart-bar {
        display: none;
        position: fixed;
        bottom: 74px; /* Justo arriba de la barra inferior móvil */
        left: 12px;
        right: 12px;
        background: linear-gradient(90deg, #E52521, #B91C1C);
        border: 2px solid #FFD200;
        border-radius: 16px;
        padding: 0.75rem 1rem;
        z-index: 9998;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
        align-items: center;
        justify-content: space-between;
        color: #FFFFFF;
        cursor: pointer;
    }

    @media (max-width: 1024px) {
        .mobile-floating-cart-bar.visible {
            display: flex;
        }
    }

    /* Modales Operativos */
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        padding: 1rem;
    }

    .modal-box-custom {
        background: var(--surface-1);
        border: 2px solid rgba(255, 210, 0, 0.4);
        border-radius: 20px;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 1.75rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
    }
</style>
@endsection

@section('content')
<!-- Banner de Modo Adición -->
<div id="additionBanner" class="addition-mode-banner">
    <div>
        <span>🟢 MODO ADICIÓN ACTIVO:</span>
        <span id="additionTableLabel">Mesa X</span>
    </div>
    <button onclick="cancelAdditionMode()" style="background: rgba(0,0,0,0.3); border: 1px solid #FFFFFF; color: #FFF; border-radius: 8px; padding: 0.35rem 0.75rem; cursor: pointer; font-weight: 700;">
        ✕ Cancelar Adición
    </button>
</div>

<div class="waiter-layout">
    <!-- Left Column: Tables & Menu -->
    <div>
        <!-- Tables Section -->
        <div class="tables-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <h2 style="font-size: 1.1rem; font-weight: 800; color: #FFFFFF; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🍽️</span> Salón de Mesas Big Pollo
                </h2>
                <span id="selectedTableBadge" style="font-size: 0.78rem; font-weight: 700; color: #FEF08A; background: rgba(255, 210, 0, 0.15); border: 1px solid rgba(255, 210, 0, 0.3); padding: 0.25rem 0.65rem; border-radius: 8px;">
                    Ninguna seleccionada
                </span>
            </div>
            <div class="table-cards-grid" id="tablesContainer">
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 1.5rem;">
                    ⏳ Cargando mesas del restaurante...
                </div>
            </div>
        </div>

        <!-- Categorías del Menú -->
        <div class="category-tabs" id="categoriesContainer"></div>

        <!-- Catálogo de Platos -->
        <div class="products-grid" id="productsContainer"></div>
    </div>

    <!-- Right Column: Cart Panel (Desktop) -->
    <div class="cart-panel-desktop">
        <div class="cart-panel" id="desktopCartPanel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #FFFFFF; display: flex; align-items: center; gap: 0.4rem;">
                    <span>🛒</span> Comanda Actual
                </h3>
                <span id="cartCountBadge" style="font-size: 0.78rem; color: var(--text-muted); font-weight: 700;">0 ítems</span>
            </div>

            <div id="cartItemsContainer" style="max-height: 280px; overflow-y: auto; margin-bottom: 1rem;">
                <div style="text-align: center; color: var(--text-dim); padding: 2rem 0; font-size: 0.85rem;">
                    Selecciona una mesa y agrega platos a la orden
                </div>
            </div>

            <div style="background: var(--surface-2); border-radius: 12px; padding: 0.85rem; font-size: 0.85rem; margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; color: var(--text-muted);">
                    <span>Subtotal:</span>
                    <span id="summarySubtotal">$ 0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; color: var(--text-muted);">
                    <span>Impoconsumo (8%):</span>
                    <span id="summaryTax">$ 0</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 900; color: #FEF08A; border-top: 1px dashed var(--border-subtle); padding-top: 0.4rem;">
                    <span>Total:</span>
                    <span id="summaryTotal">$ 0 COP</span>
                </div>
            </div>

            <label style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem; cursor: pointer;">
                <input type="checkbox" id="customerConsentCheck" checked style="margin-top: 2px; accent-color: #E52521;">
                <span>Autorización Habeas Data conforme a Ley 1581 para factura electrónica DIAN.</span>
            </label>

            <button id="btnSendOrder" class="btn-send-order" disabled onclick="submitOrder()">
                <span>🚀</span> Enviar a Cocina (KDS)
            </button>
        </div>
    </div>
</div>

<!-- Mobile Floating Cart Bar -->
<div id="mobileFloatingCart" class="mobile-floating-cart-bar" onclick="openMobileCartModal()">
    <div style="display: flex; align-items: center; gap: 0.6rem;">
        <span style="font-size: 1.3rem;">🛍️</span>
        <div>
            <div style="font-size: 0.88rem; font-weight: 800;" id="mCartCount">0 platos</div>
            <div style="font-size: 0.75rem; color: #FEF08A;" id="mCartTotal">$ 0 COP</div>
        </div>
    </div>
    <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid #FFD200; border-radius: 10px; padding: 0.4rem 0.8rem; font-weight: 800; font-size: 0.85rem;">
        Ver Comanda ➔
    </div>
</div>

<!-- Modal Móvil de Carrito (Bottom Sheet) -->
<div class="modal-backdrop-custom" id="mobileCartModal">
    <div class="modal-box-custom">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #FFFFFF;">🛒 Comanda para Salón</h3>
            <button onclick="closeMobileCartModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>
        <div id="mCartItemsContainer" style="max-height: 250px; overflow-y: auto; margin-bottom: 1rem;"></div>
        <div style="background: var(--surface-2); border-radius: 12px; padding: 0.85rem; font-size: 0.85rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; font-weight: 900; font-size: 1.1rem; color: #FEF08A;">
                <span>Total:</span>
                <span id="mSummaryTotal">$ 0 COP</span>
            </div>
        </div>
        <button class="btn-send-order" onclick="submitOrder()">
            <span>🚀</span> Enviar a Cocina (KDS)
        </button>
    </div>
</div>

<!-- Modal de Detalle de Mesa Ocupada -->
<div class="modal-backdrop-custom" id="occupiedTableModal">
    <div class="modal-box-custom">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #FFFFFF; display: flex; align-items: center; gap: 0.5rem;" id="occModalTitle">
                    🍗 Mesa Ocupada
                </h3>
                <div style="font-size: 0.78rem; color: #F59E0B; margin-top: 2px;" id="occModalTime">
                    ⏱️ Tiempo de atención: Calculando...
                </div>
            </div>
            <button onclick="closeOccupiedTableModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>

        <div style="font-size: 0.85rem; font-weight: 700; color: #FFD200; margin-bottom: 0.5rem;">
            📦 Platos en Comanda Activa:
        </div>
        <div id="occItemsList" style="max-height: 220px; overflow-y: auto; background: var(--surface-2); border-radius: 12px; padding: 0.75rem; margin-bottom: 1rem;">
            <!-- Ítems pedidos -->
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.15rem; font-weight: 900; color: #FEF08A; margin-bottom: 1.25rem;">
            <span>Total Acumulado:</span>
            <span id="occModalTotal">$ 0 COP</span>
        </div>

        <!-- Opciones Operativas de la Mesa -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
            <button type="button" class="btn-send-order" style="margin-top: 0; background: linear-gradient(135deg, #10B981, #059669); font-size: 0.85rem;" onclick="startAdditionMode()">
                ➕ Adicionar Platos
            </button>
            <button type="button" class="btn-send-order" style="margin-top: 0; background: var(--surface-2); border-color: var(--border-subtle); color: #FFF; font-size: 0.85rem;" onclick="showTransferControls()">
                🔄 Cambiar Mesa
            </button>
            <button type="button" class="btn-send-order" style="margin-top: 0; background: var(--surface-2); border-color: var(--border-subtle); color: #FFF; font-size: 0.85rem;" onclick="showMergeControls()">
                🔗 Unir Mesas
            </button>
            <button type="button" class="btn-send-order" style="margin-top: 0; background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #FCA5A5; font-size: 0.85rem;" onclick="releaseCurrentTable()">
                ❌ Liberar Mesa
            </button>
        </div>

        <!-- Contenedor desplegable para Transferir -->
        <div id="transferContainer" style="display: none; background: var(--surface-2); padding: 0.85rem; border-radius: 12px; margin-bottom: 1rem;">
            <label style="font-size: 0.8rem; font-weight: 700; color: #FFFFFF;">Seleccione mesa libre de destino:</label>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.4rem;">
                <select id="transferTargetSelect" class="login-input" style="flex: 1; padding: 0.5rem; font-size: 0.85rem;"></select>
                <button type="button" class="add-btn" style="width: auto; padding: 0 1rem; font-size: 0.85rem;" onclick="executeTransfer()">Mover</button>
            </div>
        </div>

        <!-- Contenedor desplegable para Unir -->
        <div id="mergeContainer" style="display: none; background: var(--surface-2); padding: 0.85rem; border-radius: 12px; margin-bottom: 1rem;">
            <label style="font-size: 0.8rem; font-weight: 700; color: #FFFFFF;">Seleccione mesa a unir:</label>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.4rem;">
                <select id="mergeTargetSelect" class="login-input" style="flex: 1; padding: 0.5rem; font-size: 0.85rem;"></select>
                <button type="button" class="add-btn" style="width: auto; padding: 0 1rem; font-size: 0.85rem;" onclick="executeMerge()">Unir</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let tables = [];
    let categories = [];
    let allProducts = [];
    let selectedTable = null;
    let cart = [];
    let isSubmitting = false;

    // Modo Adición
    let isAdditionMode = false;
    let additionTargetTable = null;

    // Audio Chime de Cocina con Web Audio API
    function playKitchenChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12); // A5
            gain.gain.setValueAtTime(0.35, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.9);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.9);
        } catch (e) {
            console.log('Audio chime not supported');
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await loadTables();
        await loadMenu();
        // Polling de mesas y alertas cada 5s
        setInterval(loadTables, 5000);
    });

    async function loadTables() {
        try {
            const res = await fetch('/api/tables');
            const data = await res.json();
            const previousTables = tables;
            tables = data.data || [];

            // Detectar si alguna mesa pasó a tener plato listo
            tables.forEach(t => {
                const prev = previousTables.find(p => p.id === t.id);
                // Si la mesa está ocupada y cocina timbró
                if (t.has_ready_order && (!prev || !prev.has_ready_order)) {
                    playKitchenChime();
                    showToast(`🛎️ ¡${t.table_number} tiene el pedido listo para servir!`, 'success');
                }
            });

            renderTables();
        } catch (e) {
            console.error('Error cargando mesas:', e);
        }
    }

    function renderTables() {
        const container = document.getElementById('tablesContainer');
        container.innerHTML = '';

        tables.forEach(table => {
            const isSelected = selectedTable && selectedTable.id === table.id;
            const isReady = table.has_ready_order;
            const card = document.createElement('div');
            card.className = `table-card ${table.status} ${isSelected ? 'selected' : ''} ${isReady ? 'ready-to-serve' : ''}`;
            
            card.onclick = () => handleTableClick(table);

            card.innerHTML = `
                <div class="table-num">${table.table_number}</div>
                <div class="table-status-label">${table.status === 'available' ? '🟢 Libre' : '🟠 Ocupada'}</div>
                <div style="font-size: 0.68rem; color: var(--text-dim); margin-top: 2px;">Cap: ${table.capacity} p</div>
                ${isReady ? '<div style="font-size: 0.72rem; color: #10B981; font-weight: 800; margin-top: 2px;">🛎️ ¡Listo!</div>' : ''}
            `;
            container.appendChild(card);
        });
    }

    async function handleTableClick(table) {
        if (isAdditionMode) {
            showToast('Finaliza o cancela el modo adición actual primero', 'info');
            return;
        }

        if (table.status === 'available') {
            // Mesa libre: seleccionarla para comanda nueva
            selectedTable = table;
            document.getElementById('selectedTableBadge').innerText = `Mesa: ${table.table_number}`;
            renderTables();
            updateCartUI();
        } else {
            // Mesa ocupada: abrir modal con detalle de comanda y opciones
            openOccupiedTableModal(table);
        }
    }

    async function openOccupiedTableModal(table) {
        selectedTable = table;
        const modal = document.getElementById('occupiedTableModal');
        modal.style.display = 'flex';
        document.getElementById('occModalTitle').innerText = `🍗 ${table.table_number} (Comanda Activa)`;
        document.getElementById('transferContainer').style.display = 'none';
        document.getElementById('mergeContainer').style.display = 'none';

        const itemsList = document.getElementById('occItemsList');
        itemsList.innerHTML = '<div style="color: var(--text-muted); text-align: center;">⏳ Consultando platos...</div>';

        try {
            const res = await fetch(`/api/tables/${table.id}/active-order`);
            const data = await res.json();

            if (!data.has_active_order) {
                itemsList.innerHTML = '<div style="color: var(--text-muted);">Sin comanda activa registrada.</div>';
                return;
            }

            const ord = data.order;
            document.getElementById('occModalTime').innerHTML = `⏱️ Tiempo en mesa: <b>${data.minutes_elapsed} min</b> ${data.is_delayed ? '🔴 <span style="color:#EF4444; font-weight:800;">¡ATRASADO!</span>' : ''}`;
            document.getElementById('occModalTotal').innerText = `$ ${Number(ord.total).toLocaleString('es-CO')} COP`;

            itemsList.innerHTML = ord.items.map(it => {
                const statusBadge = it.kitchen_status === 'ready'
                    ? '<span style="color: #34D399; font-weight: 800;">🟢 Listo</span>'
                    : (it.kitchen_status === 'cooking' ? '<span style="color: #FBBF24; font-weight: 800;">🔥 En fuego</span>' : '<span style="color: #94A3B8;">🟡 En cola</span>');

                return `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0; border-bottom: 1px dashed var(--border-subtle); font-size: 0.82rem;">
                        <div>
                            <div><b>${it.quantity}x</b> ${it.product.name}</div>
                            <div style="font-size: 0.72rem; color: var(--text-dim);">${it.notes || 'Estándar'}</div>
                        </div>
                        <div style="text-align: right;">
                            <div>$ ${Number(it.subtotal).toLocaleString('es-CO')}</div>
                            <div style="font-size: 0.72rem;">${statusBadge}</div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            itemsList.innerHTML = '<div style="color: #EF4444;">Error al obtener la comanda.</div>';
        }
    }

    function closeOccupiedTableModal() {
        document.getElementById('occupiedTableModal').style.display = 'none';
    }

    function startAdditionMode() {
        if (!selectedTable) return;
        isAdditionMode = true;
        additionTargetTable = selectedTable;
        closeOccupiedTableModal();

        document.getElementById('additionBanner').style.display = 'flex';
        document.getElementById('additionTableLabel').innerText = `${additionTargetTable.table_number}`;
        document.getElementById('btnSendOrder').innerHTML = `<span>➕</span> Enviar Adición a Cocina`;
        cart = [];
        updateCartUI();
        showToast(`Modo adición activado para ${additionTargetTable.table_number}. Selecciona los nuevos platos.`, 'info');
    }

    function cancelAdditionMode() {
        isAdditionMode = false;
        additionTargetTable = null;
        document.getElementById('additionBanner').style.display = 'none';
        document.getElementById('btnSendOrder').innerHTML = `<span>🚀</span> Enviar a Cocina (KDS)`;
        cart = [];
        updateCartUI();
    }

    function showTransferControls() {
        const wrap = document.getElementById('transferContainer');
        wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';

        const select = document.getElementById('transferTargetSelect');
        const availableTables = tables.filter(t => t.status === 'available');
        select.innerHTML = availableTables.map(t => `<option value="${t.id}">${t.table_number}</option>`).join('');
    }

    async function executeTransfer() {
        const toTableId = document.getElementById('transferTargetSelect').value;
        if (!selectedTable || !toTableId) return;

        try {
            const res = await fetch(`/api/tables/${selectedTable.id}/transfer`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ to_table_id: parseInt(toTableId) })
            });
            const data = await res.json();
            if (res.ok) {
                showToast(data.message, 'success');
                closeOccupiedTableModal();
                selectedTable = null;
                await loadTables();
            } else {
                showToast(data.message || 'Error al trasladar mesa', 'error');
            }
        } catch (e) {
            showToast('Error de conexión al trasladar mesa', 'error');
        }
    }

    function showMergeControls() {
        const wrap = document.getElementById('mergeContainer');
        wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';

        const select = document.getElementById('mergeTargetSelect');
        const otherTables = tables.filter(t => t.id !== selectedTable.id);
        select.innerHTML = otherTables.map(t => `<option value="${t.id}">${t.table_number} (${t.status === 'available' ? 'Libre' : 'Ocupada'})</option>`).join('');
    }

    async function executeMerge() {
        const targetTableId = document.getElementById('mergeTargetSelect').value;
        if (!selectedTable || !targetTableId) return;

        try {
            const res = await fetch(`/api/tables/${selectedTable.id}/merge`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ target_table_id: parseInt(targetTableId) })
            });
            const data = await res.json();
            if (res.ok) {
                showToast(data.message, 'success');
                closeOccupiedTableModal();
                selectedTable = null;
                await loadTables();
            } else {
                showToast(data.message || 'Error al unir mesas', 'error');
            }
        } catch (e) {
            showToast('Error al conectar con el servidor', 'error');
        }
    }

    async function releaseCurrentTable() {
        if (!selectedTable) return;
        if (!confirm(`¿Confirmas liberar la ${selectedTable.table_number}? Si tiene orden abierta se cancelará y revertirá el stock.`)) return;

        try {
            const res = await fetch(`/api/tables/${selectedTable.id}/release`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ reason: 'Liberación voluntaria de mesero' })
            });
            const data = await res.json();
            if (res.ok) {
                showToast(data.message, 'success');
                closeOccupiedTableModal();
                selectedTable = null;
                await loadTables();
            } else {
                showToast(data.message || 'Error al liberar mesa', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        }
    }

    // Catálogo y Menú
    async function loadMenu() {
        try {
            const res = await fetch('/api/menu');
            const data = await res.json();
            categories = data.data || [];
            renderCategories();
            if (categories.length > 0) {
                renderProducts(categories[0].id);
            }
        } catch (e) {
            console.error('Error cargando menú:', e);
        }
    }

    function renderCategories() {
        const container = document.getElementById('categoriesContainer');
        container.innerHTML = '';

        categories.forEach((cat, idx) => {
            const btn = document.createElement('button');
            btn.className = `cat-btn ${idx === 0 ? 'active' : ''}`;
            btn.innerText = cat.name;
            btn.onclick = () => {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                renderProducts(cat.id);
            };
            container.appendChild(btn);
        });
    }

    function renderProducts(categoryId) {
        const container = document.getElementById('productsContainer');
        container.innerHTML = '';

        const cat = categories.find(c => c.id === categoryId);
        if (!cat || !cat.products) return;

        cat.products.forEach(prod => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <div>
                    <div class="product-name">${prod.name}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.3;">
                        ${prod.description || 'Especialidad Big Pollo con fórmula de la casa.'}
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem;">
                    <div class="product-price">$ ${Number(prod.price).toLocaleString('es-CO')}</div>
                    <button class="add-btn" onclick="addToCart(${prod.id}, '${prod.name}', ${prod.price})">+</button>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function addToCart(productId, name, price) {
        const existing = cart.find(i => i.product_id === productId);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({ product_id: productId, name, price, quantity: 1 });
        }
        updateCartUI();
    }

    function changeCartQty(productId, delta) {
        const idx = cart.findIndex(i => i.product_id === productId);
        if (idx !== -1) {
            cart[idx].quantity += delta;
            if (cart[idx].quantity <= 0) {
                cart.splice(idx, 1);
            }
        }
        updateCartUI();
    }

    function updateCartUI() {
        const desktopContainer = document.getElementById('cartItemsContainer');
        const mobileContainer = document.getElementById('mCartItemsContainer');
        const floatBar = document.getElementById('mobileFloatingCart');

        const totalItems = cart.reduce((a, b) => a + b.quantity, 0);
        const subtotal = cart.reduce((a, b) => a + (b.price * b.quantity), 0);
        const tax = Math.round(subtotal * 0.08);
        const total = subtotal + tax;

        const html = cart.length === 0
            ? '<div style="text-align: center; color: var(--text-dim); padding: 2rem 0; font-size: 0.85rem;">Carrito vacío. Agrega platos del menú.</div>'
            : cart.map(i => `
                <div class="cart-item-row">
                    <div>
                        <div style="font-weight: 700; font-size: 0.88rem; color: #FFFFFF;">${i.name}</div>
                        <div style="font-size: 0.75rem; color: #FEF08A;">$ ${Number(i.price * i.quantity).toLocaleString('es-CO')}</div>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="changeCartQty(${i.product_id}, -1)">-</button>
                        <span style="font-weight: 800; min-width: 20px; text-align: center;">${i.quantity}</span>
                        <button class="qty-btn" onclick="changeCartQty(${i.product_id}, 1)">+</button>
                    </div>
                </div>
            `).join('');

        desktopContainer.innerHTML = html;
        if (mobileContainer) mobileContainer.innerHTML = html;

        document.getElementById('summarySubtotal').innerText = `$ ${subtotal.toLocaleString('es-CO')}`;
        document.getElementById('summaryTax').innerText = `$ ${tax.toLocaleString('es-CO')}`;
        document.getElementById('summaryTotal').innerText = `$ ${total.toLocaleString('es-CO')} COP`;
        document.getElementById('cartCountBadge').innerText = `${totalItems} ítems`;

        // Mobile Floating Bar
        if (floatBar) {
            if (cart.length > 0) {
                floatBar.classList.add('visible');
                document.getElementById('mCartCount').innerText = `${totalItems} platos seleccionados`;
                document.getElementById('mCartTotal').innerText = `$ ${total.toLocaleString('es-CO')} COP`;
                if (document.getElementById('mSummaryTotal')) {
                    document.getElementById('mSummaryTotal').innerText = `$ ${total.toLocaleString('es-CO')} COP`;
                }
            } else {
                floatBar.classList.remove('visible');
            }
        }

        const canSubmit = (selectedTable || isAdditionMode) && cart.length > 0;
        document.getElementById('btnSendOrder').disabled = !canSubmit;
    }

    function openMobileCartModal() {
        document.getElementById('mobileCartModal').style.display = 'flex';
    }

    function closeMobileCartModal() {
        document.getElementById('mobileCartModal').style.display = 'none';
    }

    async function submitOrder() {
        if (isSubmitting) return;

        // Si estamos en Modo Adición
        if (isAdditionMode && additionTargetTable) {
            await submitAddition();
            return;
        }

        if (!selectedTable || cart.length === 0) {
            showToast('Selecciona una mesa disponible y al menos un plato', 'error');
            return;
        }

        isSubmitting = true;
        const btn = document.getElementById('btnSendOrder');
        btn.disabled = true;
        btn.innerText = '⏳ Enviando a Cocina...';

        const consent = document.getElementById('customerConsentCheck').checked;
        const payload = {
            restaurant_table_id: selectedTable.id,
            type: 'dine_in',
            customer_consent: consent,
            items: cart.map(i => ({
                product_id: i.product_id,
                quantity: i.quantity,
                notes: ''
            }))
        };

        try {
            const res = await fetch('/api/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok) {
                showToast(`¡Comanda #${data.data.order_number} enviada a cocina!`, 'success');
                cart = [];
                selectedTable = null;
                document.getElementById('selectedTableBadge').innerText = 'Ninguna seleccionada';
                closeMobileCartModal();
                updateCartUI();
                await loadTables();
            } else {
                showToast(data.message || 'Error al registrar orden', 'error');
            }
        } catch (e) {
            showToast('Error de conexión', 'error');
        } finally {
            isSubmitting = false;
            btn.innerHTML = '<span>🚀</span> Enviar a Cocina (KDS)';
            updateCartUI();
        }
    }

    async function submitAddition() {
        isSubmitting = true;
        const btn = document.getElementById('btnSendOrder');
        btn.disabled = true;
        btn.innerText = '⏳ Guardando adición...';

        const payload = {
            items: cart.map(i => ({
                product_id: i.product_id,
                quantity: i.quantity,
                notes: 'Adición comanda'
            }))
        };

        try {
            const res = await fetch(`/api/tables/${additionTargetTable.id}/add-items`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok) {
                showToast(`¡Nuevos platos adicionados a ${additionTargetTable.table_number}!`, 'success');
                cancelAdditionMode();
                closeMobileCartModal();
                await loadTables();
            } else {
                showToast(data.message || 'Error al guardar adición', 'error');
            }
        } catch (e) {
            showToast('Error de conexión al enviar adición', 'error');
        } finally {
            isSubmitting = false;
            updateCartUI();
        }
    }
</script>
@endsection
