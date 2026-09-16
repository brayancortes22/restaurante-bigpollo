@extends('layouts.app')

@section('title', 'Comandero Mesero')

@section('styles')
<style>
    .waiter-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .waiter-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Table Grid */
    .tables-section {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 1.05rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.75rem;
    }

    .table-card {
        background: var(--surface-2);
        border: 2px solid var(--border-subtle);
        border-radius: 14px;
        padding: 1rem 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    .table-card:hover {
        transform: translateY(-2px);
        border-color: var(--border-highlight);
    }

    .table-card.selected {
        border-color: var(--primary);
        box-shadow: 0 0 16px var(--primary-glow);
        background: rgba(99, 102, 241, 0.15);
    }

    .table-card.available {
        border-left: 4px solid var(--success);
    }

    .table-card.occupied {
        border-left: 4px solid var(--warning);
    }

    .table-num {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .table-status-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .available .table-status-label { color: var(--success); }
    .occupied .table-status-label { color: var(--warning); }

    /* Category Tabs */
    .category-tabs {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }

    .cat-btn {
        padding: 0.6rem 1.2rem;
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        color: var(--text-muted);
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.2s;
    }

    .cat-btn:hover {
        color: var(--text-main);
        background: var(--surface-2);
    }

    .cat-btn.active {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary);
        box-shadow: 0 4px 12px var(--primary-glow);
    }

    /* Product Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .product-card {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        min-height: 140px;
    }

    .product-card:hover {
        transform: translateY(-3px);
        border-color: var(--border-highlight);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }

    .product-name {
        font-size: 0.98rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1rem;
    }

    .product-price {
        font-size: 1.15rem;
        font-weight: 800;
        color: #F59E0B;
    }

    .add-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--primary);
        color: #FFFFFF;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .add-btn:hover {
        background: var(--primary-hover);
        transform: scale(1.08);
    }

    /* Cart Sidebar */
    .cart-panel {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        height: fit-content;
        position: sticky;
        top: 85px;
    }

    .cart-title {
        font-size: 1.15rem;
        font-weight: 800;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-items {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-height: 360px;
        overflow-y: auto;
        margin-bottom: 1.25rem;
        padding-right: 0.25rem;
    }

    .cart-item {
        background: var(--surface-2);
        border-radius: 12px;
        padding: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-size: 0.88rem;
        font-weight: 700;
    }

    .item-sub {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .qty-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--surface-3);
        color: #FFFFFF;
        border: none;
        cursor: pointer;
        font-weight: 700;
    }

    .cart-summary {
        border-top: 1px solid var(--border-subtle);
        padding-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.88rem;
        color: var(--text-muted);
    }

    .summary-row.total {
        font-size: 1.2rem;
        font-weight: 800;
        color: #FFFFFF;
        margin-top: 0.5rem;
    }

    .send-order-btn {
        margin-top: 1.25rem;
        width: 100%;
        padding: 1rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: #FFFFFF;
        border: none;
        font-size: 1.05rem;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 4px 16px var(--success-glow);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .send-order-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
    }

    .send-order-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .legal-consent {
        margin-top: 0.85rem;
        font-size: 0.74rem;
        color: var(--text-dim);
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        line-height: 1.4;
    }
</style>
@endsection

@section('content')
<div class="waiter-grid">
    <!-- Left Column: Tables & Menu -->
    <div>
        <!-- Table Selector -->
        <div class="tables-section">
            <div class="section-header">
                <div class="section-title">
                    <span>🪑</span> Selecciona Mesa del Salón
                </div>
                <div id="selected-table-badge" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                    Ninguna seleccionada
                </div>
            </div>
            <div class="table-cards-grid" id="tables-container">
                <div style="color: var(--text-muted); font-size: 0.88rem;">Cargando mesas...</div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs" id="categories-container"></div>

        <!-- Product Catalog -->
        <div class="products-grid" id="products-container"></div>
    </div>

    <!-- Right Column: Current Order Cart -->
    <div>
        <div class="cart-panel">
            <div class="cart-title">
                <span>🛒 Comanda Actual</span>
                <span id="cart-item-count" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">0 ítems</span>
            </div>

            <div class="cart-items" id="cart-items-container">
                <div style="text-align: center; color: var(--text-dim); padding: 2rem 0; font-size: 0.9rem;">
                    Selecciona una mesa y agrega productos a la comanda
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="summary-subtotal" class="tabular-nums">$ 0</span>
                </div>
                <div class="summary-row">
                    <span>Impoconsumo (8%):</span>
                    <span id="summary-tax" class="tabular-nums">$ 0</span>
                </div>
                <div class="summary-row total">
                    <span>Total Comanda:</span>
                    <span id="summary-total" class="tabular-nums" style="color: #F59E0B;">$ 0</span>
                </div>
            </div>

            <div class="legal-consent">
                <input type="checkbox" id="customer-consent-check" checked style="margin-top: 2px; accent-color: var(--primary);">
                <label for="customer-consent-check">
                    Autorización Habeas Data: El comensal autoriza el tratamiento de datos para factura electrónica conforme a la Ley 1581 de 2012.
                </label>
            </div>

            <button id="send-order-btn" class="send-order-btn" disabled onclick="sendOrder()">
                <span>🚀</span> Enviar a Cocina (KDS)
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let tables = [];
    let categories = [];
    let selectedTableId = null;
    let cart = []; // [{product_id, name, price, tax_rate, quantity}]

    async function init() {
        await loadTables();
        await loadMenu();
    }

    async function loadTables() {
        try {
            const res = await fetch('/api/tables');
            const data = await res.json();
            tables = data.data;
            renderTables();
        } catch (e) {
            showToast('Error cargando mesas', 'error');
        }
    }

    function renderTables() {
        const container = document.getElementById('tables-container');
        container.innerHTML = '';

        tables.forEach(table => {
            const card = document.createElement('div');
            card.className = `table-card ${table.status} ${selectedTableId === table.id ? 'selected' : ''}`;
            card.onclick = () => selectTable(table.id, table.table_number);
            
            card.innerHTML = `
                <div class="table-num">${table.table_number}</div>
                <div class="table-status-label">${table.status === 'available' ? 'Libre' : 'Ocupada'}</div>
                <div style="font-size: 0.68rem; color: var(--text-dim); margin-top: 2px;">Cap: ${table.capacity}</div>
            `;
            container.appendChild(card);
        });
    }

    function selectTable(id, number) {
        selectedTableId = id;
        document.getElementById('selected-table-badge').innerText = `Mesa: ${number}`;
        renderTables();
        updateSendButton();
    }

    async function loadMenu() {
        try {
            const res = await fetch('/api/menu');
            const data = await res.json();
            categories = data.data;
            renderCategories();
            if (categories.length > 0) {
                renderProducts(categories[0].id);
            }
        } catch (e) {
            showToast('Error cargando menú', 'error');
        }
    }

    function renderCategories() {
        const container = document.getElementById('categories-container');
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
        const container = document.getElementById('products-container');
        container.innerHTML = '';

        const cat = categories.find(c => c.id === categoryId);
        if (!cat || !cat.products) return;

        cat.products.forEach(prod => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <div>
                    <div class="product-name">${prod.name}</div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">${prod.description || 'Delicioso producto preparado al instante'}</div>
                </div>
                <div class="product-footer">
                    <div class="product-price tabular-nums">${formatCOP(prod.price)}</div>
                    <button class="add-btn" onclick="addToCart(${prod.id}, '${prod.name.replace(/'/g, "\\'")}', ${prod.price}, ${prod.tax_percentage})">+</button>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function addToCart(productId, name, price, taxRate) {
        const existing = cart.find(i => i.product_id === productId);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                product_id: productId,
                name: name,
                price: parseFloat(price),
                tax_rate: parseFloat(taxRate),
                quantity: 1
            });
        }
        renderCart();
    }

    function changeQty(productId, delta) {
        const item = cart.find(i => i.product_id === productId);
        if (!item) return;
        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.product_id !== productId);
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        if (cart.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: var(--text-dim); padding: 2rem 0; font-size: 0.9rem;">
                    Selecciona una mesa y agrega productos a la comanda
                </div>
            `;
        } else {
            container.innerHTML = '';
            cart.forEach(item => {
                const el = document.createElement('div');
                el.className = 'cart-item';
                el.innerHTML = `
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-sub tabular-nums">${formatCOP(item.price)} c/u</div>
                    </div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="changeQty(${item.product_id}, -1)">-</button>
                        <span style="font-weight: 800; font-size: 0.9rem; min-width: 18px; text-align: center;">${item.quantity}</span>
                        <button class="qty-btn" onclick="changeQty(${item.product_id}, 1)">+</button>
                    </div>
                `;
                container.appendChild(el);
            });
        }

        let subtotal = 0;
        let tax = 0;
        cart.forEach(i => {
            const itemSub = i.price * i.quantity;
            subtotal += itemSub;
            tax += (itemSub * (i.tax_rate / 100));
        });

        const total = subtotal + tax;

        document.getElementById('summary-subtotal').innerText = formatCOP(subtotal);
        document.getElementById('summary-tax').innerText = formatCOP(tax);
        document.getElementById('summary-total').innerText = formatCOP(total);
        document.getElementById('cart-item-count').innerText = `${cart.reduce((a, b) => a + b.quantity, 0)} ítems`;

        updateSendButton();
    }

    function updateSendButton() {
        const btn = document.getElementById('send-order-btn');
        btn.disabled = !(selectedTableId && cart.length > 0);
    }

    async function sendOrder() {
        if (!selectedTableId || cart.length === 0) return;

        const consent = document.getElementById('customer-consent-check').checked;
        const btn = document.getElementById('send-order-btn');
        btn.disabled = true;
        btn.innerText = 'Enviando comanda...';

        const payload = {
            restaurant_table_id: selectedTableId,
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
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok) {
                showToast(`¡Comanda ${data.data.order_number} enviada a Cocina!`, 'success');
                cart = [];
                selectedTableId = null;
                document.getElementById('selected-table-badge').innerText = 'Ninguna seleccionada';
                renderCart();
                await loadTables();
            } else {
                showToast(data.message || 'Error al enviar la comanda', 'error');
            }
        } catch (e) {
            showToast('Error de conexión con el servidor', 'error');
        } finally {
            btn.innerHTML = '<span>🚀</span> Enviar a Cocina (KDS)';
            updateSendButton();
        }
    }

    document.addEventListener('DOMContentLoaded', init);
</script>
@endsection
