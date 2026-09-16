<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurante Big Pollo') - Asado & Broaster Cloud POS</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Driver.js for Interactive Guided Spotlight Tour -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.8.0/dist/driver.css"/>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.8.0/dist/driver.js.iife.js"></script>
    <script src="/js/guided-tour.js" defer></script>
    
    <style>
        :root {
            /* Paleta Oficial Big Pollo: Rojo Broaster & Amarillo Asado */
            --bg-base: #0B0E14;
            --surface-1: #141923;
            --surface-2: #1C2332;
            --surface-3: #29344A;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(245, 158, 11, 0.25);
            
            --bp-red: #E52521;
            --bp-red-dark: #991B1B;
            --bp-red-glow: rgba(229, 37, 33, 0.45);
            
            --bp-gold: #FFD200;
            --bp-yellow: #FFC107;
            --bp-gold-glow: rgba(255, 210, 0, 0.4);

            --primary: var(--bp-red);
            --primary-hover: #B91C1C;
            --primary-glow: var(--bp-red-glow);

            --success: #10B981;
            --success-glow: rgba(16, 185, 129, 0.3);
            --warning: #F59E0B;
            --warning-glow: rgba(245, 158, 11, 0.3);
            --danger: #EF4444;
            --danger-glow: rgba(239, 68, 68, 0.3);

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

        /* Top Brand Header */
        .navbar {
            background: rgba(20, 25, 35, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 2px solid rgba(245, 158, 11, 0.3);
            padding: 0.75rem 1.5rem;
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
            gap: 0.85rem;
            text-decoration: none;
            color: inherit;
        }

        /* Emblema Oficial del Pollo con Pulgar Arriba 👍 */
        .brand-emblem {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: radial-gradient(circle, #FDE047 0%, #F59E0B 60%, #DC2626 100%);
            border: 2px solid #FEF08A;
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            position: relative;
            flex-shrink: 0;
            animation: bounce-subtle 3s infinite ease-in-out;
        }

        .brand-emblem-thumb {
            position: absolute;
            bottom: -3px;
            right: -3px;
            font-size: 0.95rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
        }

        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }

        .brand-texts {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1.1;
            background: linear-gradient(135deg, #FFFFFF 0%, #FEF08A 60%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(220, 38, 38, 0.2);
        }

        .brand-slogan-badge {
            font-size: 0.68rem;
            font-weight: 800;
            color: #FEF08A;
            background: linear-gradient(90deg, #DC2626, #991B1B);
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            border: 1px solid rgba(245, 158, 11, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            width: fit-content;
            margin-top: 2px;
        }

        /* Nav Links */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            list-style: none;
            flex-wrap: wrap;
        }

        .nav-link {
            padding: 0.55rem 0.95rem;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 700;
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
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.25) 0%, rgba(245, 158, 11, 0.2) 100%);
            border-color: rgba(245, 158, 11, 0.5);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
        }

        .tutorial-btn {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #000000 !important;
            font-weight: 800 !important;
            padding: 0.55rem 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .tutorial-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(245, 158, 11, 0.5);
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
            0% { box-shadow: 0 0 0 0 var(--success-glow); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .main-container {
            flex: 1;
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.6rem 0.85rem;
            }
            .brand-emblem {
                width: 38px;
                height: 38px;
                border-radius: 10px;
            }
            .brand-name {
                font-size: 1.15rem;
            }
            .brand-slogan-badge {
                font-size: 0.62rem;
                padding: 0.1rem 0.4rem;
            }
            .main-container {
                padding: 0.75rem 0.65rem;
            }
        }

        /* Executive Commercial Footer */
        .footer-commercial {
            background: #0D111A;
            border-top: 1px solid var(--border-subtle);
            padding: 2.5rem 1.5rem 1.5rem;
            margin-top: auto;
        }

        .footer-grid {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        .footer-col h4 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .footer-col p, .footer-col li {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .footer-col a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-col a:hover {
            color: #F59E0B;
        }

        .trust-badge {
            background: var(--surface-1);
            border: 1px solid var(--border-subtle);
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.5rem;
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            border-top: 1px solid var(--border-subtle);
            padding-top: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--text-dim);
            flex-wrap: wrap;
            gap: 0.75rem;
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
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: toast-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .toast.success { border-left: 4px solid var(--success); }
        .toast.error { border-left: 4px solid var(--danger); }
        .toast.info { border-left: 4px solid var(--bp-gold); }

        @keyframes toast-in {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 1.5rem;
        }

        .modal-tutorial-card {
            background: var(--surface-1);
            border: 2px solid rgba(245, 158, 11, 0.4);
            border-radius: 22px;
            padding: 2rem;
            width: 100%;
            max-width: 780px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
        }

        .role-tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border-subtle);
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
            overflow-x: auto;
        }

        .role-tab-btn {
            padding: 0.6rem 1.1rem;
            border-radius: 12px;
            background: var(--surface-2);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .role-tab-btn.active {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            color: #FFFFFF;
            border-color: #F59E0B;
        }

        .tutorial-step {
            background: var(--surface-2);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            border: 1px solid var(--border-subtle);
        }

        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #F59E0B;
            color: #000;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* User Auth Badge & Logout */
        .user-auth-badge {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            background: var(--surface-2);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 0.35rem 0.65rem 0.35rem 0.75rem;
            margin-left: 0.5rem;
        }

        .user-role-tag {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            background: rgba(245, 158, 11, 0.2);
            color: #FEF08A;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }

        .user-role-tag.role-admin {
            background: rgba(220, 38, 38, 0.25);
            color: #FCA5A5;
            border-color: rgba(220, 38, 38, 0.5);
        }

        .user-role-tag.role-cajero {
            background: rgba(16, 185, 129, 0.2);
            color: #6EE7B7;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .user-role-tag.role-cocina {
            background: rgba(245, 158, 11, 0.2);
            color: #FDE047;
            border-color: rgba(245, 158, 11, 0.5);
        }

        .user-name-text {
            font-size: 0.82rem;
            font-weight: 700;
            color: #FFFFFF;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #FCA5A5;
            padding: 0.35rem 0.65rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-logout:hover {
            background: #DC2626;
            color: #FFFFFF;
        }

        .btn-nav-login {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            color: #FFFFFF;
            border: 1px solid #F59E0B;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.84rem;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.45rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
            transition: all 0.2s;
        }

        .btn-nav-login:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        }

        /* Driver.js Big Pollo Custom Theme (Mascota Oficial 👍) */
        .driver-popover.bp-driver-popover {
            background: #141923 !important;
            color: #F8FAFC !important;
            border: 2px solid #FFD200 !important;
            border-radius: 18px !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.8), 0 0 20px rgba(229, 37, 33, 0.4) !important;
            padding: 1.25rem !important;
            max-width: 380px !important;
        }

        .bp-driver-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .bp-driver-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #E52521;
            border: 2px solid #FFD200;
            object-fit: cover;
            flex-shrink: 0;
        }

        .bp-driver-title {
            font-weight: 800;
            font-size: 0.98rem;
            color: #FFFFFF;
        }

        .bp-driver-role-tag {
            font-size: 0.68rem;
            color: #FEF08A;
            font-weight: 800;
            background: rgba(229, 37, 33, 0.3);
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
        }

        .bp-driver-body {
            font-size: 0.85rem;
            color: #CBD5E1;
            line-height: 1.5;
        }

        .driver-popover-footer button {
            background: #E52521 !important;
            color: #FFFFFF !important;
            border: 1px solid #FFD200 !important;
            border-radius: 8px !important;
            padding: 0.4rem 0.8rem !important;
            font-weight: 700 !important;
            font-size: 0.78rem !important;
            text-shadow: none !important;
        }

        .driver-popover-footer button:hover {
            background: #B91C1C !important;
        }

        /* Mobile Bottom App Bar (<= 768px) */
        .mobile-bottom-bar {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding-bottom: 72px; /* Espacio para barra táctil inferior */
            }

            .nav-links {
                display: none !important; /* En celular, la navegación va abajo para fácil uso con pulgares */
            }

            .mobile-bottom-bar {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 64px;
                background: rgba(20, 25, 35, 0.96);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-top: 2px solid rgba(255, 210, 0, 0.35);
                z-index: 9999;
                align-items: center;
                justify-content: space-around;
                padding: 0 0.5rem;
                box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.6);
            }

            .mobile-bottom-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                color: var(--text-muted);
                font-size: 0.68rem;
                font-weight: 700;
                gap: 2px;
                min-width: 58px;
                min-height: 48px; /* Touch target accesible (Fitts's Law) */
                border-radius: 10px;
                transition: all 0.2s;
                cursor: pointer;
                border: none;
                background: none;
            }

            .mobile-bottom-item.active {
                color: #FEF08A;
                background: rgba(229, 37, 33, 0.3);
                border: 1px solid rgba(255, 210, 0, 0.4);
            }

            .mobile-bottom-icon {
                font-size: 1.25rem;
                line-height: 1;
            }

            .mobile-header-actions {
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }

            .mobile-user-pill {
                display: flex;
                align-items: center;
                gap: 0.35rem;
                background: var(--surface-2);
                border: 1px solid var(--border-subtle);
                border-radius: 10px;
                padding: 0.25rem 0.4rem;
            }
        }

        .mobile-header-actions {
            display: none;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Navbar Oficial Big Pollo -->
    <nav class="navbar">
        <a href="/" class="brand-badge">
            <div class="brand-emblem" style="overflow: hidden; padding: 0; background: #E52521; border: 2px solid #FFD200;">
                <img src="/images/big_pollo_avatar.png" alt="Big Pollo Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
            </div>
            <div class="brand-texts">
                <div class="brand-name">Big Pollo</div>
                <div class="brand-slogan-badge">Asado & Broaster</div>
            </div>
        </a>

        <!-- Mobile Header Actions (Control rápido de turno y usuario) -->
        <div class="mobile-header-actions">
            @auth
            <div class="mobile-user-pill">
                <span class="user-role-tag role-{{ Auth::user()->role }}" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">
                    {{ \App\Enums\UserRole::tryFrom(Auth::user()->role)?->label() ?? ucfirst(Auth::user()->role) }}
                </span>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0; display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout" style="padding: 0.25rem 0.5rem; font-size: 0.72rem;" title="Cerrar Turno">
                        🚪
                    </button>
                </form>
            </div>
            @else
            <a href="/login" class="btn-nav-login" style="padding: 0.35rem 0.65rem; font-size: 0.78rem;">
                🔒 Entrar
            </a>
            @endauth
        </div>

        <ul class="nav-links">
            @auth
                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->isMesero() || Auth::user()->isCajero())
                <li>
                    <a href="/waiter" class="nav-link {{ request()->is('waiter') ? 'active' : '' }}">
                        <span>🍽️</span> Comandero Mesero
                    </a>
                </li>
                @endif

                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->isCocina())
                <li>
                    <a href="/kds" class="nav-link {{ request()->is('kds') ? 'active' : '' }}">
                        <span>👨‍🍳</span> Cocina KDS
                        <span class="pulse-dot"></span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->isCajero())
                <li>
                    <a href="/pos" class="nav-link {{ request()->is('pos') ? 'active' : '' }}">
                        <span>💳</span> Caja POS & Factus
                    </a>
                </li>
                @endif

                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                <li>
                    <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <span>📊</span> Dashboard Dueño
                    </a>
                </li>
                <li>
                    <a href="/admin/menu" class="nav-link {{ request()->is('admin/menu') ? 'active' : '' }}">
                        <span>🍗</span> Admin Menú
                    </a>
                </li>
                @endif
            @endauth

            @auth
            <li>
                <button class="tutorial-btn" onclick="startBigPolloTour()">
                    <span>📖</span> Guía de Uso
                </button>
            </li>

            <li>
                <div class="user-auth-badge">
                    <span class="user-role-tag role-{{ Auth::user()->role }}">
                        {{ \App\Enums\UserRole::tryFrom(Auth::user()->role)?->label() ?? ucfirst(Auth::user()->role) }}
                    </span>
                    <span class="user-name-text" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0; display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout" title="Cerrar Sesión de Turno">
                            <span>🚪 Salir</span>
                        </button>
                    </form>
                </div>
            </li>
            @else
            <li>
                <a href="/login" class="btn-nav-login">
                    <span>🔒</span> Iniciar Sesión
                </a>
            </li>
            @endauth
        </ul>
    </nav>

    <!-- Main Container -->
    <main class="main-container">
        @yield('content')
    </main>

    <!-- Executive Commercial Footer -->
    <footer class="footer-commercial">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>🍗 Restaurante Big Pollo — Asado & Broaster</h4>
                <p>
                    Plataforma SaaS Comercial Multi-Tenant para atención en salón, comanderos móviles, cocina KDS en tiempo real, gestión automática de recetas de inventario y facturación electrónica autorizada por la DIAN.
                </p>
                <div style="margin-top: 0.85rem; font-size: 0.78rem; color: var(--text-dim);">
                    <strong>NIT:</strong> 900.123.456-7 · <strong>Dirección:</strong> Calle Principal # 10-20<br>
                    <strong>Horario:</strong> Lunes a Domingo de 10:00 AM a 10:00 PM
                </div>
            </div>

            <div class="footer-col">
                <h4>🧭 Módulos del Sistema</h4>
                <ul>
                    <li><a href="/waiter">🍽️ Comandero Meseros (Mobile)</a></li>
                    <li><a href="/kds">👨‍🍳 Pantalla Cocina KDS</a></li>
                    <li><a href="/pos">💳 Terminal POS y Arqueo Z</a></li>
                    <li><a href="/admin/menu">🍗 Administración de Platos y Recetas</a></li>
                    <li><a href="/privacy">🛡️ Política de Habeas Data Ley 1581</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>🛡️ Seguridad y Cumplimiento</h4>
                <a href="/privacy" style="text-decoration: none; color: inherit; display: block;">
                    <div class="trust-badge" style="cursor: pointer; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#FFD200'" onmouseout="this.style.borderColor='var(--border-subtle)'">
                        <span style="font-size: 1.2rem;">🏛️</span>
                        <div>
                            <div style="font-size: 0.8rem; font-weight: 700; color: #FFFFFF;">Habeas Data Ley 1581 / SIC ➔</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">Tratamiento de datos comensales blindado</div>
                        </div>
                    </div>
                </a>

                <div class="trust-badge">
                    <span style="font-size: 1.2rem;">⚡</span>
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: #10B981;">🟢 Factus API DIAN: Conectado</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">Timbrado oficial UBL 2.1 con CUFE y QR</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span>© {{ date('Y') }} Restaurante Big Pollo Asado & Broaster. Todos los derechos reservados.</span>
                <span>·</span>
                <a href="/privacy" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; transition: color 0.2s;" onmouseover="this.style.color='#FFD200'" onmouseout="this.style.color='var(--text-muted)'">
                    <span>🛡️</span> Política de Privacidad & Habeas Data (SIC)
                </a>
            </div>
            <div>
                Desarrollado con arquitectura limpia por <strong>Brayan Stid Cortés Lombana (bscl)</strong>.
            </div>
        </div>
    </footer>

    <!-- 📖 Modal Tutorial / Guía Interactiva Paso a Paso -->
    <div id="tutorial-modal" class="modal-overlay">
        <div class="modal-tutorial-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="font-size: 1.8rem;">📖</div>
                    <div>
                        <h3 style="font-size: 1.3rem; font-weight: 900; color: #FFFFFF;">Guía de Uso Rápida del Sistema</h3>
                        <p style="font-size: 0.82rem; color: var(--text-muted);">Aprende a operar el restaurante en 2 minutos según tu puesto de trabajo.</p>
                    </div>
                </div>
                <button onclick="closeTutorialModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">✕</button>
            </div>

            <!-- Role Tabs -->
            <div class="role-tabs">
                <button class="role-tab-btn active" onclick="switchTutorialRole('waiter', this)">🍽️ Mesero (Toma de Pedidos)</button>
                <button class="role-tab-btn" onclick="switchTutorialRole('kds', this)">👨‍🍳 Cocina (KDS en Vivo)</button>
                <button class="role-tab-btn" onclick="switchTutorialRole('pos', this)">💳 Cajero (POS y Facturas)</button>
                <button class="role-tab-btn" onclick="switchTutorialRole('admin', this)">🍗 Administrador (Menú y Recetas)</button>
            </div>

            <!-- Tutorial Content -->
            <div id="tutorial-content">
                <!-- Injected via JS -->
            </div>

            <button onclick="closeTutorialModal()" style="width: 100%; margin-top: 1.25rem; padding: 0.85rem; border-radius: 12px; background: linear-gradient(135deg, #F59E0B, #D97706); color: #000; font-weight: 800; border: none; cursor: pointer;">
                ¡Entendido, volver a trabajar! 👍
            </button>
        </div>
    </div>

    <!-- ⚡ Simulador Flotante de Hora Pico -->
    <div id="sim-panel" style="position: fixed; bottom: 1.5rem; left: 1.5rem; z-index: 9999; font-family: 'Plus Jakarta Sans', sans-serif;">
        <div id="sim-card" style="background: rgba(15, 23, 42, 0.94); backdrop-filter: blur(16px); border: 2px solid var(--bp-gold); border-radius: 18px; padding: 1.25rem; width: 340px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); display: none; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="font-weight: 800; font-size: 0.95rem; color: #FFFFFF; display: flex; align-items: center; gap: 0.4rem;">
                    <span>⚡</span> Simulador de Hora Pico
                </div>
                <span class="pulse-dot"></span>
            </div>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.4;">
                Simula una ráfaga masiva de pedidos en 5 mesas, cocina KDS y cobro con Factura DIAN.
            </p>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <button id="sim-burst-btn" onclick="runPeakHourSimulation()" style="width: 100%; padding: 0.75rem; border-radius: 12px; background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%); color: #FFF; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 4px 12px var(--bp-red-glow);">
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

        <button id="sim-toggle-btn" onclick="toggleSimPanel()" style="padding: 0.75rem 1.25rem; border-radius: 30px; background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%); color: #FFF; font-weight: 800; font-size: 0.85rem; border: 1px solid #F59E0B; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 8px 24px var(--bp-red-glow); transition: all 0.2s;">
            <span>⚡ Modo Simulación Hora Pico</span>
        </button>
    </div>

    <div id="toast-container"></div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? '✅' : (type === 'error' ? '❌' : '🍗');
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

        // --- 📖 Lógica de Tutorial Onboarding ---
        const tutorialGuides = {
            waiter: [
                { num: 1, title: 'Selecciona la Mesa', desc: 'Toca la mesa que deseas atender en la grilla superior. Las mesas en verde están libres y en naranja ocupadas.' },
                { num: 2, title: 'Agrega los Platos al Carrito', desc: 'Navega por las pestañas de categorías (Broaster, Asado, Bebidas) y usa el botón (+) para añadir porciones. El subtotal y el 8% de impoconsumo se calculan solos.' },
                { num: 3, title: 'Enviar a Cocina', desc: 'Verifica la casilla de Habeas Data (Ley 1581) y presiona "Enviar a Cocina (KDS)". La orden viajará al instante a las pantallas de los cocineros y la mesa cambiará a estado Ocupada.' }
            ],
            kds: [
                { num: 1, title: 'Monitorea las Comandas en Tiempo Real', desc: 'Cada ticket muestra el número de mesa, platos pedidos, notas especiales y un cronómetro con semáforo (Verde < 10m, Amarillo 10-20m, Rojo parpadeante si hay demora).' },
                { num: 2, title: 'Comenzar Preparación', desc: 'Toca el botón morado "Preparar" cuando pongas las presas en la freidora o asador. El ticket cambiará a borde azul.' },
                { num: 3, title: '¡Plato Listo para Servir!', desc: 'Toca el botón verde "¡Plato Listo!" cuando el pedido esté emplatado. El mesero sabrá de inmediato que puede pasar a recogerlo.' }
            ],
            pos: [
                { num: 1, title: 'Apertura de Caja', desc: 'Al iniciar la jornada, haz clic en "Abrir Turno" e ingresa la base de dinero en efectivo con la que comienzas.' },
                { num: 2, title: 'Cobro de la Cuenta', desc: 'Selecciona la comanda en la lista izquierda, elige si el cliente paga en Efectivo, Tarjeta o Nequi, y presiona "Registrar Pago". La mesa se liberará sola.' },
                { num: 3, title: 'Factura Electrónica DIAN', desc: 'Presiona "Emitir Factura DIAN con Factus". El sistema enviará la factura a la DIAN y te mostrará el código CUFE y el QR reglamentario.' },
                { num: 4, title: 'Cierre Z y Arqueo', desc: 'Al final del turno, toca "Arqueo Z / Cerrar" e ingresa el efectivo contado físicamente para ver si hay sobrante, faltante o cuadre exacto.' }
            ],
            admin: [
                { num: 1, title: 'Crear Nuevos Platos', desc: 'Ve a "Admin Menú" y presiona "➕ Nuevo Plato". Ingresa el nombre del producto, categoría y precio de venta.' },
                { num: 2, title: 'Vincular Receta de Inventario', desc: 'Agrega los ingredientes que componen el plato (ej. 0.5 kg de Pollo, 2 Arepas). Cuando el mesero venda este plato, el sistema descontará automáticamente los insumos del almacén.' },
                { num: 3, title: 'Pausar Platos Agotados', desc: 'Si se acaba un ingrediente, puedes pausar el plato con 1 clic para que los meseros no puedan pedirlo hasta que haya stock.' }
            ]
        };

        function openTutorialModal() {
            document.getElementById('tutorial-modal').style.display = 'flex';
            renderTutorialContent('waiter');
        }

        function closeTutorialModal() {
            document.getElementById('tutorial-modal').style.display = 'none';
        }

        function switchTutorialRole(role, btn) {
            document.querySelectorAll('.role-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderTutorialContent(role);
        }

        function renderTutorialContent(role) {
            const container = document.getElementById('tutorial-content');
            const steps = tutorialGuides[role] || [];
            container.innerHTML = steps.map(s => `
                <div class="tutorial-step">
                    <div class="step-num">${s.num}</div>
                    <div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: #FFFFFF; margin-bottom: 0.2rem;">${s.title}</div>
                        <div style="font-size: 0.83rem; color: var(--text-muted); line-height: 1.5;">${s.desc}</div>
                    </div>
                </div>
            `).join('');
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
                const tablesRes = await fetch('/api/tables');
                const tablesJson = await tablesRes.json();
                const menuRes = await fetch('/api/menu');
                const menuJson = await menuRes.json();

                const tables = tablesJson.data || [];
                const products = [];
                (menuJson.data || []).forEach(c => (c.products || []).forEach(p => products.push(p)));

                if (tables.length === 0 || products.length === 0) {
                    setSimLog('No hay mesas o productos disponibles.', '#EF4444');
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
                    await new Promise(r => setTimeout(r, 600));
                }

                setSimLog('✅ 5 comandas enviadas a Cocina KDS.', '#10B981');
                showToast('¡Ráfaga completada! Revisa la pestaña Cocina KDS', 'success');

                if (typeof loadTables === 'function') loadTables();
            } catch (e) {
                setSimLog('Error en la simulación.', '#EF4444');
                console.error(e);
            }
        }

        async function runKdsCookSimulation() {
            setSimLog('👨‍🍳 Cocinando y despachando...', '#DC2626');
            showToast('Cocina acelerada: procesando todos los pedidos...', 'info');

            try {
                const res = await fetch('/api/orders');
                const json = await res.json();
                const orders = (json.data || []).filter(o => ['pending', 'in_kitchen'].includes(o.status));

                for (const ord of orders) {
                    await fetch(`/api/orders/${ord.id}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ status: 'in_kitchen' })
                    });
                    await new Promise(r => setTimeout(r, 300));

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
                setSimLog('Error en cocina.', '#EF4444');
            }
        }

        async function runPosCashSimulation() {
            setSimLog('💳 Cobrando y timbrando con Factus DIAN...', '#F59E0B');
            showToast('Cobrando comandas y emitiendo factura electrónica...', 'info');

            try {
                await fetch('/api/cash-shifts/open', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ opening_amount: 250000 })
                }).catch(() => {});

                const res = await fetch('/api/orders');
                const json = await res.json();
                const orders = (json.data || []).filter(o => o.status !== 'paid' && o.status !== 'cancelled');

                for (const ord of orders) {
                    await fetch(`/api/orders/${ord.id}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ status: 'paid' })
                    });

                    await fetch(`/api/orders/${ord.id}/invoice`, {
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
                setSimLog('Error en cobro.', '#EF4444');
            }
        }
    </script>
    
    <!-- Mobile Bottom App Bar para Meseros y Personal de Salón (<= 768px) -->
    <div class="mobile-bottom-bar">
        <a href="/waiter" class="mobile-bottom-item {{ request()->is('waiter') ? 'active' : '' }}">
            <span class="mobile-bottom-icon">🍽️</span>
            <span>Mesas</span>
        </a>
        <a href="/kds" class="mobile-bottom-item {{ request()->is('kds') ? 'active' : '' }}">
            <span class="mobile-bottom-icon">👨‍🍳</span>
            <span>Cocina</span>
        </a>
        <a href="/pos" class="mobile-bottom-item {{ request()->is('pos') ? 'active' : '' }}">
            <span class="mobile-bottom-icon">💳</span>
            <span>Caja</span>
        </a>
        <a href="/admin/menu" class="mobile-bottom-item {{ request()->is('admin/menu') ? 'active' : '' }}">
            <span class="mobile-bottom-icon">🍗</span>
            <span>Menú</span>
        </a>
        <button type="button" class="mobile-bottom-item" onclick="startBigPolloTour()">
            <span class="mobile-bottom-icon">📖</span>
            <span>Tour</span>
        </button>
    </div>

    @yield('scripts')
</body>
</html>
