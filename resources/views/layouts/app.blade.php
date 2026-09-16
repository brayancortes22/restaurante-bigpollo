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
    </script>
    @yield('scripts')
</body>
</html>
