<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class VentaController extends Controller
{
    // ============================================================
    // GET /ventas
    // ============================================================

    #[OA\Get(
        path: "/api/ventas",
        tags: ["Ventas"],
        summary: "Obtener todas las ventas",
        description: "Obtiene las ventas pertenecientes al negocio del usuario autenticado.",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de ventas"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function index()
    {
        $ventas = Venta::with([
            'usuario:id,nombre,email',
            'sucursal:id,nombre',
            'detalles.producto:id,codigo,nombre'
        ])
            ->where(
                'negocio_id',
                Auth::user()->negocio_id
            )
            ->orderByDesc('fecha')
            ->paginate(10);

        return response()->json(
            $ventas,
            200
        );
    }


    // ============================================================
    // GET /ventas/{id}
    // ============================================================

    #[OA\Get(
        path: "/api/ventas/{id}",
        tags: ["Ventas"],
        summary: "Obtener una venta por ID",
        description: "Obtiene una venta específica perteneciente al negocio del usuario autenticado.",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la venta",
                schema: new OA\Schema(
                    type: "integer",
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Venta encontrada"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            ),
            new OA\Response(
                response: 404,
                description: "Venta no encontrada"
            )
        ]
    )]
    public function show($id)
    {
        $venta = Venta::with([
            'usuario:id,nombre,email',
            'sucursal:id,nombre',
            'detalles.producto:id,codigo,nombre'
        ])
            ->where(
                'negocio_id',
                Auth::user()->negocio_id
            )
            ->find($id);

        if (!$venta) {

            return response()->json([
                "message" => "Venta no encontrada"
            ], 404);
        }

        return response()->json(
            $venta,
            200
        );
    }


    // ============================================================
    // POST /ventas
    // ============================================================

    #[OA\Post(
        path: "/api/ventas",
        tags: ["Ventas"],
        summary: "Registrar una nueva venta",
        description: "Registra una venta, disminuye el stock de los productos y crea automáticamente los movimientos de inventario.",
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
                        description: "ID de la sucursal donde se realiza la venta",
                        example: 1
                    ),

                    new OA\Property(
                        property: "fecha",
                        type: "string",
                        format: "date",
                        nullable: true,
                        description: "Fecha de la venta",
                        example: "2026-08-08"
                    ),

                    new OA\Property(
                        property: "observacion",
                        type: "string",
                        nullable: true,
                        description: "Observación de la venta",
                        example: "Venta de productos"
                    ),

                    new OA\Property(
                        property: "detalles",
                        type: "array",
                        minItems: 1,
                        description: "Productos incluidos en la venta",
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
                                    description: "Cantidad de productos vendidos",
                                    example: 2
                                ),

                                new OA\Property(
                                    property: "precio",
                                    type: "number",
                                    format: "float",
                                    minimum: 0.01,
                                    description: "Precio unitario de venta",
                                    example: 220.00
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
                description: "Venta registrada correctamente"
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
                description: "Error al registrar la venta"
            )
        ]
    )]
    public function store(Request $request)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Verificar sucursal
        |--------------------------------------------------------------------------
        */

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
                "message" =>
                    "La sucursal no pertenece a tu negocio"
            ], 403);
        }


        try {

            $venta = DB::transaction(function () use (
                $validated,
                $user
            ) {

                $total = 0;


                /*
                |--------------------------------------------------------------------------
                | Verificar productos y stock
                |--------------------------------------------------------------------------
                */

                foreach ($validated['detalles'] as $detalle) {

                    $producto = Producto::where(
                        'id',
                        $detalle['producto_id']
                    )
                        ->where(
                            'negocio_id',
                            $user->negocio_id
                        )
                        ->where(
                            'sucursal_id',
                            $validated['sucursal_id']
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$producto) {

                        throw new \Exception(
                            "El producto {$detalle['producto_id']} no pertenece a tu negocio o sucursal."
                        );
                    }


                    /*
                    | Verificar stock disponible
                    */

                    if (
                        $detalle['cantidad']
                        >
                        $producto->stock
                    ) {

                        throw new \Exception(
                            "Stock insuficiente para el producto {$producto->nombre}. Stock disponible: {$producto->stock}."
                        );
                    }


                    /*
                    | Calcular subtotal
                    */

                    $subtotal =
                        $detalle['cantidad']
                        *
                        $detalle['precio'];

                    $total += $subtotal;
                }


                /*
                |--------------------------------------------------------------------------
                | Crear venta
                |--------------------------------------------------------------------------
                */

                $venta = Venta::create([

                    'negocio_id' =>
                        $user->negocio_id,

                    'user_id' =>
                        $user->id,

                    'sucursal_id' =>
                        $validated['sucursal_id'],

                    'fecha' =>
                        $validated['fecha'] ?? now(),

                    'total' =>
                        $total,

                    'observacion' =>
                        $validated['observacion'] ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Crear detalles y actualizar stock
                |--------------------------------------------------------------------------
                */

                foreach ($validated['detalles'] as $detalle) {

                    $producto = Producto::where(
                        'id',
                        $detalle['producto_id']
                    )
                        ->where(
                            'negocio_id',
                            $user->negocio_id
                        )
                        ->where(
                            'sucursal_id',
                            $validated['sucursal_id']
                        )
                        ->lockForUpdate()
                        ->first();


                    /*
                    | Calcular subtotal
                    */

                    $subtotal =
                        $detalle['cantidad']
                        *
                        $detalle['precio'];


                    /*
                    | Guardar stock anterior
                    */

                    $stockAnterior =
                        $producto->stock;


                    /*
                    | Disminuir stock
                    */

                    $producto->stock =
                        $producto->stock
                        -
                        $detalle['cantidad'];

                    $producto->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Crear detalle
                    |--------------------------------------------------------------------------
                    */

                    VentaDetalle::create([

                        'venta_id' =>
                            $venta->id,

                        'producto_id' =>
                            $producto->id,

                        'cantidad' =>
                            $detalle['cantidad'],

                        'precio' =>
                            $detalle['precio'],

                        'subtotal' =>
                            $subtotal,
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Crear movimiento
                    |--------------------------------------------------------------------------
                    */

                    Movimiento::create([

                        'negocio_id' =>
                            $user->negocio_id,

                        'producto_id' =>
                            $producto->id,

                        'user_id' =>
                            $user->id,

                        'tipo' =>
                            'salida',

                        'cantidad' =>
                            $detalle['cantidad'],

                        'stock_anterior' =>
                            $stockAnterior,

                        'stock_actual' =>
                            $producto->stock,
                    ]);
                }


                return $venta;
            });


            /*
            |--------------------------------------------------------------------------
            | Cargar relaciones
            |--------------------------------------------------------------------------
            */

            $venta->load([
                'usuario:id,nombre,email',
                'sucursal:id,nombre',
                'detalles.producto:id,codigo,nombre'
            ]);


            return response()->json([

                "message" =>
                    "Venta registrada correctamente",

                "data" =>
                    $venta

            ], 201);


        } catch (\Exception $e) {

            return response()->json([

                "message" =>
                    "Error al registrar la venta",

                "error" =>
                    $e->getMessage()

            ], 500);
        }
    }
}
