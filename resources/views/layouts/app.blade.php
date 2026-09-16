<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurante Big Pollo') - Cloud POS & KDS</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #0B0F17;
            --surface-1: #151D2C;
            --surface-2: #1E293B;
            --surface-3: #334155;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.16);
            --primary: #6366F1;
            --primary-hover: #4F46E5;
            --primary-glow: rgba(99, 102, 241, 0.35);
            --success: #10B981;
            --success-glow: rgba(16, 185, 129, 0.3);
            --warning: #F59E0B;
            --warning-glow: rgba(245, 158, 11, 0.3);
            --danger: #F43F5E;
            --danger-glow: rgba(244, 63, 94, 0.3);
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Glassmorphism Navigation */
        .navbar {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.85rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: inherit;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #F59E0B 0%, #EA580C 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        }

        .brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, #FFFFFF 0%, #E2E8F0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            font-size: 0.72rem;
            font-weight: 600;
            color: #F59E0B;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
        }

        .nav-link {
            padding: 0.55rem 1rem;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-muted);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            gap: 0.45rem;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link.active {
            color: #FFFFFF;
            background: var(--surface-2);
            border-color: var(--border-highlight);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        /* Pulse live indicator */
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 0 0 var(--success-glow);
            animation: pulse-ring 1.8s infinite;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 var(--success-glow);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Toast Container */
        #toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            min-width: 280px;
            max-width: 420px;
            padding: 1rem 1.25rem;
            background: var(--surface-2);
            border: 1px solid var(--border-highlight);
            border-radius: 12px;
            color: #FFFFFF;
            font-size: 0.88rem;
            font-weight: 500;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateY(20px);
            opacity: 0;
            animation: toast-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .toast.success { border-left: 4px solid var(--success); }
        .toast.error { border-left: 4px solid var(--danger); }
        .toast.info { border-left: 4px solid var(--primary); }

        @keyframes toast-in {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .main-container {
            flex: 1;
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Utility classes */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar">
        <a href="/" class="brand-badge">
            <div class="brand-icon">🍗</div>
            <div>
                <div class="brand-title">Restaurante Big Pollo</div>
                <div class="brand-subtitle">Cloud POS & KDS Pro</div>
            </div>
        </a>

        <ul class="nav-links">
            <li>
                <a href="/waiter" class="nav-link {{ request()->is('waiter') ? 'active' : '' }}">
                    <span>🍽️</span> Comandero Mesero
                </a>
            </li>
            <li>
                <a href="/kds" class="nav-link {{ request()->is('kds') ? 'active' : '' }}">
                    <span>👨‍🍳</span> Cocina KDS
                    <span class="pulse-dot"></span>
                </a>
            </li>
            <li>
                <a href="/pos" class="nav-link {{ request()->is('pos') ? 'active' : '' }}">
                    <span>💳</span> Caja & Factus DIAN
                </a>
            </li>
            <li>
                <a href="/privacy" class="nav-link {{ request()->is('privacy') ? 'active' : '' }}">
                    <span>🛡️</span> Habeas Data (SIC)
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-container">
        @yield('content')
    </main>

    <!-- ⚡ Simulador Flotante de Hora Pico -->
    <div id="sim-panel" style="position: fixed; bottom: 1.5rem; left: 1.5rem; z-index: 9999; font-family: 'Plus Jakarta Sans', sans-serif;">
        <div id="sim-card" style="background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(16px); border: 2px solid var(--primary); border-radius: 18px; padding: 1.25rem; width: 340px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); display: none; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="font-weight: 800; font-size: 0.95rem; color: #FFFFFF; display: flex; align-items: center; gap: 0.4rem;">
                    <span>⚡</span> Simulador de Hora Pico
                </div>
                <span class="pulse-dot"></span>
            </div>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.4;">
                Simula una avalancha de 5 mesas pidiendo combos simultáneamente, cocina KDS a máxima capacidad y cobro con Factura DIAN.
            </p>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <button id="sim-burst-btn" onclick="runPeakHourSimulation()" style="width: 100%; padding: 0.75rem; border-radius: 12px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #000; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 4px 12px var(--warning-glow);">
                    <span>🚀</span> Iniciar Ráfaga de Pedidos
                </button>
                <button id="sim-kds-btn" onclick="runKdsCookSimulation()" style="width: 100%; padding: 0.65rem; border-radius: 10px; background: var(--surface-2); border: 1px solid var(--border-highlight); color: #FFF; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                    <span>👨‍🍳</span> Despachar Toda la Cocina
                </button>
                <button id="sim-pos-btn" onclick="runPosCashSimulation()" style="width: 100%; padding: 0.65rem; border-radius: 10px; background: var(--surface-2); border: 1px solid var(--border-highlight); color: #FFF; font-weight: 700; font-size: 0.8rem; cursor: pointer;">
                    <span>💳</span> Cobrar y Facturar DIAN
                </button>
            </div>
            <div id="sim-status-log" style="margin-top: 0.75rem; font-size: 0.72rem; color: #10B981; font-weight: 600; min-height: 18px; text-align: center;">
                Listo para simular.
            </div>
        </div>

        <button id="sim-toggle-btn" onclick="toggleSimPanel()" style="padding: 0.75rem 1.25rem; border-radius: 30px; background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); color: #FFF; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 24px var(--primary-glow); transition: all 0.2s;">
            <span>⚡ Modo Simulación Hora Pico</span>
        </button>
    </div>

    <div id="toast-container"></div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? '✅' : (type === 'error' ? '❌' : 'ℹ️');
            toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
            
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'all 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function formatCOP(amount) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                maximumFractionDigits: 0
            }).format(amount);
        }

        // --- ⚡ Lógica de Simulación de Hora Pico ---
        function toggleSimPanel() {
            const card = document.getElementById('sim-card');
            card.style.display = card.style.display === 'none' ? 'block' : 'none';
        }

        function setSimLog(msg, color = '#10B981') {
            const el = document.getElementById('sim-status-log');
            if (el) {
                el.innerText = msg;
                el.style.color = color;
            }
        }

        async function runPeakHourSimulation() {
            setSimLog('🚀 Generando pedidos masivos...', '#F59E0B');
            showToast('⚡ Ráfaga de hora pico iniciada en 5 mesas...', 'info');

            try {
                // Obtener mesas y menú
                const tablesRes = await fetch('/api/tables');
                const tablesJson = await tablesRes.json();
                const menuRes = await fetch('/api/menu');
                const menuJson = await menuRes.json();

                const tables = tablesJson.data || [];
                const products = [];
                (menuJson.data || []).forEach(c => (c.products || []).forEach(p => products.push(p)));

                if (tables.length === 0 || products.length === 0) {
                    setSimLog('No hay mesas o productos disponibles.', '#F43F5E');
                    return;
                }

                const diners = ['Andrés Gómez', 'María Paula Rincón', 'Camilo Restrepo', 'Valentina Díaz', 'Felipe Caicedo'];

                for (let i = 0; i < Math.min(5, tables.length); i++) {
                    const table = tables[i];
                    const randomProd1 = products[i % products.length];
                    const randomProd2 = products[(i + 1) % products.length];

                    const payload = {
                        restaurant_table_id: table.id,
                        type: 'dine_in',
                        customer_name: diners[i],
                        customer_nit_cedula: `1098${i}23456`,
                        customer_consent: true,
                        items: [
                            { product_id: randomProd1.id, quantity: 2, notes: 'Bien crocante' },
                            { product_id: randomProd2.id, quantity: 1, notes: 'Salsa tártara extra' }
                        ]
                    };

                    const orderRes = await fetch('/api/orders', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (orderRes.ok) {
                        const json = await orderRes.json();
                        showToast(`Mesa ${table.table_number}: Comanda #${json.data.order_number} enviada`, 'success');
                    }
                    await new Promise(r => setTimeout(r, 600)); // Pausa visual
                }

                setSimLog('✅ 5 comandas enviadas a Cocina KDS.', '#10B981');
                showToast('¡Ráfaga completada! Revisa la pestaña Cocina KDS', 'success');

                // Si estamos en /waiter, refrescar mesas
                if (typeof loadTables === 'function') loadTables();
            } catch (e) {
                setSimLog('Error en la simulación.', '#F43F5E');
                console.error(e);
            }
        }

        async function runKdsCookSimulation() {
            setSimLog('👨‍🍳 Cocinando y despachando...', '#6366F1');
            showToast('Cocina acelerada: procesando todos los pedidos...', 'info');

            try {
                const res = await fetch('/api/orders');
                const json = await res.json();
                const orders = (json.data || []).filter(o => ['pending', 'in_kitchen'].includes(o.status));

                for (const ord of orders) {
                    // Pasar a in_kitchen
                    await fetch(`/api/orders/${ord.id}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ status: 'in_kitchen' })
                    });
                    await new Promise(r => setTimeout(r, 300));

                    // Pasar a ready
                    await fetch(`/api/orders/${ord.id}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ status: 'ready' })
                    });
                    showToast(`Comanda #${ord.order_number} ¡LISTA PARA SERVIR!`, 'success');
                    await new Promise(r => setTimeout(r, 300));
                }

                setSimLog('✅ Todos los pedidos listos en cocina.', '#10B981');
                if (typeof loadKdsOrders === 'function') loadKdsOrders();
            } catch (e) {
                setSimLog('Error en cocina.', '#F43F5E');
            }
        }

        async function runPosCashSimulation() {
            setSimLog('💳 Cobrando y timbrando con Factus DIAN...', '#F59E0B');
            showToast('Cobrando comandas y emitiendo factura electrónica...', 'info');

            try {
                // Asegurar turno de caja abierto
                await fetch('/api/cash-shifts/open', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ opening_amount: 250000 })
                }).catch(() => {});

                const res = await fetch('/api/orders');
                const json = await res.json();
                const orders = (json.data || []).filter(o => o.status !== 'paid' && o.status !== 'cancelled');

                for (const ord of orders) {
                    // 1. Cobrar
                    await fetch(`/api/orders/${ord.id}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ status: 'paid' })
                    });

                    // 2. Emitir Factus DIAN
                    const invRes = await fetch(`/api/orders/${ord.id}/invoice`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    showToast(`Comanda #${ord.order_number} pagada y Factura DIAN emitida`, 'success');
                    await new Promise(r => setTimeout(r, 400));
                }

                setSimLog('✅ Todas las comandas cobradas y timbradas.', '#10B981');
                if (typeof loadOrders === 'function') loadOrders();
                if (typeof loadShiftStatus === 'function') loadShiftStatus();
            } catch (e) {
                setSimLog('Error en cobro.', '#F43F5E');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
