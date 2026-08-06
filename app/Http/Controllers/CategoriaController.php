<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class CategoriaController extends Controller
{

    // GET /categorias

    #[OA\Get(
        path: "/api/categorias",
        tags: ["Categorías"],
        summary: "Obtener todas las categorías del negocio autenticado",
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

            Categoria::where(
                'negocio_id',
                Auth::user()->negocio_id
            )->get(),

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

        $categoria = Categoria::where(

            'negocio_id',

            Auth::user()->negocio_id

        )->find($id);



        if (!$categoria) {

            return response()->json([

                "message" => "Categoría no encontrada"

            ], 404);
        }



        return response()->json($categoria, 200);
    }





    // POST /categorias

    #[OA\Post(
        path: "/api/categorias",
        tags: ["Categorías"],
        summary: "Crear una categoría",
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
                description: "Categoría creada correctamente"
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

            "negocio_id" => Auth::user()->negocio_id,

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
                description: "ID de la categoría",
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
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

        $categoria = Categoria::where(

            'negocio_id',

            Auth::user()->negocio_id

        )->find($id);



        if (!$categoria) {

            return response()->json([

                "message" => "Categoría no encontrada"

            ], 404);
        }



        $request->validate([

            "nombre" => "sometimes|string|max:255",

            "descripcion" => "nullable|string"

        ]);



        $categoria->update([

            "nombre" => $request->nombre ?? $categoria->nombre,

            "descripcion" => $request->descripcion ?? $categoria->descripcion

        ]);



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
                description: "ID de la categoría",
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Categoría eliminada correctamente"
            ),
            new OA\Response(
                response: 404,
                description: "Categoría no encontrada"
            )
        ]
    )]
    public function destroy($id)
    {

        $categoria = Categoria::where(

            'negocio_id',

            Auth::user()->negocio_id

        )->find($id);



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





    // GET /categorias/buscar

    #[OA\Get(
        path: "/api/categorias/buscar",
        tags: ["Categorías"],
        summary: "Buscar categorías por nombre",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "texto",
                in: "query",
                required: true,
                description: "Texto para buscar en el nombre",
                schema: new OA\Schema(
                    type: "string"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de categorías encontradas"
            )
        ]
    )]
    public function buscar(Request $request)
    {

        $categorias = Categoria::where(

            'negocio_id',

            Auth::user()->negocio_id

        )

            ->where(

                "nombre",

                "LIKE",

                "%" . $request->texto . "%"

            )

            ->get();

        return response()->json(

            $categorias,

            200

        );
    }
}
