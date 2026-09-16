@extends('layouts.app')

@section('title', 'Iniciar Sesión - Control de Acceso')

@section('styles')
<style>
    .login-wrapper {
        min-height: calc(100vh - 220px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .login-card {
        background: var(--surface-1);
        border: 2px solid var(--border-highlight);
        border-radius: 24px;
        width: 100%;
        max-width: 460px;
        padding: 2.5rem 2.25rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7);
        position: relative;
        overflow: hidden;
    }

    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #DC2626, #F59E0B, #DC2626);
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-emblem {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: radial-gradient(circle, #FDE047 0%, #F59E0B 60%, #DC2626 100%);
        border: 2px solid #FEF08A;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin: 0 auto 1rem auto;
        position: relative;
    }

    .login-thumb {
        position: absolute;
        bottom: -4px;
        right: -4px;
        font-size: 1.1rem;
    }

    .login-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: #FFFFFF;
        letter-spacing: -0.02em;
    }

    .login-subtitle {
        font-size: 0.86rem;
        color: var(--text-muted);
        margin-top: 0.35rem;
    }

    .login-form-group {
        margin-bottom: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .login-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
    }

    .login-input {
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        border-radius: 12px;
        padding: 0.8rem 1rem;
        color: #FFFFFF;
        font-size: 0.92rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .login-input:focus {
        border-color: #F59E0B;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
    }

    .btn-login-submit {
        background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
        color: #FFFFFF;
        border: 1px solid #F59E0B;
        padding: 0.9rem;
        border-radius: 12px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        transition: all 0.2s;
        margin-top: 1rem;
    }

    .btn-login-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.6);
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    }

    .alert-login-error {
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #FCA5A5;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Sección de Acceso Rápido / Switch de Roles */
    .quick-roles-wrap {
        margin-top: 2rem;
        width: 100%;
        max-width: 620px;
    }

    .quick-roles-title {
        text-align: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .quick-roles-title::before, .quick-roles-title::after {
        content: '';
        height: 1px;
        background: var(--border-subtle);
        flex: 1;
    }

    .quick-roles-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .role-card-quick {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 0.85rem 1rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-align: left;
    }

    .role-card-quick:hover {
        border-color: #F59E0B;
        background: var(--surface-2);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
    }

    .role-card-icon {
        font-size: 1.5rem;
    }

    .role-card-name {
        font-weight: 800;
        font-size: 0.88rem;
        color: #FFFFFF;
    }

    .role-card-sub {
        font-size: 0.72rem;
        color: var(--text-muted);
    }
</style>
@endsection

@section('content')
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-emblem">
                🍗
                <div class="login-thumb">👍</div>
            </div>
            <h1 class="login-title">Acceso de Personal</h1>
            <p class="login-subtitle">Sistema POS, KDS & Gestión Restaurante Big Pollo</p>
        </div>

        @if ($errors->any())
            <div class="alert-login-error">
                <span>⚠️</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('info'))
            <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #FEF08A; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.85rem; margin-bottom: 1.25rem;">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" id="mainLoginForm">
            @csrf
            <div class="login-form-group">
                <label class="login-label">Correo Electrónico Corporativo</label>
                <input type="email" name="email" id="inputEmail" class="login-input" placeholder="ej. admin@bigpollo.com" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="login-form-group">
                <label class="login-label">Contraseña de Seguridad</label>
                <input type="password" name="password" id="inputPassword" class="login-input" placeholder="••••••••" required>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; margin-bottom: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: var(--text-muted); cursor: pointer;">
                    <input type="checkbox" name="remember" value="1" style="accent-color: #DC2626;"> Recordar mi turno
                </label>
                <a href="/privacy" style="font-size: 0.78rem; color: #F59E0B; text-decoration: none;">Seguridad & Datos</a>
            </div>

            <button type="submit" class="btn-login-submit" id="btnLoginSubmit">
                <span>🔒</span> Ingresar al Sistema
            </button>
        </form>
    </div>

    <!-- Selector de Acceso Rápido para Demostración y Pruebas -->
    <div class="quick-roles-wrap">
        <div class="quick-roles-title">
            <span>⚡ Acceso Rápido por Perfil (Entorno Seguro)</span>
        </div>

        <div class="quick-roles-grid">
            <!-- Admin -->
            <button type="button" class="role-card-quick" onclick="quickFillAndLogin('admin@bigpollo.com', 'admin123')">
                <div class="role-card-icon">👑</div>
                <div>
                    <div class="role-card-name">Administrador</div>
                    <div class="role-card-sub">Menú, Recetas y Supervisión Total</div>
                </div>
            </button>

            <!-- Cajero -->
            <button type="button" class="role-card-quick" onclick="quickFillAndLogin('cajero@bigpollo.com', 'caja123')">
                <div class="role-card-icon">💳</div>
                <div>
                    <div class="role-card-name">Cajero / POS</div>
                    <div class="role-card-sub">Turnos, Cobro y Factura DIAN</div>
                </div>
            </button>

            <!-- Mesero -->
            <button type="button" class="role-card-quick" onclick="quickFillAndLogin('mesero@bigpollo.com', 'mesero123')">
                <div class="role-card-icon">🍽️</div>
                <div>
                    <div class="role-card-name">Mesero / Salonero</div>
                    <div class="role-card-sub">Toma de Comandas en Mesas</div>
                </div>
            </button>

            <!-- Cocina -->
            <button type="button" class="role-card-quick" onclick="quickFillAndLogin('cocina@bigpollo.com', 'cocina123')">
                <div class="role-card-icon">👨‍🍳</div>
                <div>
                    <div class="role-card-name">Jefe de Cocina</div>
                    <div class="role-card-sub">Pantalla KDS en Tiempo Real</div>
                </div>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function quickFillAndLogin(email, password) {
        document.getElementById('inputEmail').value = email;
        document.getElementById('inputPassword').value = password;
        const btn = document.getElementById('btnLoginSubmit');
        btn.disabled = true;
        btn.innerHTML = '⏳ Autenticando perfil...';
        document.getElementById('mainLoginForm').submit();
    }
</script>
@endsection
