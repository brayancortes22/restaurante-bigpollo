@extends('layouts.app')

@section('title', 'Editor de Plano del Salón — Admin Big Pollo')

@section('styles')
    <link rel="stylesheet" href="/css/floor_plan.css?v={{ time() }}">
@endsection

@section('content')
<div class="fp-layout">

    <!-- ── Barra de Herramientas ── -->
    <aside class="fp-toolbar">
        <div class="fp-toolbar-brand">
            <span style="font-size:1.5rem;">🗺️</span>
            <div>
                <div class="fp-toolbar-title">Editor de Plano</div>
                <div class="fp-toolbar-sub">Administrador Big Pollo</div>
            </div>
        </div>

        <div class="fp-tool-section">
            <div class="fp-tool-label">📐 Nueva Mesa</div>
            <div class="fp-form-group">
                <label class="fp-form-lbl">Número / Nombre</label>
                <input type="text" id="newTableNumber" class="fp-input" placeholder="Ej: Mesa 20, Terraza 1">
            </div>
            <div class="fp-form-group">
                <label class="fp-form-lbl">Capacidad</label>
                <select id="newTableCapacity" class="fp-input">
                    <option value="2">2 personas</option>
                    <option value="4" selected>4 personas</option>
                    <option value="6">6 personas</option>
                    <option value="8">8 personas</option>
                    <option value="12">12 personas</option>
                </select>
            </div>
            <div class="fp-form-group">
                <label class="fp-form-lbl">Forma</label>
                <div class="fp-shape-selector">
                    <button type="button" class="fp-shape-btn active" data-shape="square" onclick="selectShape('square')">
                        <span class="shape-preview shape-square"></span> Cuadrada
                    </button>
                    <button type="button" class="fp-shape-btn" data-shape="round" onclick="selectShape('round')">
                        <span class="shape-preview shape-round"></span> Redonda
                    </button>
                    <button type="button" class="fp-shape-btn" data-shape="rect" onclick="selectShape('rect')">
                        <span class="shape-preview shape-rect"></span> Rectangular
                    </button>
                </div>
            </div>
            <div class="fp-form-group">
                <label class="fp-form-lbl">Zona</label>
                <select id="newTableZone" class="fp-input">
                    <option value="calle">Salón Calle</option>
                    <option value="principal">Salón Principal</option>
                    <option value="mostrador">Mostrador</option>
                </select>
            </div>
            <button type="button" class="fp-btn-add" onclick="addNewTable()">
                ➕ Agregar Mesa al Plano
            </button>
        </div>

        <div class="fp-tool-section">
            <div class="fp-tool-label">💾 Acciones</div>
            <button type="button" class="fp-btn-save" id="fpBtnSave" onclick="saveLayout()">
                💾 Guardar Distribución
            </button>
            <button type="button" class="fp-btn-reset" onclick="confirmReset()">
                🔄 Restaurar Plano Original
            </button>
        </div>

        <div class="fp-tool-section">
            <div class="fp-tool-label">ℹ️ Instrucciones</div>
            <ul class="fp-tips">
                <li>🖱️ Arrastra mesas libremente sobre el lienzo</li>
                <li>🗑️ Clic sobre una mesa para opciones de edición</li>
                <li>📌 Snap automático a cuadrícula opcional</li>
                <li>💾 Guarda para que el mesero vea los cambios</li>
            </ul>
        </div>
    </aside>

    <!-- ── Lienzo Principal ── -->
    <main class="fp-canvas-area">
        <div class="fp-canvas-header">
            <h1 class="fp-canvas-title">🏠 Plano del Salón — Big Pollo</h1>
            <div class="fp-canvas-actions">
                <label class="fp-snap-toggle">
                    <input type="checkbox" id="snapToggle" checked onchange="toggleSnap(this.checked)">
                    <span>Snap a cuadrícula</span>
                </label>
                <span id="fpStatusBadge" class="fp-status-badge fp-status-saved">✅ Guardado</span>
            </div>
        </div>

        <!-- Lienzo con cuadrícula -->
        <div id="fpCanvas" class="fp-canvas">
            <!-- Zonas decorativas -->
            <div class="fp-zone fp-zone-calle"  style="left:0;top:0;width:62%;height:48%;">
                <span class="fp-zone-label">Salón Frente a la Calle</span>
            </div>
            <div class="fp-zone fp-zone-principal" style="left:0;top:50%;width:52%;height:48%;">
                <span class="fp-zone-label">Salón Principal</span>
            </div>
            <div class="fp-zone fp-zone-kitchen" style="left:63%;top:50%;width:37%;height:48%;">
                <span class="fp-zone-label">🍗 Rotisería / Asador</span>
            </div>
            <div class="fp-zone fp-zone-cash" style="left:63%;top:0;width:37%;height:48%;">
                <span class="fp-zone-label">💳 Zona Caja</span>
            </div>

            <!-- Estación Mostrador (decorativa) -->
            <div class="fp-takeout-deco" style="left:65%;top:52%;">
                🥡 Mostrador
            </div>

            <!-- Mesas (generadas por JS) -->
            <div id="fpTablesLayer"></div>
        </div>

        <div class="fp-canvas-footer">
            <span id="fpTableCount" class="fp-table-count">0 mesas en el plano</span>
            <span class="fp-hint">Las mesas en naranja/rojo tienen comandas activas y no pueden eliminarse.</span>
        </div>
    </main>
