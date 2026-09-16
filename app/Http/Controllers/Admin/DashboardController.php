<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Dashboard\GetBusinessMetricsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected GetBusinessMetricsAction $getBusinessMetricsAction
    ) {}

    /**
     * Muestra el panel de métricas de negocio para el dueño / administrador.
     */
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $metrics = $this->getBusinessMetricsAction->execute($tenantId);

        return view('admin.dashboard', compact('metrics'));
    }
}
