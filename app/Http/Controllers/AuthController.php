<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{


    #[OA\Post(
        path: "/api/register",
        tags: ["Autenticación"],
        summary: "Registrar un nuevo negocio y usuario administrador",
        description: "Crea un negocio, crea el usuario administrador asociado y genera un token de autenticación.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    "negocio",
                    "nombre",
                    "email",
                    "password"
                ],
                properties: [

                    new OA\Property(
                        property: "negocio",
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
                        property: "nombre",
                        type: "string",
                        example: "Juan Perez"
                    ),

                    new OA\Property(
                        property: "email",
                        type: "string",
                        format: "email",
                        example: "juan@mail.com"
                    ),

                    new OA\Property(
                        property: "password",
                        type: "string",
                        format: "password",
                        example: "123456789"
                    )

                ]
            )
        ),
        responses: [

            new OA\Response(
                response: 201,
                description: "Registro exitoso"
            ),

            new OA\Response(
                response: 422,
                description: "Error de validación"
            ),

            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )

        ]
    )]
    public function register(Request $request)
    {
        $request->validate([

            // Negocio
            'negocio' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',


            // Usuario
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'

        ]);

        DB::beginTransaction();


        try {

            $negocio = Negocio::create([

                'nombre' => $request->negocio,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'estado' => true

            ]);


            $user = User::create([

                'negocio_id' => $negocio->id,
                'nombre' => $request->nombre,
                'role' => 'admin',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => true

            ]);


            $token = $user
                ->createToken('api-token')
                ->plainTextToken;


            DB::commit();


            return response()->json([

                'message' => 'Registro exitoso',
                'negocio' => $negocio,
                'user' => $user,
                'token' => $token

            ], 201);
        } catch (\Exception $e) {


            DB::rollBack();


            return response()->json([

                'message' => 'Error al registrar',

                'error' => $e->getMessage()

            ], 500);
        }
    }

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


        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->estado) {

            Auth::logout();

            return response()->json([
                'message' => 'Usuario inactivo'
            ], 403);
        }


        $token = $user
            ->createToken('api-token')
            ->plainTextToken;


        return response()->json([

            'message' => 'Inicio de sesión exitoso',

            'user' => $user->load('negocio'),

            'token' => $token

        ], 200);
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
                description: "Sesion cerrada correctamente"
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
