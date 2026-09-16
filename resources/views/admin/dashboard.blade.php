@extends('layouts.app')

@section('title', 'Dashboard del Dueño — Big Pollo')

@section('styles')
    <link rel="stylesheet" href="/css/dashboard.css">
@endsection

@section('content')
<div class="dashboard-header">
    <div class="dashboard-title-box">
        <h2>
            <span>📊</span> Panel Ejecutivo del Dueño
        </h2>
        <div class="dashboard-subtitle">
            Métricas de ventas en tiempo real, rotación de platos, facturación DIAN y control de inventario.
        </div>
    </div>
    <div class="dashboard-date-badge">
        <span>📅</span> Hoy: {{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM, YYYY') }}
    </div>
</div>

<!-- 1. Grilla de Métricas Principales (KPI Cards) -->
<div class="kpi-grid">
    <div class="kpi-card sales">
        <div class="kpi-top-row">
            <span class="kpi-label">Ventas Brutas Hoy</span>
            <span class="kpi-icon">💰</span>
        </div>
        <div class="kpi-value">$ {{ number_format($metrics['total_sales_today'], 0, ',', '.') }}</div>
        <div class="kpi-subtext">Total de {{ $metrics['orders_count_today'] }} comandas liquidadas hoy</div>
    </div>

    <div class="kpi-card ticket">
        <div class="kpi-top-row">
            <span class="kpi-label">Ticket Promedio</span>
            <span class="kpi-icon">🎟️</span>
        </div>
        <div class="kpi-value">$ {{ number_format($metrics['average_ticket'], 0, ',', '.') }}</div>
        <div class="kpi-subtext">Gasto promedio por mesa atendida</div>
    </div>

    <div class="kpi-card occupancy">
        <div class="kpi-top-row">
            <span class="kpi-label">Ocupación del Salón</span>
            <span class="kpi-icon">🍽️</span>
        </div>
        <div class="kpi-value">{{ $metrics['occupancy_rate'] }}%</div>
        <div class="kpi-subtext">{{ $metrics['occupied_tables'] }} de {{ $metrics['total_tables'] }} mesas activas ahora</div>
    </div>

    <div class="kpi-card dian">
        <div class="kpi-top-row">
            <span class="kpi-label">Facturado DIAN (Factus)</span>
            <span class="kpi-icon">🏛️</span>
        </div>
        <div class="kpi-value">$ {{ number_format($metrics['dian_sales_total'], 0, ',', '.') }}</div>
        <div class="kpi-subtext">{{ $metrics['dian_invoices_count'] }} facturas electrónicas timbradas</div>
    </div>
</div>

<!-- 2. Dos Columnas: Medios de Pago & Top Platos -->
<div class="dashboard-two-cols">
    <!-- Columna Izquierda: Ingresos por Medio de Pago -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <span>💳</span> Ingresos por Medio de Pago
            </h3>
            <span class="kpi-subtext">Caja Actual: {{ $metrics['active_shift'] ? '🟢 Turno Abierto' : '🔴 Caja Cerrada' }}</span>
        </div>

        @php
            $tot = $metrics['total_sales_today'] > 0 ? $metrics['total_sales_today'] : 1;
            $cashPct = round(($metrics['cash_sales'] / $tot) * 100);
            $cardPct = round(($metrics['card_sales'] / $tot) * 100);
            $trfPct = round(($metrics['transfer_sales'] / $tot) * 100);
        @endphp

        <div class="payment-breakdown-list">
            <div class="payment-row">
                <div class="payment-row-header">
                    <span>💵 Efectivo en Caja</span>
                    <span style="color: #6EE7B7;">$ {{ number_format($metrics['cash_sales'], 0, ',', '.') }} ({{ $cashPct }}%)</span>
                </div>
                <div class="payment-progress-bg">
                    <div class="payment-progress-bar bar-cash" style="width: {{ $cashPct }}%;"></div>
                </div>
            </div>

            <div class="payment-row">
                <div class="payment-row-header">
                    <span>💳 Tarjeta Débito / Crédito</span>
                    <span style="color: #A5B4FC;">$ {{ number_format($metrics['card_sales'], 0, ',', '.') }} ({{ $cardPct }}%)</span>
                </div>
                <div class="payment-progress-bg">
                    <div class="payment-progress-bar bar-card" style="width: {{ $cardPct }}%;"></div>
                </div>
            </div>

            <div class="payment-row">
                <div class="payment-row-header">
                    <span>📱 Transferencia (Nequi / Daviplata)</span>
                    <span style="color: #FDE047;">$ {{ number_format($metrics['transfer_sales'], 0, ',', '.') }} ({{ $trfPct }}%)</span>
                </div>
                <div class="payment-progress-bg">
                    <div class="payment-progress-bar bar-transfer" style="width: {{ $trfPct }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Ranking Top Platos Más Vendidos -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <span>🍗</span> Platos Más Vendidos Hoy
            </h3>
            <a href="/admin/menu" style="font-size: 0.78rem; color: #FEF08A; text-decoration: none; font-weight: 700;">Ver Catálogo Completo ➔</a>
        </div>

        <div class="top-products-list">
            @forelse($metrics['top_products'] as $idx => $prod)
                <div class="top-product-item">
                    <div class="rank-badge">#{{ $idx + 1 }}</div>
                    <div class="top-prod-info">
                        <div class="top-prod-name">{{ $prod->name }}</div>
                        <div class="top-prod-sub">Generado: $ {{ number_format($prod->total_revenue, 0, ',', '.') }} COP</div>
                    </div>
                    <div class="top-prod-stats">
                        <div class="top-prod-qty">{{ $prod->total_quantity }} u</div>
                        <div class="top-prod-rev">Vendidas</div>
                    </div>
                </div>
            @empty
                <div class="cart-empty-state">
                    No se han registrado ventas de platos hoy.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- 3. Alertas de Inventario Crítico (Bajo Stock) -->
<div class="dashboard-panel" style="margin-bottom: 2rem;">
    <div class="panel-header">
        <h3 class="panel-title">
            <span>⚠️</span> Insumos de Inventario en Alerta (Materia Prima)
        </h3>
        <span class="kpi-subtext">Insumos por debajo o iguales al stock mínimo requerido</span>
    </div>

    @if($metrics['low_stock_ingredients']->count() > 0)
        <div class="inventory-alerts-grid">
            @foreach($metrics['low_stock_ingredients'] as $ing)
                <div class="inventory-alert-card">
                    <span class="inventory-alert-icon">🔴</span>
                    <div>
                        <div class="inventory-alert-name">{{ $ing->name }}</div>
                        <div class="inventory-alert-stock">
                            Stock Actual: <b>{{ $ing->current_stock }} {{ $ing->unit }}</b> (Mínimo: {{ $ing->minimum_stock }} {{ $ing->unit }})
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="inventory-ok-state">
            ✅ ¡Inventario en óptimo estado! Todos los insumos de cocina cuentan con stock superior al mínimo.
        </div>
    @endif
</div>
@endsection
