@extends('layouts.app')

@section('title', 'Administración de Menú & Recetas')

@section('styles')
<style>
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin-bottom: 2rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-subtle);
    }

    .admin-title-wrap h1 {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #FFFFFF 0%, #FEF08A 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .admin-title-wrap p {
        color: var(--text-muted);
        font-size: 0.92rem;
        margin-top: 0.35rem;
    }

    .btn-create-dish {
        background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
        color: #FFFFFF;
        border: 1px solid #F59E0B;
        padding: 0.75rem 1.4rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.92rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 18px rgba(220, 38, 38, 0.4);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-create-dish:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 22px rgba(220, 38, 38, 0.6);
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    }

    /* Filtros y Buscador */
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .category-pills {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.25rem;
    }

    .cat-pill {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .cat-pill.active {
        background: rgba(245, 158, 11, 0.2);
        border-color: #F59E0B;
        color: #FEF08A;
    }

    .search-box {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        padding: 0.55rem 1rem;
        color: #FFFFFF;
        font-size: 0.88rem;
        width: 260px;
        outline: none;
    }

    .search-box:focus {
        border-color: #F59E0B;
    }

    /* Grid de Platos */
    .dishes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }

    .dish-card {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s, border-color 0.2s;
        position: relative;
    }

    .dish-card:hover {
        transform: translateY(-3px);
        border-color: rgba(245, 158, 11, 0.4);
    }

    .dish-card.unavailable {
        opacity: 0.65;
        border-style: dashed;
    }

    .dish-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }

    .dish-cat-badge {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: rgba(220, 38, 38, 0.2);
        color: #FCA5A5;
        border: 1px solid rgba(220, 38, 38, 0.4);
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
    }

    .dish-name {
        font-size: 1.15rem;
        font-weight: 800;
        color: #FFFFFF;
        margin-bottom: 0.35rem;
    }

    .dish-desc {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.35;
        margin-bottom: 1rem;
    }

    .dish-price-row {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .dish-price {
        font-size: 1.3rem;
        font-weight: 900;
        color: #FEF08A;
    }

    .dish-tax-badge {
        font-size: 0.72rem;
        font-weight: 700;
        color: #94A3B8;
        background: rgba(255, 255, 255, 0.05);
        padding: 0.15rem 0.45rem;
        border-radius: 4px;
    }

    /* Receta vinculada */
    .dish-recipe-box {
        background: var(--surface-2);
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 1rem;
        font-size: 0.78rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .dish-recipe-title {
        font-weight: 800;
        color: #F59E0B;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .dish-recipe-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .dish-recipe-item {
        display: flex;
        justify-content: space-between;
        color: var(--text-muted);
    }

    .dish-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border-subtle);
        padding-top: 0.85rem;
        margin-top: auto;
    }

    .status-pill {
        font-size: 0.78rem;
        font-weight: 800;
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .status-pill.available {
        background: rgba(16, 185, 129, 0.15);
        color: #6EE7B7;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-pill.out-of-stock {
        background: rgba(239, 68, 68, 0.15);
        color: #FCA5A5;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .btn-toggle-switch {
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        color: var(--text-main);
        padding: 0.45rem 0.85rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-toggle-switch:hover {
        background: var(--surface-3);
        border-color: #F59E0B;
    }

    /* Modal Formulario Plato */
    .dish-form-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        margin-bottom: 1rem;
    }

    .dish-form-group label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
    }

    .dish-input, .dish-select, .dish-textarea {
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
        color: #FFFFFF;
        font-size: 0.88rem;
        outline: none;
    }

    .dish-input:focus, .dish-select:focus, .dish-textarea:focus {
        border-color: #F59E0B;
    }

    .recipe-builder-section {
        background: var(--surface-2);
        border-radius: 12px;
        padding: 1rem;
        margin: 1.25rem 0;
        border: 1px solid var(--border-subtle);
    }

    .recipe-row {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .btn-del-recipe {
        background: rgba(239, 68, 68, 0.2);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #FCA5A5;
        border-radius: 6px;
        padding: 0.4rem 0.6rem;
        cursor: pointer;
        font-weight: 800;
    }
</style>
@endsection

@section('content')
<div class="admin-header">
    <div class="admin-title-wrap">
        <h1>🍗 Catálogo & Recetario Big Pollo</h1>
        <p>Gestione el menú, disponibilidad en tiempo real y la fórmula de inventario para descuento automático de stock.</p>
    </div>
    <button class="btn-create-dish" onclick="openCreateDishModal()">
        <span>➕</span> Nuevo Plato & Receta
    </button>
</div>

<!-- Filtros de Categorías y Búsqueda -->
<div class="filter-bar">
    <div class="category-pills" id="categoryPills">
        <button class="cat-pill active" onclick="filterCategory(null)">Todos los Platos</button>
    </div>
    <input type="text" id="searchBox" class="search-box" placeholder="🔍 Buscar plato por nombre..." oninput="handleSearch()">
</div>

<!-- Grid de Platos -->
<div class="dishes-grid" id="dishesGrid">
    <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted);">
        ⏳ Cargando catálogo y recetas de Big Pollo...
    </div>
