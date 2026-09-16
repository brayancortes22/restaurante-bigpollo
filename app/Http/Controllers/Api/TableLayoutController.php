<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableLayoutController extends Controller
{
    /**
     * Actualiza masivamente las coordenadas y zonas de las mesas en el mapa
     */
    public function updateLayout(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));

        $validated = $request->validate([
            'tables' => ['required', 'array'],
            'tables.*.id' => ['required', 'integer', 'exists:restaurant_tables,id'],
            'tables.*.pos_x' => ['required', 'numeric', 'min:0', 'max:100'],
            'tables.*.pos_y' => ['required', 'numeric', 'min:0', 'max:100'],
            'tables.*.zone' => ['nullable', 'string', 'max:50'],
            'tables.*.shape' => ['nullable', 'string', 'in:square,round,rect'],
        ]);

        foreach ($validated['tables'] as $tableData) {
            RestaurantTable::where('id', $tableData['id'])
                ->where('tenant_id', $tenantId)
                ->update([
                    'pos_x' => $tableData['pos_x'],
                    'pos_y' => $tableData['pos_y'],
                    'zone' => $tableData['zone'] ?? 'calle',
                    'shape' => $tableData['shape'] ?? 'square',
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => '¡Distribución del plano guardada con éxito!',
        ]);
    }

    /**
     * Crea una nueva mesa en el plano
     */
    public function storeTable(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));

        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'zone' => ['nullable', 'string', 'max:50'],
            'shape' => ['nullable', 'string', 'in:square,round,rect'],
            'pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $table = RestaurantTable::create([
            'tenant_id' => $tenantId,
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'zone' => $validated['zone'] ?? 'calle',
            'shape' => $validated['shape'] ?? 'square',
            'pos_x' => $validated['pos_x'] ?? 50,
            'pos_y' => $validated['pos_y'] ?? 50,
            'status' => 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Mesa {$table->table_number} agregada al plano.",
            'table' => $table,
        ], 201);
    }

    /**
     * Elimina una mesa si no tiene órdenes activas
     */
    public function destroyTable(RestaurantTable $table): JsonResponse
    {
        if ($table->status === 'occupied') {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar la mesa {$table->table_number} porque tiene comanda activa.",
            ], 422);
        }

        $table->delete();

        return response()->json([
            'success' => true,
            'message' => "Mesa {$table->table_number} eliminada del plano.",
        ]);
    }

    /**
     * Restaura la distribución original del plano arquitectónico Big Pollo
     */
    public function resetToDefault(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->input('tenant_id', Tenant::first()?->id ?? 1));

        $blueprintDefaults = [
            // Salón Frente a la Calle (Fila 1)
            ['number' => 'Mesa 1', 'pos_x' => 9.5, 'pos_y' => 12.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 2', 'pos_x' => 20.6, 'pos_y' => 12.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 3', 'pos_x' => 31.7, 'pos_y' => 12.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 4', 'pos_x' => 42.8, 'pos_y' => 12.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 5', 'pos_x' => 53.9, 'pos_y' => 12.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 6', 'pos_x' => 67.5, 'pos_y' => 13.0, 'capacity' => 2, 'shape' => 'rect', 'zone' => 'calle'],
            ['number' => 'Mesa 7', 'pos_x' => 76.5, 'pos_y' => 13.0, 'capacity' => 2, 'shape' => 'rect', 'zone' => 'calle'],
            ['number' => 'Mesa 8', 'pos_x' => 85.5, 'pos_y' => 13.0, 'capacity' => 2, 'shape' => 'rect', 'zone' => 'calle'],
            ['number' => 'Mesa 9', 'pos_x' => 94.5, 'pos_y' => 13.0, 'capacity' => 2, 'shape' => 'rect', 'zone' => 'calle'],

            // Salón Frente a la Calle (Fila 2)
            ['number' => 'Mesa 10', 'pos_x' => 9.5, 'pos_y' => 31.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 11', 'pos_x' => 20.6, 'pos_y' => 31.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 12', 'pos_x' => 31.7, 'pos_y' => 31.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],
            ['number' => 'Mesa 13', 'pos_x' => 42.8, 'pos_y' => 31.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'calle'],

            // Salón Principal (Fila 1)
            ['number' => 'Mesa 14', 'pos_x' => 9.5, 'pos_y' => 61.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'principal'],
            ['number' => 'Mesa 15', 'pos_x' => 23.0, 'pos_y' => 61.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'principal'],
            ['number' => 'Mesa 16', 'pos_x' => 36.5, 'pos_y' => 61.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'principal'],

            // Salón Principal (Fila 2)
            ['number' => 'Mesa 17', 'pos_x' => 9.5, 'pos_y' => 79.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'principal'],
            ['number' => 'Mesa 18', 'pos_x' => 23.0, 'pos_y' => 79.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'principal'],
            ['number' => 'Mesa 19', 'pos_x' => 36.5, 'pos_y' => 79.0, 'capacity' => 4, 'shape' => 'square', 'zone' => 'principal'],
        ];

        foreach ($blueprintDefaults as $def) {
            RestaurantTable::updateOrCreate(
                ['tenant_id' => $tenantId, 'table_number' => $def['number']],
                [
                    'capacity' => $def['capacity'],
                    'pos_x' => $def['pos_x'],
                    'pos_y' => $def['pos_y'],
                    'shape' => $def['shape'],
                    'zone' => $def['zone'],
                    'status' => 'available',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Distribución del plano restaurada a la arquitectura oficial de Big Pollo (19 mesas).',
        ]);
    }
}
