<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    // GET /users
    #[OA\Get(
        path: "/api/users",
        tags: ["Usuarios"],
        summary: "Obtener todos los usuarios",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de usuarios"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function index()
    {
        return response()->json(User::all(), 200);
    }


    // GET /users/{id}
    #[OA\Get(
        path: "/api/users/{id}",
        tags: ["Usuarios"],
        summary: "Obtener un usuario por ID",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del usuario",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Usuario encontrado"
            ),
            new OA\Response(
                response: 404,
                description: "Usuario no encontrado"
            )
        ]
    )]
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json($user, 200);
    }


    // POST /users
    #[OA\Post(
        path: "/api/users",
        tags: ["Usuarios"],
        summary: "Crear usuario",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "nombre",
                    "role",
                    "email",
                    "password",
                    "estado"
                ],
                properties: [
                    new OA\Property(
                        property: "nombre",
                        type: "string",
                        example: "Juan Perez"
                    ),
                    new OA\Property(
                        property: "role",
                        type: "string",
                        example: "admin"
                    ),
                    new OA\Property(
                        property: "email",
                        type: "string",
                        example: "juan@mail.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        example: "1234567890"
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
                description: "Usuario creado"
            )
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'role' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'estado' => 'required'
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'role' => $request->role,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'estado' => $request->estado,
        ]);

        return response()->json($user, 201);
    }


    // PUT /users/{id}
    #[OA\Put(
        path: "/api/users/{id}",
        tags: ["Usuarios"],
        summary: "Actualizar usuario",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Usuario actualizado"
            )
        ]
    )]
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'role' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|min:6',
            'estado' => 'sometimes'
        ]);

        $user->nombre = $request->nombre ?? $user->nombre;
        $user->role = $request->role ?? $user->role;
        $user->email = $request->email ?? $user->email;
        $user->estado = $request->estado ?? $user->estado;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json($user, 200);
    }


    // DELETE /users/{id}
    #[OA\Delete(
        path: "/api/users/{id}",
        tags: ["Usuarios"],
        summary: "Eliminar usuario",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Usuario eliminado"
            )
        ]
    )]
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }
}
