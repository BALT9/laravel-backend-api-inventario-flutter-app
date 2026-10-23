<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Inventario",
    version: "1.0.0",
    description: "Documentación de la API del sistema de inventario"
)]
#[OA\Server(
    url: "/",
    description: "Servidor actual"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class OpenApi {}
