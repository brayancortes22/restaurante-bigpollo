<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function privacyPolicy(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->query('tenant_id', Tenant::first()?->id ?? 1));
        $tenant = Tenant::find($tenantId) ?? Tenant::first();

        return response()->json([
            'status' => 'success',
            'legal_framework' => [
                'country' => 'Colombia',
                'laws' => [
                    'Ley 1581 de 2012 (Régimen General de Protección de Datos Personales)',
                    'Decreto 1377 de 2013',
                    'Circular Externa 002 de la Superintendencia de Industria y Comercio (SIC)',
                    'Estatuto Tributario Art. 632 (Obligación de Conservación Fiscal)',
                ],
            ],
            'controller' => [
                'business_name' => $tenant?->name ?? 'Restaurante Big Pollo',
                'nit' => $tenant?->nit ?? '900.123.456-7',
                'contact_email' => $tenant?->email ?? 'legal@bigpollo.com',
                'address' => $tenant?->address ?? 'Calle Principal # 10-20',
            ],
            'purposes' => [
                'Emisión y validación previa de facturas electrónicas ante la DIAN',
                'Atención y despacho de pedidos en salón, domicilio o para llevar',
                'Notificaciones sobre el estado de la comanda y entrega',
            ],
            'rights_arco' => [
                'Acceso a sus datos personales almacenados',
                'Rectificación o actualización de datos erróneos',
                'Supresión o revocación de la autorización (conforme a la excepción legal de conservación contable/fiscal de 5 años por la DIAN)',
            ],
            'contact_channel' => 'Para ejercer sus derechos ARCO, envíe su solicitud a: ' . ($tenant?->email ?? 'legal@bigpollo.com'),
        ]);
    }
}
