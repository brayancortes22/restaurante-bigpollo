/**
 * Restaurante Big Pollo — Comandero Mesero (waiter.js)
 * Lógica modular del lado del cliente, búsqueda en 0ms y operaciones de mesa
 */

let tables = [];
let categories = [];
let allProducts = [];
let selectedTable = null;
let cart = [];
let isSubmitting = false;

// Estado de Búsqueda y Filtros
let currentCategoryId = null;
let searchTerm = '';

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
        console.log('Audio chime no soportado');
    }
}

// Feedback Háptico para Pantallas Táctiles Móviles
function triggerHaptic(duration = 35) {
    if (navigator.vibrate) {
        try {
            navigator.vibrate(duration);
        } catch (e) {
            // Silencioso si no está permitido
        }
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadTables();
    await loadMenu();
    setupSearchEvents();
    // Sondeo de mesas cada 5 segundos
    setInterval(loadTables, 5000);
});

/* --------------------------------------------------------------------------
   Gestión de Mesas del Salón
   -------------------------------------------------------------------------- */
async function loadTables() {
    try {
        const res = await fetch('/api/tables');
        const data = await res.json();
        const previousTables = tables;
        tables = data.data || [];

        // Detectar si alguna mesa pasó a tener plato listo
        tables.forEach(t => {
            const prev = previousTables.find(p => p.id === t.id);
            if (t.has_ready_order && (!prev || !prev.has_ready_order)) {
                playKitchenChime();
                triggerHaptic(60);
                if (typeof showToast === 'function') {
                    showToast(`🛎️ ¡${t.table_number} tiene el pedido listo para servir!`, 'success');
                }
            }
        });

        renderTables();
    } catch (e) {
        console.error('Error cargando mesas:', e);
    }
}

