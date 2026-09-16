@extends('layouts.app')

@section('title', 'Política de Tratamiento de Datos (Habeas Data)')

@section('styles')
<style>
    .legal-card {
        background: var(--surface-1);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 2.5rem;
        max-width: 900px;
        margin: 0 auto;
        line-height: 1.7;
    }

    .legal-card h1 {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #FFFFFF 0%, #CBD5E1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .legal-card h2 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 1.5rem 0 0.5rem;
        color: #F59E0B;
    }

    .legal-badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        background: rgba(99, 102, 241, 0.15);
        color: var(--primary);
        border: 1px solid var(--primary-glow);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
    }

    .arco-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .arco-list li {
        background: var(--surface-2);
        border-left: 4px solid var(--success);
        padding: 0.85rem 1.2rem;
        border-radius: 8px;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="legal-card">
    <div class="legal-badge">Cumplimiento Oficial Ley 1581 de 2012 / SIC</div>
    <h1>🛡️ Política de Tratamiento de Datos Personales (Habeas Data)</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
        Restaurante Big Pollo informa a sus comensales, usuarios y proveedores sobre el tratamiento que se le otorga a sus datos personales en estricto apego a la Constitución Política de Colombia, la <strong>Ley Estatutaria 1581 de 2012</strong>, el <strong>Decreto 1377 de 2013</strong> y la <strong>Circular Externa 002 de la Superintendencia de Industria y Comercio (SIC)</strong>.
    </p>

    <h2>1. Responsable del Tratamiento</h2>
    <p style="color: var(--text-muted);">
        <strong>Razón Social:</strong> Restaurante Big Pollo S.A.S.<br>
        <strong>NIT:</strong> 900.123.456-7<br>
        <strong>Domicilio:</strong> Colombia<br>
        <strong>Canal Oficial de Atención:</strong> <span style="color: var(--primary); font-weight: 600;">legal@bigpollo.com</span>
    </p>

    <h2>2. Finalidades de la Recolección</h2>
    <p style="color: var(--text-muted);">Los datos solicitados (nombre, identificación, correo electrónico, teléfono y dirección) son recolectados con las siguientes finalidades exclusivas:</p>
    <ul style="margin-left: 1.5rem; color: var(--text-muted); margin-top: 0.5rem;">
        <li>Emisión, transmisión y validación previa de <strong>Facturas Electrónicas de Venta ante la DIAN</strong>.</li>
        <li>Gestión, despacho y entrega de comandas y pedidos en salón o domicilio.</li>
        <li>Atención de peticiones, quejas, reclamos y soporte posventa.</li>
    </ul>

    <h2>3. Derechos del Titular (Derechos ARCO)</h2>
    <p style="color: var(--text-muted);">Como titular de los datos personales, le asisten los siguientes derechos consagrados por la legislación colombiana:</p>
    <ul class="arco-list">
        <li><strong>Acceso:</strong> Conocer los datos personales que se encuentran bajo tratamiento en nuestras bases de datos.</li>
        <li><strong>Rectificación:</strong> Actualizar y rectificar datos inexactos, incompletos o desactualizados.</li>
        <li><strong>Supresión:</strong> Solicitar la eliminación de sus datos cuando considere que no están siendo tratados conforme a la ley.</li>
        <li><strong>Excepción Tributaria DIAN:</strong> Por mandato expreso del <em>Artículo 632 del Estatuto Tributario de Colombia</em>, la información vinculada a facturas electrónicas emitidas no podrá suprimirse antes de cinco (5) años con fines de auditoría fiscal.</li>
    </ul>

    <h2>4. Canal de Peticiones y Reclamos</h2>
    <p style="color: var(--text-muted);">
        Para ejercer cualquiera de sus derechos, envíe un correo a <strong>legal@bigpollo.com</strong> indicando su nombre completo, número de documento y el derecho que desea ejercer. Su solicitud será resuelta en un plazo máximo de diez (10) días hábiles conforme al régimen legal vigente.
    </p>
</div>
@endsection
