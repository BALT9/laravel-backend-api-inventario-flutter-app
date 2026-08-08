<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MovimientoController extends Controller
{
    // ============================================================
    // GET /movimientos
    // ============================================================

    #[OA\Get(
        path: "/api/movimientos",
        tags: ["Movimientos"],
        summary: "Obtener movimientos de inventario",
        description: "Obtiene los movimientos de inventario pertenecientes al negocio del usuario autenticado. Permite filtrar por producto, tipo de movimiento y fechas.",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "producto_id",
                in: "query",
                required: false,
                description: "Filtrar movimientos por ID del producto",
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            ),

            new OA\Parameter(
                name: "tipo",
                in: "query",
                required: false,
                description: "Filtrar por tipo de movimiento",
                schema: new OA\Schema(
                    type: "string",
                    enum: [
                        "entrada",
                        "salida",
                        "ajuste"
                    ],
                    example: "entrada"
                )
            ),

            new OA\Parameter(
                name: "fecha",
                in: "query",
                required: false,
                description: "Filtrar movimientos de una fecha específica",
                schema: new OA\Schema(
                    type: "string",
                    format: "date",
                    example: "2026-08-08"
                )
            ),

            new OA\Parameter(
                name: "fecha_desde",
                in: "query",
                required: false,
                description: "Mostrar movimientos desde esta fecha",
                schema: new OA\Schema(
                    type: "string",
                    format: "date",
                    example: "2026-08-01"
                )
            ),

            new OA\Parameter(
                name: "fecha_hasta",
                in: "query",
                required: false,
                description: "Mostrar movimientos hasta esta fecha",
                schema: new OA\Schema(
                    type: "string",
                    format: "date",
                    example: "2026-08-08"
                )
            ),

            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                description: "Número de página",
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: "Lista de movimientos"
            ),

            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Movimiento::with([
            'producto:id,codigo,nombre',
            'usuario:id,nombre,email'
        ])
            ->where(
                'negocio_id',
                $user->negocio_id
            );


        // ========================================================
        // Filtrar por producto
        // ========================================================

        if ($request->filled('producto_id')) {

            $query->where(
                'producto_id',
                $request->producto_id
            );
        }


        // ========================================================
        // Filtrar por tipo
        // ========================================================

        if ($request->filled('tipo')) {

            $query->where(
                'tipo',
                $request->tipo
            );
        }


        // ========================================================
        // Filtrar por fecha específica
        // ========================================================

        if ($request->filled('fecha')) {

            $query->whereDate(
                'created_at',
                $request->fecha
            );
        }


        // ========================================================
        // Filtrar desde una fecha
        // ========================================================

        if ($request->filled('fecha_desde')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->fecha_desde
            );
        }


        // ========================================================
        // Filtrar hasta una fecha
        // ========================================================

        if ($request->filled('fecha_hasta')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->fecha_hasta
            );
        }


        // ========================================================
        // Ordenar y paginar
        // ========================================================

        $movimientos = $query
            ->orderByDesc('created_at')
            ->paginate(10);


        return response()->json(
            $movimientos,
            200
        );
    }
}