</div>

<!-- Modal: Editar / Eliminar Mesa seleccionada -->
<div class="fp-modal-backdrop" id="fpTableModal">
    <div class="fp-modal">
        <div class="fp-modal-header">
            <h3 id="fpModalTitle">🪑 Mesa Seleccionada</h3>
            <button type="button" class="fp-modal-close" onclick="closeFpModal()">&times;</button>
        </div>
        <div class="fp-modal-body">
            <div class="fp-form-group">
                <label class="fp-form-lbl">Número / Nombre</label>
                <input type="text" id="editTableNumber" class="fp-input">
            </div>
            <div class="fp-form-group">
                <label class="fp-form-lbl">Capacidad</label>
                <select id="editTableCapacity" class="fp-input">
                    <option value="2">2 personas</option>
                    <option value="4">4 personas</option>
                    <option value="6">6 personas</option>
                    <option value="8">8 personas</option>
                    <option value="12">12 personas</option>
                </select>
            </div>
            <div class="fp-form-group">
                <label class="fp-form-lbl">Zona</label>
                <select id="editTableZone" class="fp-input">
                    <option value="calle">Salón Calle</option>
                    <option value="principal">Salón Principal</option>
                    <option value="mostrador">Mostrador</option>
                </select>
            </div>
        </div>
        <div class="fp-modal-footer">
            <button type="button" class="fp-btn-save" style="flex:1;" onclick="applyTableEdit()">✅ Aplicar Cambios</button>
            <button type="button" class="fp-btn-delete" id="fpBtnDelete" onclick="deleteSelectedTable()">🗑️ Eliminar</button>
            <button type="button" class="fp-btn-cancel" onclick="closeFpModal()">✕ Cancelar</button>
        </div>
    </div>
</div>

<!-- Modal de confirmación de reset -->
<div class="fp-modal-backdrop" id="fpResetModal">
    <div class="fp-modal" style="max-width:420px;">
        <div class="fp-modal-header">
            <h3>🔄 Restaurar Plano</h3>
            <button type="button" class="fp-modal-close" onclick="closeResetModal()">&times;</button>
        </div>
        <div class="fp-modal-body">
            <p style="color:#94A3B8;line-height:1.6;">
                ¿Deseas restaurar la distribución original del plano arquitectónico de Big Pollo
                (19 mesas, posiciones predeterminadas)? Esta acción sobreescribirá las posiciones actuales.
            </p>
        </div>
        <div class="fp-modal-footer">
            <button type="button" class="fp-btn-delete" style="flex:1;" onclick="executeReset()">🔄 Sí, Restaurar</button>
            <button type="button" class="fp-btn-cancel" onclick="closeResetModal()">✕ Cancelar</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/js/floor_plan.js?v={{ time() }}"></script>
@endsection
