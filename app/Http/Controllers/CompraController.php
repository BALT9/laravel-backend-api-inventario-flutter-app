<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class CompraController extends Controller
{
    // ============================================================
    // GET /compras
    // ============================================================

    #[OA\Get(
        path: "/api/compras",
        tags: ["Compras"],
        summary: "Obtener todas las compras",
        description: "Obtiene todas las compras pertenecientes al negocio del usuario autenticado.",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de compras"
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

        $compras = Compra::with([
            'usuario:id,nombre,email',
            'sucursal:id,nombre',
            'detalles.producto:id,nombre,codigo'
        ])
            ->where(
                'negocio_id',
                $user->negocio_id
            )
            ->orderByDesc('fecha')
            ->get();

        return response()->json([
            'message' => 'Compras obtenidas correctamente',
            'data' => $compras
        ]);
    }


    // ============================================================
    // GET /compras/{id}
    // ============================================================

    #[OA\Get(
        path: "/api/compras/{id}",
        tags: ["Compras"],
        summary: "Obtener una compra por ID",
        description: "Obtiene una compra específica perteneciente al negocio del usuario autenticado.",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la compra",
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Compra encontrada"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            ),
            new OA\Response(
                response: 404,
                description: "Compra no encontrada"
            )
        ]
    )]
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $compra = Compra::with([
            'usuario:id,nombre,email',
            'sucursal:id,nombre',
            'detalles.producto:id,nombre,codigo'
        ])
            ->where(
                'negocio_id',
                $user->negocio_id
            )
            ->find($id);

        if (!$compra) {

            return response()->json([
                'message' => 'Compra no encontrada'
            ], 404);
        }

        return response()->json([
            'message' => 'Compra obtenida correctamente',
            'data' => $compra
        ]);
    }


    // ============================================================
    // POST /compras
    // ============================================================

    #[OA\Post(
        path: "/api/compras",
        tags: ["Compras"],
        summary: "Registrar una nueva compra",
        description: "Registra una compra, aumenta el stock de los productos y crea automáticamente los movimientos de inventario.",
        security: [["sanctum" => []]],

        requestBody: new OA\RequestBody(
            required: true,

            content: new OA\JsonContent(

                required: [
                    "sucursal_id",
                    "detalles"
                ],

                properties: [

                    new OA\Property(
                        property: "sucursal_id",
                        type: "integer",
                        description: "ID de la sucursal donde se registra la compra",
                        example: 1
                    ),

                    new OA\Property(
                        property: "fecha",
                        type: "string",
                        format: "date",
                        nullable: true,
                        description: "Fecha de la compra",
                        example: "2026-08-08"
                    ),

                    new OA\Property(
                        property: "observacion",
                        type: "string",
                        nullable: true,
                        description: "Observación de la compra",
                        example: "Compra de mercadería"
                    ),

                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        minItems: 1,
                        description: "Productos incluidos en la compra",

                        items: new OA\Items(
                            type: "object",

                            required: [
                                "producto_id",
                                "cantidad",
                                "precio"
                            ],

                            properties: [

                                new OA\Property(
                                    property: "producto_id",
                                    type: "integer",
                                    description: "ID del producto",
                                    example: 1
                                ),

                                new OA\Property(
                                    property: "cantidad",
                                    type: "integer",
                                    minimum: 1,
                                    description: "Cantidad comprada",
                                    example: 10
                                ),

                                new OA\Property(
                                    property: "precio",
                                    type: "number",
                                    format: "float",
                                    minimum: 0.01,
                                    description: "Precio unitario de compra",
                                    example: 150.00
                                )
                            ]
                        )
                    )
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: "Compra registrada correctamente"
            ),

            new OA\Response(
                response: 401,
                description: "No autenticado"
            ),

            new OA\Response(
                response: 403,
                description: "La sucursal no pertenece al negocio"
            ),

            new OA\Response(
                response: 422,
                description: "Error de validación"
            ),

            new OA\Response(
                response: 500,
                description: "Error al registrar la compra"
            )
        ]
    )]
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([

            'sucursal_id' => [
                'required',
                'integer',
                'exists:sucursales,id'
            ],

            'fecha' => [
                'nullable',
                'date'
            ],

            'observacion' => [
                'nullable',
                'string'
            ],

            'detalles' => [
                'required',
                'array',
                'min:1'
            ],

            'detalles.*.producto_id' => [
                'required',
                'integer',
                'exists:productos,id'
            ],

            'detalles.*.cantidad' => [
                'required',
                'integer',
                'min:1'
            ],

            'detalles.*.precio' => [
                'required',
                'numeric',
                'gt:0'
            ],
        ]);


        // ========================================================
        // Verificar sucursal
        // ========================================================

        $sucursal = Sucursal::where(
            'id',
            $validated['sucursal_id']
        )
            ->where(
                'negocio_id',
                $user->negocio_id
            )
            ->first();

        if (!$sucursal) {

            return response()->json([
                'message' =>
                    'La sucursal no pertenece a tu negocio'
            ], 403);
        }


        try {

            $compra = DB::transaction(
                function () use ($validated, $user) {

                    $total = 0;


                    // ====================================================
                    // Verificar productos
                    // ====================================================

                    foreach ($validated['detalles'] as $detalle) {

                        $producto = Producto::where(
                            'id',
                            $detalle['producto_id']
                        )
                            ->where(
                                'negocio_id',
                                $user->negocio_id
                            )
                            ->first();

                        if (!$producto) {

                            throw new \Exception(
                                "El producto {$detalle['producto_id']} no pertenece a tu negocio."
                            );
                        }


                        // Calcular subtotal

                        $subtotal =
                            $detalle['cantidad']
                            *
                            $detalle['precio'];

                        $total += $subtotal;
                    }


                    // ====================================================
                    // Crear compra
                    // ====================================================

                    $compra = Compra::create([

                        'negocio_id' =>
                            $user->negocio_id,

                        'user_id' =>
                            $user->id,

                        'sucursal_id' =>
                            $validated['sucursal_id'],

                        'fecha' =>
                            $validated['fecha']
                            ?? now(),

                        'total' =>
                            $total,

                        'observacion' =>
                            $validated['observacion']
                            ?? null,
                    ]);


                    // ====================================================
                    // Crear detalles y actualizar stock
                    // ====================================================

                    foreach ($validated['detalles'] as $detalle) {

                        $producto = Producto::where(
                            'id',
                            $detalle['producto_id']
                        )
                            ->where(
                                'negocio_id',
                                $user->negocio_id
                            )
                            ->lockForUpdate()
                            ->first();


                        $subtotal =
                            $detalle['cantidad']
                            *
                            $detalle['precio'];


                        // Stock anterior

                        $stockAnterior =
                            $producto->stock;


                        // Aumentar stock

                        $producto->stock +=
                            $detalle['cantidad'];

                        $producto->save();


                        // ====================================================
                        // Crear detalle
                        // ====================================================

                        CompraDetalle::create([

                            'compra_id' =>
                                $compra->id,

                            'producto_id' =>
                                $producto->id,

                            'cantidad' =>
                                $detalle['cantidad'],

                            'precio' =>
                                $detalle['precio'],

                            'subtotal' =>
                                $subtotal,
                        ]);


                        // ====================================================
                        // Registrar movimiento
                        // ====================================================

                        Movimiento::create([

                            'negocio_id' =>
                                $user->negocio_id,

                            'producto_id' =>
                                $producto->id,

                            'user_id' =>
                                $user->id,

                            'tipo' =>
                                'entrada',

                            'cantidad' =>
                                $detalle['cantidad'],

                            'stock_anterior' =>
                                $stockAnterior,

                            'stock_actual' =>
                                $producto->stock,
                        ]);
                    }


                    return $compra;
                }
            );


            // ========================================================
            // Cargar relaciones
            // ========================================================

            $compra->load([

                'usuario:id,nombre,email',

                'sucursal:id,nombre',

                'detalles.producto:id,nombre,codigo'

            ]);


            return response()->json([

                'message' =>
                    'Compra registrada correctamente',

                'data' =>
                    $compra

            ], 201);


        } catch (\Exception $e) {

            return response()->json([

                'message' =>
                    'No se pudo registrar la compra',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }
}
