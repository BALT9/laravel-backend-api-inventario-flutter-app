<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CategoriaController extends Controller
{

    // GET /categorias
    #[OA\Get(
        path: "/api/categorias",
        tags: ["Categorías"],
        summary: "Obtener todas las categorías",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de categorías"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function index()
    {
        return response()->json(
            Categoria::all(),
            200
        );
    }


    // GET /categorias/{id}
    #[OA\Get(
        path: "/api/categorias/{id}",
        tags: ["Categorías"],
        summary: "Obtener categoría por ID",
        security: [["sanctum" => []]],
        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la categoría",
                schema: new OA\Schema(
                    type: "integer"
                )
            )

        ],
        responses: [

            new OA\Response(
                response: 200,
                description: "Categoría encontrada"
            ),

            new OA\Response(
                response: 404,
                description: "Categoría no encontrada"
            )

        ]
    )]
    public function show($id)
    {

        $categoria = Categoria::find($id);


        if (!$categoria) {

            return response()->json([
                "message" => "Categoría no encontrada"
            ], 404);
        }


        return response()->json(
            $categoria,
            200
        );
    }


    // POST /categorias
    #[OA\Post(
        path: "/api/categorias",
        tags: ["Categorías"],
        summary: "Crear categoría",
        security: [["sanctum" => []]],

        requestBody: new OA\RequestBody(
            required: true,

            content: new OA\JsonContent(

                required: [
                    "nombre"
                ],

                properties: [

                    new OA\Property(
                        property: "nombre",
                        type: "string",
                        example: "Bebidas"
                    ),

                    new OA\Property(
                        property: "descripcion",
                        type: "string",
                        example: "Productos líquidos"
                    )

                ]

            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: "Categoría creada"
            ),

            new OA\Response(
                response: 422,
                description: "Error de validación"
            )

        ]
    )]
    public function store(Request $request)
    {

        $request->validate([

            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string"

        ]);



        $categoria = Categoria::create([

            "nombre" => $request->nombre,
            "descripcion" => $request->descripcion

        ]);



        return response()->json([

            "message" => "Categoría creada correctamente",
            "data" => $categoria

        ], 201);
    }



    // PUT /categorias/{id}
    #[OA\Put(
        path: "/api/categorias/{id}",
        tags: ["Categorías"],
        summary: "Actualizar categoría",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(

                name: "id",
                in: "path",
                required: true,

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
                        example: "Electrónicos"
                    ),

                    new OA\Property(
                        property: "descripcion",
                        type: "string",
                        example: "Productos tecnológicos"
                    )

                ]

            )

        ),

        responses: [

            new OA\Response(
                response: 200,
                description: "Categoría actualizada"
            ),

            new OA\Response(
                response: 404,
                description: "Categoría no encontrada"
            )

        ]

    )]
    public function update(Request $request, $id)
    {

        $categoria = Categoria::find($id);



        if (!$categoria) {

            return response()->json([
                "message" => "Categoría no encontrada"
            ], 404);
        }



        $request->validate([

            "nombre" => "sometimes|string|max:255",
            "descripcion" => "nullable|string"

        ]);



        $categoria->nombre =
            $request->nombre ?? $categoria->nombre;


        $categoria->descripcion =
            $request->descripcion ?? $categoria->descripcion;



        $categoria->save();



        return response()->json([

            "message" => "Categoría actualizada",
            "data" => $categoria

        ], 200);
    }


    // DELETE /categorias/{id}
    #[OA\Delete(
        path: "/api/categorias/{id}",
        tags: ["Categorías"],
        summary: "Eliminar categoría",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,

                schema: new OA\Schema(
                    type: "integer"
                )
            )

        ],

        responses: [

            new OA\Response(
                response: 200,
                description: "Categoría eliminada"
            ),

            new OA\Response(
                response: 404,
                description: "Categoría no encontrada"
            )

        ]
    )]
    public function destroy($id)
    {

        $categoria = Categoria::find($id);



        if (!$categoria) {

            return response()->json([
                "message" => "Categoría no encontrada"
            ], 404);
        }



        $categoria->delete();



        return response()->json([

            "message" => "Categoría eliminada correctamente"

        ], 200);
    }


    // GET /categorias-buscar
    #[OA\Get(
        path: "/api/categorias-buscar",
        tags: ["Categorías"],
        summary: "Buscar categorías por nombre",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(

                name: "texto",
                in: "query",
                required: true,

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

        $categorias = Categoria::where(

            "nombre",
            "LIKE",
            "%" . $request->texto . "%"

        )->get();



        return response()->json(
            $categorias,
            200
        );
    }
}
