/**
 * Big Pollo - Guided Interactive Tour Engine (Driver.js)
 * Estilo Accesorios Lilís con la Mascota Oficial Big Pollo 👍
 */

function renderBigPolloHeader(title, badge = '🍗 Tu Asesor Big Pollo') {
    return `
        <div class="bp-driver-header">
            <img src="/images/big_pollo_avatar.png" alt="Big Pollo" class="bp-driver-avatar" />
            <div>
                <div class="bp-driver-title">${title}</div>
                <span class="bp-driver-role-tag">${badge}</span>
            </div>
        </div>
    `;
}

function startBigPolloTour() {
    if (typeof window.driver === 'undefined' || typeof window.driver.js === 'undefined') {
        if (typeof openTutorialModal === 'function') {
            openTutorialModal();
        }
        return;
    }

    const driverObj = window.driver.js.driver({
        showProgress: true,
        animate: true,
        popoverClass: 'bp-driver-popover',
        nextBtnText: 'Siguiente ➔',
        prevBtnText: '⬅ Anterior',
        doneBtnText: '¡Entendido, a Vender! 🍗',
        allowClose: true,
    });

    const path = window.location.pathname;
    let steps = [];

    if (path.includes('/waiter') || path === '/') {
        steps = [
            {
                element: '.brand-badge',
                popover: {
                    title: renderBigPolloHeader('¡Bienvenido al Comandero Big Pollo! 🍗', '🍗 Tu Asesor Big Pollo'),
                    description: `
                        <div class="bp-driver-body">
                            <p>¡Hola! Soy tu asistente de <strong>Big Pollo</strong>. Te enseñaré en segundos cómo tomar pedidos, mover mesas y atender a los clientes en hora pico.</p>
                            <p style="margin-top: 6px; font-size: 0.78rem; color: #FEF08A;">💡 Usa los botones <strong>Siguiente ➔</strong> para avanzar paso a paso.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '.tables-section',
                popover: {
                    title: renderBigPolloHeader('1. Salón de Mesas en Vivo 🍽️', 'Mapeo de Mesas'),
                    description: `
                        <div class="bp-driver-body">
                            <p>• 🟢 <strong>Mesa Verde (Disponible):</strong> Tócala para sentar comensales e iniciar un pedido.</p>
                            <p>• 🟠 <strong>Mesa Naranja (Ocupada):</strong> Tócala para <strong>ver qué pidieron</strong>, adicionar más platos a la cuenta o cambiarlos de mesa.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '.category-tabs',
                popover: {
                    title: renderBigPolloHeader('2. Categorías del Menú 🍗', 'Filtro Rápido'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Filtra instantáneamente entre <strong>Pollo Asado al Carbón</strong>, <strong>Pollo Broaster Crocante</strong>, Acompañamientos y Bebidas heladas.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '.products-grid',
                popover: {
                    title: renderBigPolloHeader('3. Selección Táctil de Platos 🛒', 'Catálogo'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Usa los botones grandes <strong>(+)</strong> y <strong>(-)</strong> para agregar combos y porciones. Cada plato descuenta automáticamente las presas y papas del inventario.</p>
                        </div>
                    `,
                    side: 'top',
                    align: 'start'
                }
            },
            {
                element: '.cart-section',
                popover: {
                    title: renderBigPolloHeader('4. Carrito & Envío a Cocina 🚀', 'Comanda'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Revisa el subtotal y el <strong>8% de Impoconsumo</strong> legal. Al tocar <strong>"Enviar a Cocina"</strong> la comanda vuela a la pantalla KDS.</p>
                            <p style="margin-top: 6px; color: #6EE7B7;">🔔 Cuando la cocina termine, sonará una <strong>campanilla</strong> avisándote que el pollo está caliente y listo para servir.</p>
                        </div>
                    `,
                    side: 'left',
                    align: 'center'
                }
            }
        ];
    } else if (path.includes('/kds')) {
        steps = [
            {
                element: '.kds-header',
                popover: {
                    title: renderBigPolloHeader('Cocina KDS en Tiempo Real 👨‍🍳', 'Jefe de Cocina'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Esta pantalla organiza el asador y las freidoras en horas pico sin usar papel. Se actualiza sola cada 4 segundos.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '.orders-kds-grid',
                popover: {
                    title: renderBigPolloHeader('Semáforo de Retraso de Cocina ⏱️', 'SLA de Cocina'),
                    description: `
                        <div class="bp-driver-body">
                            <p>• 🟢 <strong>Verde (&lt; 10 min):</strong> Tiempo óptimo.</p>
                            <p>• 🟡 <strong>Amarillo (10-20 min):</strong> Apurar preparación.</p>
                            <p>• 🔴 <strong>Rojo Parpadeante (&gt; 20 min):</strong> ¡Urgente! Alerta en comanderos y caja.</p>
                            <p>Toca <strong>"¡Plato Listo!"</strong> para hacer sonar la campanilla del mesero.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'start'
                }
            }
        ];
    } else if (path.includes('/pos')) {
        steps = [
            {
                element: '.shift-status-card',
                popover: {
                    title: renderBigPolloHeader('Turno de Caja & Arqueo Z 💳', 'Caja POS'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Abre tu turno con la base en efectivo. Al final del día, el sistema totaliza efectivo, tarjetas y transferencias (Nequi/Daviplata) calculando el arqueo exacto.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '#ordersToPayContainer',
                popover: {
                    title: renderBigPolloHeader('Cobro y Facturación DIAN 🏛️', 'Factus API'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Selecciona la mesa a pagar. Con 1 clic emites la <strong>Factura Electrónica DIAN</strong> con CUFE criptográfico y código QR oficial.</p>
                        </div>
                    `,
                    side: 'top',
                    align: 'start'
                }
            }
        ];
    } else if (path.includes('/admin/menu')) {
        steps = [
            {
                element: '.btn-create-dish',
                popover: {
                    title: renderBigPolloHeader('Crear Nuevos Platos y Recetas 🍗', 'Administración'),
                    description: `
                        <div class="bp-driver-body">
                            <p>Agrega cualquier plato definiendo precio COP, categoría e insumos requeridos (Pollo Crudo, Papas, Aceite) para descuento automático de stock.</p>
                        </div>
                    `,
                    side: 'bottom',
                    align: 'end'
                }
            }
        ];
    }

    if (steps.length > 0) {
        driverObj.setSteps(steps);
        driverObj.drive();
    } else {
        if (typeof openTutorialModal === 'function') openTutorialModal();
    }
}