</div>

<!-- Modal para Crear Nuevo Plato y su Receta -->
<div class="modal-overlay" id="createDishModal">
    <div class="modal-tutorial-card" style="max-width: 650px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #FFFFFF; display: flex; align-items: center; gap: 0.5rem;">
                <span>🍗</span> Registrar Nuevo Plato Big Pollo
            </h2>
            <button onclick="closeCreateDishModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>

        <form id="dishForm" onsubmit="handleCreateDish(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="dish-form-group">
                    <label>Nombre del Plato *</label>
                    <input type="text" id="dishName" class="dish-input" placeholder="ej. Pollo Broaster 1/4" required>
                </div>
                <div class="dish-form-group">
                    <label>Categoría *</label>
                    <select id="dishCategory" class="dish-select" required>
                        <option value="">Seleccione Categoría...</option>
                    </select>
                </div>
            </div>

            <div class="dish-form-group">
                <label>Descripción del Plato</label>
                <input type="text" id="dishDesc" class="dish-input" placeholder="ej. Porción de pollo crocante con papas a la francesa y arepa">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="dish-form-group">
                    <label>Precio de Venta (COP) *</label>
                    <input type="number" id="dishPrice" class="dish-input" placeholder="ej. 16000" min="0" step="100" required>
                </div>
                <div class="dish-form-group">
                    <label>Impoconsumo (DIAN) %</label>
                    <input type="number" id="dishTax" class="dish-input" value="8" min="0" max="100" step="0.1">
                </div>
            </div>

            <!-- Recetario de Inventario -->
            <div class="recipe-builder-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-weight: 800; font-size: 0.9rem; color: #F59E0B;">📦 Receta de Inventario (Deducción Automática)</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Los insumos seleccionados se restarán del inventario cada vez que se confirme un pedido.</div>
                    </div>
                    <button type="button" class="btn-toggle-switch" onclick="addRecipeRow()">
                        + Agregar Insumo
                    </button>
                </div>

                <div id="recipeRowsContainer">
                    <!-- Filas dinámicas de ingredientes -->
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn-toggle-switch" onclick="closeCreateDishModal()">Cancelar</button>
                <button type="submit" id="btnSubmitDish" class="btn-create-dish">
                    <span>💾</span> Guardar Plato y Receta
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let allProducts = [];
    let allCategories = [];
    let allIngredients = [];
    let activeCategoryFilter = null;
    let searchTerm = '';

    document.addEventListener('DOMContentLoaded', () => {
        loadData();
    });

    async function loadData() {
        try {
            const [productsRes, ingredientsRes] = await Promise.all([
                fetch('/api/admin/products'),
                fetch('/api/admin/ingredients')
            ]);

            const productsData = await productsRes.json();
            const ingredientsData = await ingredientsRes.json();

            allProducts = productsData.data.products || [];
            allCategories = productsData.data.categories || [];
            allIngredients = ingredientsData.data || [];

            renderCategoryPills();
            populateCategorySelect();
            renderDishes();
        } catch (error) {
            console.error('Error cargando catálogo:', error);
            showToast('Error cargando los datos del catálogo', 'error');
        }
    }

    function renderCategoryPills() {
        const container = document.getElementById('categoryPills');
        container.innerHTML = `
            <button class="cat-pill ${activeCategoryFilter === null ? 'active' : ''}" onclick="filterCategory(null)">
                Todos los Platos (${allProducts.length})
            </button>
        `;

        allCategories.forEach(cat => {
            const count = allProducts.filter(p => p.category_id === cat.id).length;
            const btn = document.createElement('button');
            btn.className = `cat-pill ${activeCategoryFilter === cat.id ? 'active' : ''}`;
            btn.innerText = `${cat.name} (${count})`;
            btn.onclick = () => filterCategory(cat.id);
            container.appendChild(btn);
        });
    }

    function populateCategorySelect() {
        const select = document.getElementById('dishCategory');
        select.innerHTML = '<option value="">Seleccione Categoría...</option>';
        allCategories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            select.appendChild(opt);
        });
    }

    function filterCategory(catId) {
        activeCategoryFilter = catId;
        renderCategoryPills();
        renderDishes();
    }

    function handleSearch() {
        searchTerm = document.getElementById('searchBox').value.toLowerCase().trim();
        renderDishes();
    }

    function renderDishes() {
        const grid = document.getElementById('dishesGrid');
        let filtered = allProducts;

        if (activeCategoryFilter !== null) {
            filtered = filtered.filter(p => p.category_id === activeCategoryFilter);
        }

        if (searchTerm) {
            filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm) || (p.description && p.description.toLowerCase().includes(searchTerm)));
        }

        if (filtered.length === 0) {
            grid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted);">
                    🍗 No se encontraron platos con los filtros actuales.
                </div>
            `;
            return;
        }

        grid.innerHTML = filtered.map(product => {
            const catName = product.category ? product.category.name : 'Plato Big Pollo';
            const priceFmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(product.price);
            const isAvail = product.is_available;

            const ingredientsList = (product.ingredients && product.ingredients.length > 0)
                ? product.ingredients.map(ing => `
                    <li class="dish-recipe-item">
                        <span>• ${ing.name}</span>
                        <span><b>${ing.pivot.quantity_required}</b> ${ing.unit}</span>
                    </li>
                `).join('')
                : '<li style="color: var(--text-dim);">Sin ingredientes de inventario vinculados</li>';

            return `
                <div class="dish-card ${!isAvail ? 'unavailable' : ''}">
                    <div>
                        <div class="dish-top">
                            <span class="dish-cat-badge">${catName}</span>
                            <span class="status-pill ${isAvail ? 'available' : 'out-of-stock'}">
                                ${isAvail ? '🟢 Disponible' : '🔴 Agotado'}
                            </span>
                        </div>
                        <div class="dish-name">${product.name}</div>
                        <div class="dish-desc">${product.description || 'Deliciosa especialidad de la casa preparada al instante.'}</div>
                        
                        <div class="dish-price-row">
                            <span class="dish-price">${priceFmt}</span>
                            <span class="dish-tax-badge">Impoconsumo ${product.tax_percentage || 8}%</span>
                        </div>

                        <div class="dish-recipe-box">
                            <div class="dish-recipe-title">
                                <span>📦</span> Receta de Insumos:
                            </div>
                            <ul class="dish-recipe-list">
                                ${ingredientsList}
                            </ul>
                        </div>
                    </div>

                    <div class="dish-actions">
                        <span style="font-size: 0.78rem; color: var(--text-muted);">
                            Estado de Venta
                        </span>
                        <button class="btn-toggle-switch" onclick="toggleProduct(${product.id})">
                            ${isAvail ? 'Desactivar (Agotar)' : 'Activar (Disponible)'}
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function toggleProduct(productId) {
        try {
            const res = await fetch(`/api/admin/products/${productId}/toggle`, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
            });

            const data = await res.json();
            if (res.ok) {
                const prod = allProducts.find(p => p.id === productId);
                if (prod) {
                    prod.is_available = data.is_available;
                    renderDishes();
                    showToast(`Disponibilidad de "${prod.name}" actualizada a: ${data.is_available ? 'Disponible' : 'Agotado'}`, 'success');
                }
            } else {
                showToast(data.message || 'Error al actualizar disponibilidad', 'error');
            }
        } catch (error) {
            console.error('Error toggling product:', error);
            showToast('Error de conexión al cambiar disponibilidad', 'error');
        }
    }

    function openCreateDishModal() {
        document.getElementById('createDishModal').style.display = 'flex';
        document.getElementById('dishForm').reset();
        document.getElementById('dishTax').value = '8';
        document.getElementById('recipeRowsContainer').innerHTML = '';
        addRecipeRow(); // agregar 1 fila de insumo por defecto
    }

    function closeCreateDishModal() {
        document.getElementById('createDishModal').style.display = 'none';
    }

    function addRecipeRow() {
        const container = document.getElementById('recipeRowsContainer');
        const rowId = 'rec_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);

        let options = allIngredients.map(ing => 
            `<option value="${ing.id}">${ing.name} (${ing.unit})</option>`
        ).join('');

        const row = document.createElement('div');
        row.className = 'recipe-row';
        row.id = rowId;
        row.innerHTML = `
            <select class="dish-select rec-ing" required>
                <option value="">Seleccione Insumo...</option>
                ${options}
            </select>
            <input type="number" class="dish-input rec-qty" placeholder="Cant. ej. 0.5" step="0.01" min="0.01" required>
            <button type="button" class="btn-del-recipe" onclick="document.getElementById('${rowId}').remove()">✕</button>
        `;
        container.appendChild(row);
    }

    async function handleCreateDish(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btnSubmitDish');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Guardando plato...';

        try {
            const name = document.getElementById('dishName').value.trim();
            const category_id = parseInt(document.getElementById('dishCategory').value);
            const description = document.getElementById('dishDesc').value.trim();
            const price = parseFloat(document.getElementById('dishPrice').value);
            const tax_percentage = parseFloat(document.getElementById('dishTax').value) || 8.0;

            const recipeRows = document.querySelectorAll('#recipeRowsContainer .recipe-row');
            const ingredients = [];

            recipeRows.forEach(row => {
                const ingSelect = row.querySelector('.rec-ing');
                const qtyInput = row.querySelector('.rec-qty');
                if (ingSelect.value && qtyInput.value) {
                    ingredients.push({
                        ingredient_id: parseInt(ingSelect.value),
                        quantity_required: parseFloat(qtyInput.value)
                    });
                }
            });

            const payload = {
                name,
                category_id,
                description,
                price,
                tax_percentage,
                is_available: true,
                ingredients
            };

            const res = await fetch('/api/admin/products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok) {
                showToast(`¡Plato "${data.product.name}" creado con éxito!`, 'success');
                closeCreateDishModal();
                await loadData();
            } else {
                showToast(data.message || 'Error al validar datos del plato', 'error');
            }
        } catch (error) {
            console.error('Error guardando plato:', error);
            showToast('Error de conexión al registrar el plato', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>💾</span> Guardar Plato y Receta';
        }
    }
</script>
@endsection
