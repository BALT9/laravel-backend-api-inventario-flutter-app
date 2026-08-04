<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SucursalController extends Controller
{

    // GET /sucursales
    #[OA\Get(
        path: "/api/sucursales",
        tags: ["Sucursales"],
        summary: "Obtener todas las sucursales",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de sucursales"
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
            Sucursal::all(),
            200
        );
    }


    // GET /sucursales/{id}
    #[OA\Get(
        path: "/api/sucursales/{id}",
        tags: ["Sucursales"],
        summary: "Obtener una sucursal por ID",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la sucursal",
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Sucursal encontrada"
            ),
            new OA\Response(
                response: 404,
                description: "Sucursal no encontrada"
            )
        ]
    )]
    public function show($id)
    {
        $sucursal = Sucursal::find($id);


        if (!$sucursal) {

            return response()->json([
                "message" => "Sucursal no encontrada"
            ], 404);
        }


        return response()->json(
            $sucursal,
            200
        );
    }


    // POST /sucursales
    #[OA\Post(
        path: "/api/sucursales",
        tags: ["Sucursales"],
        summary: "Crear sucursal",
        security: [["sanctum" => []]],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "nombre",
                    "direccion"
                ],

                properties: [

                    new OA\Property(
                        property: "nombre",
                        type: "string",
                        example: "Sucursal Central"
                    ),

                    new OA\Property(
                        property: "direccion",
                        type: "string",
                        example: "Av. Principal #123"
                    ),

                    new OA\Property(
                        property: "telefono",
                        type: "string",
                        example: "70012345"
                    )

                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: "Sucursal creada"
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
            "direccion" => "required|string",
            "telefono" => "nullable|string"

        ]);



        $sucursal = Sucursal::create([

            "nombre" => $request->nombre,
            "direccion" => $request->direccion,
            "telefono" => $request->telefono

        ]);



        return response()->json([

            "message" => "Sucursal creada correctamente",
            "data" => $sucursal

        ], 201);
    }


    // PUT /sucursales/{id}
    #[OA\Put(
        path: "/api/sucursales/{id}",
        tags: ["Sucursales"],
        summary: "Actualizar sucursal",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la sucursal",
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
                        example: "Sucursal Norte"
                    ),

                    new OA\Property(
                        property: "direccion",
                        type: "string",
                        example: "Calle Nueva"
                    ),

                    new OA\Property(
                        property: "telefono",
                        type: "string",
                        example: "75555555"
                    )

                ]

            )

        ),

        responses: [

            new OA\Response(
                response: 200,
                description: "Sucursal actualizada"
            ),

            new OA\Response(
                response: 404,
                description: "Sucursal no encontrada"
            )

        ]
    )]
    public function update(Request $request, $id)
    {

        $sucursal = Sucursal::find($id);



        if (!$sucursal) {

            return response()->json([
                "message" => "Sucursal no encontrada"
            ], 404);
        }



        $request->validate([

            "nombre" => "sometimes|string|max:255",
            "direccion" => "sometimes|string",
            "telefono" => "nullable|string"

        ]);



        $sucursal->nombre =
            $request->nombre ?? $sucursal->nombre;


        $sucursal->direccion =
            $request->direccion ?? $sucursal->direccion;


        $sucursal->telefono =
            $request->telefono ?? $sucursal->telefono;



        $sucursal->save();



        return response()->json([

            "message" => "Sucursal actualizada",
            "data" => $sucursal

        ], 200);
    }


    // DELETE /sucursales/{id}
    #[OA\Delete(
        path: "/api/sucursales/{id}",
        tags: ["Sucursales"],
        summary: "Eliminar sucursal",
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
                description: "Sucursal eliminada"
            ),

            new OA\Response(
                response: 404,
                description: "Sucursal no encontrada"
            )

        ]
    )]
    public function destroy($id)
    {

        $sucursal = Sucursal::find($id);



        if (!$sucursal) {

            return response()->json([
                "message" => "Sucursal no encontrada"
            ], 404);
        }



        $sucursal->delete();



        return response()->json([

            "message" => "Sucursal eliminada correctamente"

        ], 200);
    }


    // GET /sucursales-buscar
    #[OA\Get(
        path: "/api/sucursales-buscar",
        tags: ["Sucursales"],
        summary: "Buscar sucursal por nombre",
        security: [["sanctum" => []]],

        parameters: [

            new OA\Parameter(
                name: "texto",
                in: "query",
                required: true,
                description: "Texto de búsqueda",
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

        $sucursales = Sucursal::where(
            "nombre",
            "LIKE",
            "%" . $request->texto . "%"
        )->get();



        return response()->json(
            $sucursales,
            200
        );
    }
}
