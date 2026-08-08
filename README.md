# API REST - Sistema de Gestión de Inventarios

API REST desarrollada con **Laravel 13** para gestionar productos, usuarios, sucursales, categorías, compras, ventas y movimientos de inventario.

La API está diseñada con una arquitectura desacoplada y preparada para ser consumida por aplicaciones móviles, aplicaciones web u otros clientes mediante HTTP y JSON.

---

## 🚀 Tecnologías

* **Laravel 13**
* **PHP 8.4+**
* **PostgreSQL**
* **Laravel Sanctum**
* **Eloquent ORM**
* **REST API**
* **OpenAPI / Swagger**

---

# 🏗️ Arquitectura

La aplicación utiliza una arquitectura basada en API REST:

```text
Cliente
   │
   │ HTTP / JSON
   ▼
Laravel REST API
   │
   │ Eloquent ORM
   ▼
PostgreSQL
```

Toda la lógica de negocio relacionada con el inventario se encuentra en el backend.

Los clientes solamente necesitan consumir los endpoints HTTP proporcionados por la API.

---

# 🔐 Autenticación

La autenticación se implementa utilizando **Laravel Sanctum**.

Los endpoints protegidos requieren un token Bearer:

```http
Authorization: Bearer TU_TOKEN
```

## Endpoints

```text
POST /api/login
POST /api/register
POST /api/logout
GET  /api/profile
PUT  /api/change-password
```

---

# 🏢 Multiempresa

La API utiliza una arquitectura multiempresa.

Cada usuario pertenece a un negocio mediante:

```text
negocio_id
```

Los recursos operativos están asociados al negocio correspondiente.

```text
Negocio
│
├── Usuarios
├── Sucursales
├── Categorías
├── Productos
├── Compras
├── Ventas
├── Movimientos
└── Notas
```

Los usuarios autenticados solamente pueden acceder a información perteneciente a su propio negocio.

---

# 📦 Módulos

## Usuarios

Administración de usuarios del negocio.

```http
GET    /api/users
GET    /api/users/{id}
POST   /api/users
PUT    /api/users/{id}
DELETE /api/users/{id}
```

Funciones:

* Crear usuario.
* Consultar usuarios.
* Actualizar usuario.
* Eliminar usuario.
* Activar/Inactivar usuario.
* Buscar usuarios.

---

# 🏪 Sucursales

Administración de sucursales.

```http
GET    /api/sucursales
GET    /api/sucursales/{id}
POST   /api/sucursales
PUT    /api/sucursales/{id}
DELETE /api/sucursales/{id}
GET    /api/sucursales-buscar
```

---

# 🗂️ Categorías

Administración de categorías de productos.

```http
GET    /api/categorias
GET    /api/categorias/{id}
POST   /api/categorias
PUT    /api/categorias/{id}
DELETE /api/categorias/{id}
GET    /api/categorias-buscar
```

---

# 📱 Productos

Administración de productos e inventario.

```http
GET    /api/productos
GET    /api/productos/{id}
POST   /api/productos
PUT    /api/productos/{id}
DELETE /api/productos/{id}
GET    /api/productos-buscar
GET    /api/productos/{id}/stock
```

Cada producto contiene información como:

* Código.
* Nombre.
* Descripción.
* Categoría.
* Sucursal.
* Precio de compra.
* Precio de venta.
* Stock.
* Stock mínimo.

El código del producto debe ser único dentro del negocio.

---

# 🛒 Compras

Permite registrar el ingreso de productos al inventario.

```http
GET  /api/compras
GET  /api/compras/{id}
POST /api/compras
```

Una compra contiene:

```text
Compra
│
├── Usuario
├── Sucursal
├── Fecha
├── Total
├── Observación
│
└── Detalles
    ├── Producto
    ├── Cantidad
    ├── Precio
    └── Subtotal
```

### Proceso de registro

Cuando se registra una compra:

1. Se valida la sucursal.
2. Se validan los productos.
3. Se calcula el subtotal.
4. Se calcula el total.
5. Se registra la compra.
6. Se registran los detalles.
7. Se aumenta el stock.
8. Se registra un movimiento de inventario.

Las operaciones se realizan dentro de una transacción de base de datos.

---

# 💰 Ventas

Permite registrar la salida de productos del inventario.

```http
GET  /api/ventas
GET  /api/ventas/{id}
POST /api/ventas
```

Una venta contiene:

```text
Venta
│
├── Usuario
├── Sucursal
├── Fecha
├── Total
├── Observación
│
└── Detalles
    ├── Producto
    ├── Cantidad
    ├── Precio
    └── Subtotal
```

### Proceso de registro

Cuando se registra una venta:

1. Se valida la sucursal.
2. Se validan los productos.
3. Se verifica el stock disponible.
4. Se calcula el subtotal.
5. Se calcula el total.
6. Se registra la venta.
7. Se registran los detalles.
8. Se disminuye el stock.
9. Se registra un movimiento de inventario.

### Regla de stock

La API no permite vender una cantidad superior al stock disponible.

---

# 🔄 Movimientos

Registra automáticamente los cambios realizados sobre el inventario.

Tipos:

```text
entrada
salida
ajuste
```

Endpoint:

```http
GET /api/movimientos
```

Permite filtrar por:

* Producto.
* Tipo.
* Fecha.
* Fecha desde.
* Fecha hasta.

