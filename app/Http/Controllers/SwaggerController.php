<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="API Inventario",
 *     version="1.0.0",
 *     description="Documentación de la API del sistema de inventario"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController extends Controller
{
    //
}
