# Amplifica - Prueba Técnica API

Esta aplicación es una API desarrollada en Laravel que se conecta a tiendas de e-commerce (Shopify y WooCommerce) para obtener y gestionar datos de productos y pedidos.

## Requisitos

- Docker
- Composer

## Instalación y Puesta en Marcha

1.  **Clonar el Repositorio**
    ```bash
    git clone <url-del-repositorio>
    cd PT-amplifica-api
    ```

2.  **Configuración del Entorno**
    Copia el archivo de ejemplo de variables de entorno y ajústalo según sea necesario.
    ```bash
    cp .env.example .env
    ```
    Asegúrate de llenar las variables de entorno como se describe en la sección de "Configuración".

3.  **Levantar los Contenedores**
    Este proyecto usa Docker. Para iniciar los servicios (aplicación, base de datos), ejecuta:
    ```bash
    docker-compose up -d
    ```
    *(Alternativamente, si tienes Laravel Sail configurado, puedes usar `./vendor/bin/sail up -d`)*

4.  **Instalar Dependencias**
    Instala las dependencias de PHP a través de Composer.
    ```bash
    docker-compose exec laravel.test composer install
    ```

5.  **Ejecutar Migraciones**
    Crea la estructura de la base de datos ejecutando las migraciones de Laravel.
    ```bash
    docker-compose exec laravel.test php artisan migrate
    ```

6.  **Crear Usuario para Pruebas**
    Para poder iniciar sesión en la aplicación, necesitas crear un usuario. Puedes hacerlo usando Artisan Tinker:
    ```bash
    docker-compose exec laravel.test php artisan tinker
    ```
    Una vez dentro de Tinker, ejecuta el siguiente comando para crear un usuario de prueba (puedes cambiar el email y la contraseña):
    ```php
    App\Models\User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('password')]);
    ```
    Luego, escribe `exit` para salir de Tinker.

7.  **Limpiar Caché (Importante)**
    Después de configurar tus variables de entorno, limpia la caché de configuración para que la aplicación las cargue correctamente.
    ```bash
    docker-compose exec laravel.test php artisan config:clear
    ```

La aplicación ahora debería estar corriendo en `http://localhost`.

## Configuración (.env)

Debes configurar las siguientes variables en tu archivo `.env` para conectar las plataformas de e-commerce.

### Shopify

Las credenciales se obtienen creando una **Aplicación Personalizada (Custom App)** en el panel de administración de tu tienda Shopify.

-   `SHOPIFY_STORE_URL`: La URL de tu tienda, sin `https://`. (Ej: `tu-tienda.myshopify.com`)
-   `SHOPIFY_ADMIN_ACCESS_TOKEN`: El "Token de acceso de la API de admin" que te proporciona Shopify al configurar los permisos de la API.

### WooCommerce

Las credenciales se obtienen en tu panel de WordPress, en **WooCommerce > Ajustes > Avanzado > API REST**.

-   `WOOCOMMERCE_STORE_URL`: La URL completa de tu sitio WordPress. (Ej: `https://tu-tienda.com`)
-   `WOOCOMMERCE_CONSUMER_KEY`: La "Clave de cliente" generada por WooCommerce.
-   `WOOCOMMERCE_CONSUMER_SECRET`: La "Clave secreta del cliente" generada por WooCommerce.

## Documentación de la API (Swagger)

Este proyecto utiliza [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) para generar la documentación de la API en formato OpenAPI.

Para generar o actualizar la documentación, ejecuta el siguiente comando:
```bash
docker-compose exec laravel.test php artisan l5-swagger:generate
```

Una vez generada y con el servidor en marcha, puedes acceder a la documentación interactiva en la siguiente URL:
[http://localhost/api/documentation](http://localhost/api/documentation)

## Pruebas Unitarias y de Integración

El proyecto utiliza PHPUnit para las pruebas. Puedes ejecutar todas las pruebas (unitarias y de integración) con el siguiente comando:

```bash
docker-compose exec laravel.test php artisan test
```

Las pruebas unitarias se encuentran en `tests/Unit` y se enfocan en componentes individuales de la aplicación. Las pruebas de integración se encuentran en `tests/Feature` y verifican la interacción entre diferentes partes del sistema, incluyendo las rutas de la API y la base de datos.

## Base de Datos

La base de datos de la aplicación está compuesta por las siguientes tablas principales:

-   `users`: Almacena la información de los usuarios del sistema para autenticación.
-   `stores`: Contiene los detalles de las tiendas de e-commerce (Shopify, WooCommerce) conectadas a la API.
-   `products`: Guarda los productos obtenidos de las tiendas de e-commerce.
-   `orders`: Almacena los pedidos de las tiendas de e-commerce.
-   `order_items`: Detalla los productos individuales dentro de cada pedido.
-   `logs`: Registra eventos y errores de la aplicación, con niveles de `info`, `warning` y `error`.
-   `personal_access_tokens`: Utilizada por Laravel Sanctum para la autenticación de la API.
-   `cache`: Tabla de caché de Laravel.
-   `jobs`: Tabla para la gestión de colas de tareas de Laravel.

## Flujo del Sistema

### Stores (Tiendas)

El sistema permite la gestión de diferentes tiendas de e-commerce. Cada `Store` se configura con las credenciales de Shopify o WooCommerce, lo que habilita la sincronización de datos. Las tiendas son la entidad central para agrupar productos y pedidos.

### Products (Productos)

Los productos son obtenidos de las tiendas de e-commerce configuradas. La API puede sincronizar y almacenar información detallada de los productos, permitiendo su consulta y exportación. Esto incluye datos como nombre, descripción, precio, etc.

### Orders (Pedidos)

Los pedidos son sincronizados desde las tiendas de e-commerce. Cada `Order` incluye `OrderItems` que representan los productos individuales dentro de ese pedido. La API permite consultar y exportar los pedidos para análisis.

### Analytics (Análisis)

La sección de `Analytics` proporciona una visión consolidada de los datos de productos y pedidos. Permite a los usuarios obtener métricas y reportes sobre el rendimiento de sus tiendas, como ventas, productos más vendidos, etc., basándose en la información sincronizada en la base de datos.

## API Endpoints

A continuación se listan los endpoints disponibles en la API.

### Autenticación

| Método | URL | Descripción | Autenticación Requerida |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | Inicia sesión con un usuario y devuelve un token. | No |
| `POST` | `/api/logout`| Cierra la sesión y revoca el token actual. | Sí |

### Shopify

| Método | URL | Descripción | Autenticación Requerida |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/shopify/test` | Obtiene una lista de productos en formato JSON. | No |
| `GET` | `/api/shopify/orders` | Obtiene los pedidos de los últimos 30 días en JSON. | No |
| `GET` | `/api/shopify/products/export` | Descarga un archivo CSV con la lista de productos. | No |
| `GET` | `/api/shopify/orders/export` | Descarga un archivo CSV con los pedidos recientes. | No |

### WooCommerce

| Método | URL | Descripción | Autenticación Requerida |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/woocommerce/products` | Obtiene una lista de productos en formato JSON. | No |
| `GET` | `/api/woocommerce/orders` | Obtiene los pedidos de los últimos 30 días en JSON. | No |
| `GET` | `/api/woocommerce/products/export` | Descarga un archivo CSV con la lista de productos. | No |
| `GET` | `/api/woocommerce/orders/export` | Descarga un archivo CSV con los pedidos recientes. | No |