Ejemplos:

```http
GET /api/movimientos?producto_id=1
```

```http
GET /api/movimientos?tipo=entrada
```

```http
GET /api/movimientos?fecha=2026-08-08
```

```http
GET /api/movimientos?fecha_desde=2026-08-01&fecha_hasta=2026-08-08
```

Cada movimiento almacena:

```text
producto
usuario
tipo
cantidad
stock_anterior
stock_actual
created_at
```

Los movimientos de compra y venta se generan automáticamente.

---

# 📝 Notas

La API también contempla la administración de notas.

```http
GET    /api/notas
POST   /api/notas
PUT    /api/notas/{id}
DELETE /api/notas/{id}
```

---

# 🗄️ Base de Datos

La API utiliza PostgreSQL.

Principales tablas:

```text
negocios
users
sucursales
categorias
productos
compras
compra_detalles
ventas
venta_detalles
movimientos
notas
```

## Relaciones principales

```text
Negocio
│
├── Users
├── Sucursales
├── Categorias
├── Productos
├── Compras
├── Ventas
├── Movimientos
└── Notas
```

```text
Compra
└── CompraDetalle
    └── Producto
```

```text
Venta
└── VentaDetalle
    └── Producto
```

---

# 📖 Swagger / OpenAPI

La API cuenta con documentación mediante **Swagger/OpenAPI**.

Para generar o actualizar la documentación:

```bash
php artisan l5-swagger:generate
```

Iniciar el servidor:

```bash
php artisan serve
```

La documentación estará disponible en:

```text
http://127.0.0.1:8000/api/documentation
```

Swagger permite:

* Consultar todos los endpoints.
* Ver parámetros.
* Probar solicitudes.
* Ver respuestas.
* Autenticar mediante Bearer Token.
* Consultar los endpoints protegidos.

---

# ⚙️ Instalación

## 1. Clonar el proyecto

```bash
git clone <URL_DEL_REPOSITORIO>
```

Entrar al proyecto:

```bash
cd backend-api-inventario-flutter-app
```

---

## 2. Instalar dependencias

```bash
composer install
```

---

## 3. Configurar `.env`

Copiar `.env.example`:

```bash
cp .env.example .env
```

Configurar PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nombre_base_datos
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

---

## 4. Generar la clave de Laravel

```bash
php artisan key:generate
```

---

## 5. Ejecutar migraciones

```bash
php artisan migrate
```

Para reconstruir completamente la base de datos:

```bash
php artisan migrate:fresh
```

---

## 6. Generar Swagger

```bash
php artisan l5-swagger:generate
```

---

## 7. Ejecutar la API

```bash
php artisan serve
```

La API estará disponible en:

```text
http://127.0.0.1:8000
```

---

# 🧪 Pruebas

La API puede probarse utilizando:

* Swagger UI.
* Postman.
* Thunder Client.
* REST Client para Visual Studio Code.

Ejemplo:

```http
GET http://127.0.0.1:8000/api/productos
Accept: application/json
Authorization: Bearer TU_TOKEN
```

---

# 📋 Reglas principales

### Productos

* Código obligatorio y único dentro del negocio.
* Nombre obligatorio.
* Precio de compra mayor que cero.
* Precio de venta mayor que cero.
* Stock mínimo mayor o igual a cero.

### Compras

* Debe existir al menos un producto.
* Cantidad mayor que cero.
* Precio mayor que cero.
* Aumenta el stock.
* Genera movimiento de entrada.

### Ventas

* Debe existir al menos un producto.
* Cantidad mayor que cero.
* Precio mayor que cero.
* No permite vender más stock del disponible.
* Disminuye el stock.
* Genera movimiento de salida.

### Usuarios

* Email único.
* Contraseña cifrada.
* Usuario activo para iniciar sesión.

---

# 🔒 Seguridad

La API implementa:

* Laravel Sanctum.
* Autenticación mediante Bearer Token.
* Contraseñas cifradas.
* Validación de datos.
* Protección mediante `auth:sanctum`.
* Separación de información mediante `negocio_id`.
* Validación de pertenencia de recursos al negocio autenticado.
* Transacciones para operaciones críticas de inventario.

---

# 📌 Estado del proyecto

### Implementado

* [x] API REST
* [x] Laravel 12
* [x] PostgreSQL
* [x] Autenticación con Sanctum
* [x] Usuarios
* [x] Negocios
* [x] Sucursales
* [x] Categorías
* [x] Productos
* [x] Compras
* [x] Detalles de compras
* [x] Ventas
* [x] Detalles de ventas
* [x] Actualización automática de stock
* [x] Movimientos de inventario
* [x] Filtros de movimientos
* [x] Paginación
* [x] Documentación Swagger/OpenAPI

### Pendiente

* [ ] Completar módulo de notas.
* [ ] Implementar dashboard mediante endpoints.
* [ ] Pruebas automatizadas.
* [ ] Mejorar manejo global de excepciones.
* [ ] Completar documentación de todos los endpoints.
* [ ] Preparar despliegue a producción.

---

# 👨‍💻 Autor

**Sistema de Gestión de Inventarios**

API REST desarrollada con:

```text
Laravel 12
PHP 8.4+
PostgreSQL
Laravel Sanctum
Eloquent ORM
OpenAPI / Swagger
```
