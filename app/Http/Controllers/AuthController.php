<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    // POST /login

    #[OA\Post(
        path: "/api/login",
        tags: ["Autenticación"],
        summary: "Iniciar sesión",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "email",
                    "password"
                ],
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        example: "juan@mail.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        example: "1234567890"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Inicio de sesión exitoso"
            ),
            new OA\Response(
                response: 401,
                description: "Credenciales incorrectas"
            )
        ]
    )]
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = User::find(Auth::id());

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesion exitoso',
            'user' => $user,
            'token' => $token
        ]);
    }


    // POST /logout

    #[OA\Post(
        path: "/api/logout",
        tags: ["Autenticación"],
        summary: "Cerrar sesión",
        security: [
            ["sanctum" => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Sesión cerrada correctamente"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesion cerrada correctamente'
        ]);
    }


    // GET /profile

    #[OA\Get(
        path: "/api/profile",
        tags: ["Autenticación"],
        summary: "Obtener perfil del usuario autenticado",
        security: [
            ["sanctum" => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Datos del usuario autenticado"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }


    // PUT /change-password

    #[OA\Put(
        path: "/api/change-password",
        tags: ["Autenticación"],
        summary: "Cambiar contraseña",
        security: [
            ["sanctum" => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "current_password",
                    "new_password",
                    "new_password_confirmation"
                ],
                properties: [
                    new OA\Property(
                        property: "current_password",
                        type: "string",
                        example: "1234567890"
                    ),
                    new OA\Property(
                        property: "new_password",
                        type: "string",
                        example: "987654321"
                    ),
                    new OA\Property(
                        property: "new_password_confirmation",
                        type: "string",
                        example: "987654321"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Contraseña actualizada correctamente"
            ),
            new OA\Response(
                response: 400,
                description: "Contraseña actual incorrecta"
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado"
            )
        ]
    )]
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }
}
