<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class NegocioController extends Controller
{

    // GET /negocios

    #[OA\Get(
        path: "/api/negocios",
        tags: ["Negocios"],
        summary: "Obtener negocios del usuario autenticado",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de negocios"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function index()
    {
        $user = Auth::user();

        return response()->json(
            Negocio::where(
                'id',
                $user->negocio_id
            )->get(),
            200
        );
    }



    // GET /negocios/{id}

    #[OA\Get(
        path: "/api/negocios/{id}",
        tags: ["Negocios"],
        summary: "Obtener un negocio por ID",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del negocio",
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Negocio encontrado"
            ),
            new OA\Response(
                response: 404,
                description: "Negocio no encontrado"
            )
        ]
    )]
    public function show($id)
    {
        $user = Auth::user();

        $negocio = Negocio::where(
            'id',
            $user->negocio_id
        )->find($id);


        if (!$negocio) {

            return response()->json([
                'message' => 'Negocio no encontrado'
            ], 404);
        }


        return response()->json($negocio, 200);
    }



    // POST /negocios

    #[OA\Post(
        path: "/api/negocios",
        tags: ["Negocios"],
        summary: "Crear un nuevo negocio",
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
                        example: "Tienda Central"
                    ),

                    new OA\Property(
                        property: "direccion",
                        type: "string",
                        example: "Av. Principal 123"
                    ),

                    new OA\Property(
                        property: "telefono",
                        type: "string",
                        example: "70000000"
                    ),

                    new OA\Property(
                        property: "logo",
                        type: "string",
                        example: "logo.png"
                    ),

                    new OA\Property(
                        property: "estado",
                        type: "integer",
                        example: 1
                    )

                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Negocio creado"
            )
        ]
    )]
    public function store(Request $request)
    {

        $request->validate([

            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',
            'logo' => 'nullable|string',
            'estado' => 'boolean'

        ]);


        $negocio = Negocio::create([

            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'logo' => $request->logo,
            'estado' => $request->estado ?? true

        ]);


        return response()->json(
            $negocio,
            201
        );
    }





    // PUT /negocios/{id}

    #[OA\Put(
        path: "/api/negocios/{id}",
        tags: ["Negocios"],
        summary: "Actualizar negocio",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del negocio",
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Negocio actualizado"
            ),
            new OA\Response(
                response: 404,
                description: "Negocio no encontrado"
            )
        ]
    )]
    public function update(Request $request, $id)
    {

        $user = Auth::user();


        $negocio = Negocio::where(
            'id',
            $user->negocio_id
        )->find($id);



        if (!$negocio) {

            return response()->json([
                'message' => 'Negocio no encontrado'
            ], 404);
        }


        $request->validate([

            'nombre' => 'sometimes|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',
            'logo' => 'nullable|string',
            'estado' => 'boolean'

        ]);


        $negocio->update(
            $request->all()
        );


        return response()->json(
            $negocio,
            200
        );
    }





    // DELETE /negocios/{id}

    #[OA\Delete(
        path: "/api/negocios/{id}",
        tags: ["Negocios"],
        summary: "Eliminar negocio",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del negocio",
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Negocio eliminado"
            ),
            new OA\Response(
                response: 404,
                description: "Negocio no encontrado"
            )
        ]
    )]
    public function destroy($id)
    {

        $user = Auth::user();


        $negocio = Negocio::where(
            'id',
            $user->negocio_id
        )->find($id);



        if (!$negocio) {

            return response()->json([
                'message' => 'Negocio no encontrado'
            ], 404);
        }


        $negocio->delete();


        return response()->json([
            'message' => 'Negocio eliminado correctamente'
        ], 200);
    }
}