function renderTables() {
    const container = document.getElementById('tablesContainer');
    if (!container) return;
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
            <div class="table-capacity-label">Cap: ${table.capacity} p</div>
            ${isReady ? '<div class="table-ready-pill">🛎️ ¡Listo!</div>' : ''}
        `;
        container.appendChild(card);
    });
}

async function handleTableClick(table) {
    triggerHaptic(25);
    if (isAdditionMode) {
        if (typeof showToast === 'function') showToast('Finaliza o cancela el modo adición actual primero', 'info');
        return;
    }

    if (table.status === 'available') {
        selectedTable = table;
        const badge = document.getElementById('selectedTableBadge');
        if (badge) badge.innerText = `Mesa: ${table.table_number}`;
        renderTables();
        updateCartUI();
    } else {
        openOccupiedTableModal(table);
    }
}

async function openOccupiedTableModal(table) {
    selectedTable = table;
    const modal = document.getElementById('occupiedTableModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.getElementById('occModalTitle').innerText = `🍗 ${table.table_number} (Comanda Activa)`;
    document.getElementById('transferContainer').style.display = 'none';
    document.getElementById('mergeContainer').style.display = 'none';

    const itemsList = document.getElementById('occItemsList');
    itemsList.innerHTML = '<div style="color: var(--text-muted); text-align: center; padding: 1rem;">⏳ Consultando platos...</div>';

    try {
        const res = await fetch(`/api/tables/${table.id}/active-order`);
        const data = await res.json();

        if (!data.has_active_order) {
            itemsList.innerHTML = '<div style="color: var(--text-muted); text-align: center;">Sin comanda activa registrada.</div>';
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
                <div class="occ-item-row">
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
        itemsList.innerHTML = '<div style="color: #EF4444; text-align: center;">Error al obtener la comanda.</div>';
    }
}

function closeOccupiedTableModal() {
    const modal = document.getElementById('occupiedTableModal');
    if (modal) modal.style.display = 'none';
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
    if (typeof showToast === 'function') {
        showToast(`Modo adición activado para ${additionTargetTable.table_number}. Selecciona los nuevos platos.`, 'info');
    }
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
            if (typeof showToast === 'function') showToast(data.message, 'success');
            closeOccupiedTableModal();
            selectedTable = null;
            await loadTables();
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Error al trasladar mesa', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error de conexión al trasladar mesa', 'error');
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
            if (typeof showToast === 'function') showToast(data.message, 'success');
            closeOccupiedTableModal();
            selectedTable = null;
            await loadTables();
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Error al unir mesas', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error al conectar con el servidor', 'error');
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
            if (typeof showToast === 'function') showToast(data.message, 'success');
            closeOccupiedTableModal();
            selectedTable = null;
            await loadTables();
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Error al liberar mesa', 'error');
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error de conexión', 'error');
    }
}

/* --------------------------------------------------------------------------
   Catálogo de Menú y Búsqueda Táctil en 0ms (Hora Pico)
   -------------------------------------------------------------------------- */
async function loadMenu() {
    try {
        const res = await fetch('/api/menu');
        const data = await res.json();
        categories = data.data || [];
        
        // Aplanar todos los productos para búsqueda ultra-rápida en memoria
        allProducts = [];
        categories.forEach(cat => {
            if (cat.products) {
                cat.products.forEach(p => {
                    allProducts.push({
                        ...p,
                        category_id: cat.id,
                        category_name: cat.name
                    });
                });
            }
        });

        renderCategories();
        if (categories.length > 0) {
            currentCategoryId = categories[0].id;
            applyFilters();
        }
    } catch (e) {
        console.error('Error cargando menú:', e);
    }
}

function setupSearchEvents() {
    const searchInput = document.getElementById('waiterSearchInput');
    const clearBtn = document.getElementById('searchClearBtn');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchTerm = e.target.value.trim().toLowerCase();
            if (clearBtn) {
                clearBtn.style.display = searchTerm.length > 0 ? 'flex' : 'none';
            }
            applyFilters();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            searchTerm = '';
            clearBtn.style.display = 'none';
            applyFilters();
            if (searchInput) searchInput.focus();
        });
    }
}

function renderCategories() {
    const container = document.getElementById('categoriesContainer');
    if (!container) return;
    container.innerHTML = '';

    // Opción "Todos los platos"
    const allBtn = document.createElement('button');
    allBtn.className = `cat-btn ${currentCategoryId === null ? 'active' : ''}`;
    allBtn.innerText = '🔥 Todos los Platos';
    allBtn.onclick = () => {
        triggerHaptic(20);
        currentCategoryId = null;
        updateCategoryButtons();
        applyFilters();
    };
    container.appendChild(allBtn);

    categories.forEach((cat) => {
        const btn = document.createElement('button');
        btn.className = `cat-btn ${currentCategoryId === cat.id ? 'active' : ''}`;
        btn.dataset.catId = cat.id;
        btn.innerText = cat.name;
        btn.onclick = () => {
            triggerHaptic(20);
            currentCategoryId = cat.id;
            updateCategoryButtons();
            applyFilters();
        };
        container.appendChild(btn);
    });
}

function updateCategoryButtons() {
    document.querySelectorAll('.cat-btn').forEach(btn => {
        if (currentCategoryId === null && btn.innerText.includes('Todos')) {
            btn.classList.add('active');
        } else if (btn.dataset.catId == currentCategoryId) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

function applyFilters() {
    let filtered = allProducts;

    // Filtrar por categoría seleccionada si aplica
    if (currentCategoryId !== null) {
        filtered = filtered.filter(p => p.category_id === currentCategoryId);
    }

    // Filtrar por término de búsqueda en memoria (0ms latencia)
    if (searchTerm.length > 0) {
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(searchTerm) || 
            (p.description && p.description.toLowerCase().includes(searchTerm)) ||
            (p.category_name && p.category_name.toLowerCase().includes(searchTerm))
        );
    }

    renderProductCards(filtered);
}

function renderProductCards(productsToRender) {
    const container = document.getElementById('productsContainer');
    if (!container) return;
    container.innerHTML = '';

    if (productsToRender.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-dim); padding: 3rem 1rem;">
                🔍 No se encontraron platos con "<b>${searchTerm}</b>". Intenta con otra palabra.
            </div>
        `;
        return;
    }

    productsToRender.forEach(prod => {
        const inCartItem = cart.find(i => i.product_id === prod.id);
        const qty = inCartItem ? inCartItem.quantity : 0;

        const card = document.createElement('div');
        card.className = `product-card ${qty > 0 ? 'in-cart' : ''}`;
        card.id = `prodCard_${prod.id}`;

        // 1-Tap Quick Add: Pulsar en cualquier lugar de la tarjeta añade +1
        card.onclick = (e) => {
            // Evitar doble acción si pulsó los botones táctiles internos
            if (e.target.closest('.btn-touch-sub') || e.target.closest('.btn-touch-add')) return;
            quickAdd(prod.id, prod.name, prod.price);
        };

        card.innerHTML = `
            ${qty > 0 ? `<div class="product-qty-badge" id="badge_${prod.id}">x${qty} en orden</div>` : ''}
            <div>
                <div class="product-name">${prod.name}</div>
                <div class="product-desc">
                    ${prod.description || 'Especialidad Big Pollo con fórmula de la casa.'}
                </div>
            </div>
            <div class="product-bottom-row">
                <div class="product-price">$ ${Number(prod.price).toLocaleString('es-CO')}</div>
                <div class="product-touch-controls">
                    ${qty > 0 ? `
                        <button type="button" class="btn-touch-sub" onclick="quickSub(${prod.id}, event)" title="Restar 1">-</button>
                    ` : ''}
                    <button type="button" class="btn-touch-add" onclick="quickAdd(${prod.id}, '${prod.name.replace(/'/g, "\\'")}', ${prod.price}, event)" title="Sumar 1">
                        <span>+</span>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

/* --------------------------------------------------------------------------
   Acciones Táctiles de Comanda Rápida
   -------------------------------------------------------------------------- */
function quickAdd(productId, name, price, event) {
    if (event) event.stopPropagation();
    triggerHaptic(35);

    const existing = cart.find(i => i.product_id === productId);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ product_id: productId, name, price, quantity: 1 });
    }

    updateCartUI();
    refreshCardVisual(productId);
}

function quickSub(productId, event) {
    if (event) event.stopPropagation();
    triggerHaptic(30);

    const idx = cart.findIndex(i => i.product_id === productId);
    if (idx !== -1) {
        cart[idx].quantity--;
        if (cart[idx].quantity <= 0) {
            cart.splice(idx, 1);
        }
    }

    updateCartUI();
    refreshCardVisual(productId);
}

function refreshCardVisual(productId) {
    const card = document.getElementById(`prodCard_${productId}`);
    if (!card) return;

    const inCartItem = cart.find(i => i.product_id === productId);
    const qty = inCartItem ? inCartItem.quantity : 0;

    if (qty > 0) {
        card.classList.add('in-cart');
        let badge = card.querySelector('.product-qty-badge');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'product-qty-badge';
            card.prepend(badge);
        }
        badge.innerText = `x${qty} en orden`;

        // Asegurar que el botón de restar esté visible
        let subBtn = card.querySelector('.btn-touch-sub');
        if (!subBtn) {
            const controls = card.querySelector('.product-touch-controls');
            if (controls) {
                const newSub = document.createElement('button');
                newSub.type = 'button';
                newSub.className = 'btn-touch-sub';
                newSub.title = 'Restar 1';
                newSub.innerText = '-';
                newSub.onclick = (e) => quickSub(productId, e);
                controls.prepend(newSub);
            }
        }
    } else {
        card.classList.remove('in-cart');
        const badge = card.querySelector('.product-qty-badge');
        if (badge) badge.remove();
        const subBtn = card.querySelector('.btn-touch-sub');
        if (subBtn) subBtn.remove();
    }
}

function changeCartQty(productId, delta) {
    triggerHaptic(25);
    const idx = cart.findIndex(i => i.product_id === productId);
    if (idx !== -1) {
        cart[idx].quantity += delta;
        if (cart[idx].quantity <= 0) {
            cart.splice(idx, 1);
        }
    }
    updateCartUI();
    refreshCardVisual(productId);
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
        ? '<div class="cart-empty-state">Carrito vacío. Agrega platos del menú.</div>'
        : cart.map(i => `
            <div class="cart-item-row">
                <div>
                    <div class="cart-item-name">${i.name}</div>
                    <div class="cart-item-subtotal">$ ${Number(i.price * i.quantity).toLocaleString('es-CO')}</div>
                </div>
                <div class="qty-controls">
                    <button type="button" class="qty-btn" onclick="changeCartQty(${i.product_id}, -1)">-</button>
                    <span class="qty-value-label">${i.quantity}</span>
                    <button type="button" class="qty-btn" onclick="changeCartQty(${i.product_id}, 1)">+</button>
                </div>
            </div>
        `).join('');

    if (desktopContainer) desktopContainer.innerHTML = html;
    if (mobileContainer) mobileContainer.innerHTML = html;

    const subEl = document.getElementById('summarySubtotal');
    const taxEl = document.getElementById('summaryTax');
    const totEl = document.getElementById('summaryTotal');
    const countBadge = document.getElementById('cartCountBadge');

    if (subEl) subEl.innerText = `$ ${subtotal.toLocaleString('es-CO')}`;
    if (taxEl) taxEl.innerText = `$ ${tax.toLocaleString('es-CO')}`;
    if (totEl) totEl.innerText = `$ ${total.toLocaleString('es-CO')} COP`;
    if (countBadge) countBadge.innerText = `${totalItems} ítems`;

    // Mobile Floating Bar
    if (floatBar) {
        if (cart.length > 0) {
            floatBar.classList.add('visible');
            const mCount = document.getElementById('mCartCount');
            const mTot = document.getElementById('mCartTotal');
            const mSumTot = document.getElementById('mSummaryTotal');

            if (mCount) mCount.innerText = `${totalItems} platos seleccionados`;
            if (mTot) mTot.innerText = `$ ${total.toLocaleString('es-CO')} COP`;
            if (mSumTot) mSumTot.innerText = `$ ${total.toLocaleString('es-CO')} COP`;
        } else {
            floatBar.classList.remove('visible');
        }
    }

    const canSubmit = (selectedTable || isAdditionMode) && cart.length > 0;
    const btnSend = document.getElementById('btnSendOrder');
    if (btnSend) btnSend.disabled = !canSubmit;
}

function openMobileCartModal() {
    triggerHaptic(20);
    const m = document.getElementById('mobileCartModal');
    if (m) m.style.display = 'flex';
}

function closeMobileCartModal() {
    const m = document.getElementById('mobileCartModal');
    if (m) m.style.display = 'none';
}

/* --------------------------------------------------------------------------
   Envío de Comanda / Adición a Cocina
   -------------------------------------------------------------------------- */
async function submitOrder() {
    if (isSubmitting) return;

    if (!selectedTable && !isAdditionMode) {
        if (typeof showToast === 'function') showToast('Selecciona primero una mesa disponible', 'error');
        return;
    }

    if (cart.length === 0) {
        if (typeof showToast === 'function') showToast('El carrito está vacío', 'error');
        return;
    }

    isSubmitting = true;
    const btnSend = document.getElementById('btnSendOrder');
    if (btnSend) {
        btnSend.disabled = true;
        btnSend.innerText = '⏳ Procesando pedido...';
    }

    try {
        if (isAdditionMode && additionTargetTable) {
            // Caso A: Adición a comanda abierta
            const res = await fetch(`/api/tables/${additionTargetTable.id}/add-items`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    items: cart.map(i => ({
                        product_id: i.product_id,
                        quantity: i.quantity,
                        notes: 'Adición rápida en salón'
                    }))
                })
            });

            const data = await res.json();
            if (res.ok) {
                triggerHaptic(70);
                if (typeof showToast === 'function') showToast(data.message, 'success');
                cancelAdditionMode();
                closeMobileCartModal();
                await loadTables();
                applyFilters();
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'Error al adicionar ítems', 'error');
            }
        } else {
            // Caso B: Nueva comanda en mesa libre
            const consentGiven = document.getElementById('customerConsentCheck')?.checked ?? true;
            const payload = {
                order_type: 'dine_in',
                table_id: selectedTable.id,
                customer_name: `Mesa ${selectedTable.table_number}`,
                customer_document: '222222222222',
                customer_email: 'cliente@bigpollo.com',
                customer_consent: consentGiven,
                items: cart.map(i => ({
                    product_id: i.product_id,
                    quantity: i.quantity,
                    notes: 'Comanda salón'
                }))
            };

            const res = await fetch('/api/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok) {
                triggerHaptic(70);
                if (typeof showToast === 'function') showToast(`🎉 ¡Comanda ${data.data.order_number} enviada a cocina!`, 'success');
                cart = [];
                selectedTable = null;
                const badge = document.getElementById('selectedTableBadge');
                if (badge) badge.innerText = 'Ninguna seleccionada';
                updateCartUI();
                closeMobileCartModal();
                await loadTables();
                applyFilters();
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'Error al enviar comanda', 'error');
            }
        }
    } catch (e) {
        if (typeof showToast === 'function') showToast('Error de conexión con el servidor', 'error');
    } finally {
        isSubmitting = false;
        if (btnSend) {
            btnSend.innerHTML = isAdditionMode ? '<span>➕</span> Enviar Adición a Cocina' : '<span>🚀</span> Enviar a Cocina (KDS)';
            btnSend.disabled = (cart.length === 0);
        }
    }
}
