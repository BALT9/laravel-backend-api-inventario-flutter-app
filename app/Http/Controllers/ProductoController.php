<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class ProductoController extends Controller
{


    // GET /productos

    #[OA\Get(
        path: "/api/productos",
        tags: ["Productos"],
        summary: "Obtener productos del negocio autenticado con filtros y paginación",
        security: [["sanctum" => []]],
        parameters: [

            new OA\Parameter(
                name: "busqueda",
                in: "query",
                required: false,
                description: "Buscar producto por nombre o código",
                schema: new OA\Schema(
                    type: "string"
                )
            ),

            new OA\Parameter(
                name: "categoria_id",
                in: "query",
                required: false,
                description: "Filtrar productos por categoría",
                schema: new OA\Schema(
                    type: "integer"
                )
            ),

            new OA\Parameter(
                name: "sucursal_id",
                in: "query",
                required: false,
                description: "Filtrar productos por sucursal",
                schema: new OA\Schema(
                    type: "integer"
                )
            ),

            new OA\Parameter(
                name: "limite",
                in: "query",
                required: false,
                description: "Cantidad de registros por página",
                schema: new OA\Schema(
                    type: "integer",
                    example: 10
                )
            )

        ],
        responses: [

            new OA\Response(
                response: 200,
                description: "Lista de productos paginada"
            ),

            new OA\Response(
                response: 401,
                description: "No autenticado"
            )

        ]
    )]
    public function index(Request $request)
    {

        $productos = Producto::with([
            "categoria",
            "sucursal"
        ])
            ->where(
                "negocio_id",
                Auth::user()->negocio_id
            );


        // búsqueda por nombre o código

        if ($request->busqueda) {

            $productos->where(function ($query) use ($request) {

                $query->where(
                    "nombre",
                    "LIKE",
                    "%" . $request->busqueda . "%"
                )
                    ->orWhere(
                        "codigo",
                        "LIKE",
                        "%" . $request->busqueda . "%"
                    );
            });
        }



        // filtro categoría

        if ($request->categoria_id) {

            $productos->where(
                "categoria_id",
                $request->categoria_id
            );
        }



        // filtro sucursal

        if ($request->sucursal_id) {

            $productos->where(
                "sucursal_id",
                $request->sucursal_id
            );
        }



        $limite = $request->limite ?? 10;



        return response()->json(

            $productos->paginate($limite),

            200

        );
    }





    // GET /productos/{id}


    #[OA\Get(
        path: "/api/productos/{id}",
        tags: ["Productos"],
        summary: "Obtener un producto por ID",
        security: [["sanctum" => []]],
        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del producto",
                schema: new OA\Schema(
                    type: "integer"
                )
            )

        ],
        responses: [

            new OA\Response(
                response: 200,
                description: "Producto encontrado"
            ),

            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            )

        ]
    )]
    public function show($id)
    {

        $producto = Producto::with([
            "categoria",
            "sucursal"
        ])
            ->where(
                "negocio_id",
                Auth::user()->negocio_id
            )
            ->find($id);



        if (!$producto) {

            return response()->json([

                "message" => "Producto no encontrado"

            ], 404);
        }


        return response()->json(

            $producto,

            200

        );
    }

    // POST /productos


    #[OA\Post(
        path: "/api/productos",
        tags: ["Productos"],
        summary: "Crear un producto",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(

                required: [
                    "categoria_id",
                    "sucursal_id",
                    "codigo",
                    "nombre",
                    "precio_compra",
                    "precio_venta",
                    "stock"
                ],

                properties: [

                    new OA\Property(
                        property: "categoria_id",
                        type: "integer",
                        example: 1
                    ),

                    new OA\Property(
                        property: "sucursal_id",
                        type: "integer",
                        example: 1
                    ),

                    new OA\Property(
                        property: "codigo",
                        type: "string",
                        example: "PROD001"
                    ),

                    new OA\Property(
                        property: "nombre",
                        type: "string",
                        example: "Teclado mecánico"
                    ),

                    new OA\Property(
                        property: "descripcion",
                        type: "string",
                        example: "Teclado RGB"
                    ),

                    new OA\Property(
                        property: "precio_compra",
                        type: "number",
                        example: 150
                    ),

                    new OA\Property(
                        property: "precio_venta",
                        type: "number",
                        example: 220
                    ),

                    new OA\Property(
                        property: "stock",
                        type: "integer",
                        example: 20
                    ),

                    new OA\Property(
                        property: "stock_minimo",
                        type: "integer",
                        example: 5
                    )

                ]

            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: "Producto creado correctamente"
            ),

            new OA\Response(
                response: 401,
                description: "No autenticado"
            )

        ]
    )]
    public function store(Request $request)
    {

        $request->validate([

            "categoria_id" => "required|exists:categorias,id",

            "sucursal_id" => "required|exists:sucursales,id",

            "codigo" => "required|string|unique:productos,codigo",

            "nombre" => "required|string|max:255",

            "descripcion" => "nullable|string",

            "precio_compra" => "required|numeric",

            "precio_venta" => "required|numeric",

            "stock" => "required|integer",

            "stock_minimo" => "nullable|integer"

        ]);



        $producto = Producto::create([

            "negocio_id" => Auth::user()->negocio_id,

            "categoria_id" => $request->categoria_id,

            "sucursal_id" => $request->sucursal_id,

            "codigo" => $request->codigo,

            "nombre" => $request->nombre,

            "descripcion" => $request->descripcion,

            "precio_compra" => $request->precio_compra,

            "precio_venta" => $request->precio_venta,

            "stock" => $request->stock,

            "stock_minimo" => $request->stock_minimo ?? 0

        ]);



        return response()->json([

            "message" => "Producto creado correctamente",

            "data" => $producto

        ], 201);
    }





    // PUT /productos/{id}


    #[OA\Put(
        path: "/api/productos/{id}",
        tags: ["Productos"],
        summary: "Actualizar producto",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del producto",
                schema: new OA\Schema(
                    type: "integer"
                )
            )

        ],

        requestBody: new OA\RequestBody(

            required: true,

            content: new OA\JsonContent(

                properties: [

                    new OA\Property(
                        property: "nombre",
                        type: "string",
                        example: "Mouse inalámbrico"
                    ),

                    new OA\Property(
                        property: "precio_venta",
                        type: "number",
                        example: 80
                    ),

                    new OA\Property(
                        property: "stock",
                        type: "integer",
                        example: 15
                    )

                ]

            )

        ),

        responses: [

            new OA\Response(
                response: 200,
                description: "Producto actualizado correctamente"
            ),

            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            )

        ]
    )]
    public function update(Request $request, $id)
    {

        $producto = Producto::where(

            "negocio_id",

            Auth::user()->negocio_id

        )->find($id);



        if (!$producto) {

            return response()->json([

                "message" => "Producto no encontrado"

            ], 404);
        }

        $request->validate([

            "categoria_id" => "sometimes|exists:categorias,id",

            "sucursal_id" => "sometimes|exists:sucursales,id",

            "codigo" => "sometimes|string|unique:productos,codigo," . $id,

            "nombre" => "sometimes|string|max:255",

            "descripcion" => "nullable|string",

            "precio_compra" => "sometimes|numeric",

            "precio_venta" => "sometimes|numeric",

            "stock" => "sometimes|integer",

            "stock_minimo" => "nullable|integer"

        ]);


        $producto->update([

            "categoria_id" => $request->categoria_id ?? $producto->categoria_id,

            "sucursal_id" => $request->sucursal_id ?? $producto->sucursal_id,

            "codigo" => $request->codigo ?? $producto->codigo,

            "nombre" => $request->nombre ?? $producto->nombre,

            "descripcion" => $request->descripcion ?? $producto->descripcion,

            "precio_compra" => $request->precio_compra ?? $producto->precio_compra,

            "precio_venta" => $request->precio_venta ?? $producto->precio_venta,

            "stock" => $request->stock ?? $producto->stock,

            "stock_minimo" => $request->stock_minimo ?? $producto->stock_minimo

        ]);


        return response()->json([

            "message" => "Producto actualizado correctamente",

            "data" => $producto

        ], 200);
    }

    // DELETE /productos/{id}


    #[OA\Delete(
        path: "/api/productos/{id}",
        tags: ["Productos"],
        summary: "Eliminar producto",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del producto",
                schema: new OA\Schema(
                    type: "integer"
                )
            )

        ],

        responses: [

            new OA\Response(
                response: 200,
                description: "Producto eliminado correctamente"
            ),

            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            )

        ]
    )]
    public function destroy($id)
    {

        $producto = Producto::where(

            "negocio_id",

            Auth::user()->negocio_id

        )->find($id);



        if (!$producto) {

            return response()->json([

                "message" => "Producto no encontrado"

            ], 404);
        }



        $producto->delete();



        return response()->json([

            "message" => "Producto eliminado correctamente"

        ], 200);
    }


    // GET /productos/buscar


    #[OA\Get(
        path: "/api/productos/buscar",
        tags: ["Productos"],
        summary: "Buscar productos por nombre o código",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "texto",
                in: "query",
                required: true,
                description: "Texto a buscar en nombre o código",
                schema: new OA\Schema(
                    type: "string"
                )
            )

        ],

        responses: [

            new OA\Response(
                response: 200,
                description: "Resultado de búsqueda"
            )

        ]
    )]
    public function buscar(Request $request)
    {

        $productos = Producto::with([

            "categoria",
            "sucursal"

        ])
            ->where(

                "negocio_id",

                Auth::user()->negocio_id

            )
            ->where(function ($query) use ($request) {

                $query->where(

                    "nombre",

                    "LIKE",

                    "%" . $request->texto . "%"

                )
                    ->orWhere(

                        "codigo",

                        "LIKE",

                        "%" . $request->texto . "%"

                    );
            })
            ->get();



        return response()->json(

            $productos,

            200

        );
    }


    // GET /productos/{id}/stock


    #[OA\Get(
        path: "/api/productos/{id}/stock",
        tags: ["Productos"],
        summary: "Consultar stock de un producto",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del producto",
                schema: new OA\Schema(
                    type: "integer"
                )
            )

        ],

        responses: [

            new OA\Response(
                response: 200,
                description: "Stock del producto"
            ),

            new OA\Response(
                response: 404,
                description: "Producto no encontrado"
            )

        ]
    )]
    public function stock($id)
    {

        $producto = Producto::select(

            "id",
            "codigo",
            "nombre",
            "stock",
            "stock_minimo"

        )
            ->where(

                "negocio_id",

                Auth::user()->negocio_id

            )
            ->find($id);



        if (!$producto) {

            return response()->json([

                "message" => "Producto no encontrado"

            ], 404);
        }

        return response()->json(

            $producto,

            200

        );
    }
}
