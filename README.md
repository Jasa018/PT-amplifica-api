# Amplifica API

Esta es una API de Laravel diseñada para servir como backend para integraciones con plataformas de e-commerce como Shopify y WooCommerce.

El proyecto está configurado para ejecutarse en un entorno de Docker.

## Prerrequisitos

- Docker Desktop

Asegúrate de que Docker Desktop esté instalado y en ejecución en tu sistema antes de continuar.

## Instalación y Primer Uso

1.  **Clonar el repositorio (si es necesario):**
    ```bash
    git clone <repository-url>
    cd PT-amplifica-api
    ```

2.  **Construir e iniciar los contenedores:**
    Este comando descargará las imágenes de Docker necesarias, construirá los contenedores y los iniciará en segundo plano.

    ```bash
    docker-compose up -d
    ```

3.  **Instalar dependencias de Composer:**
    Una vez que los contenedores estén en funcionamiento, instala las dependencias de PHP.

    ```bash
    docker-compose exec laravel.test composer install
    ```

4.  **Ejecutar las migraciones de la base de datos:**
    La primera vez que inicies el entorno, necesitarás crear la estructura de la base de datos ejecutando las migraciones de Laravel.

    ```bash
    docker-compose exec laravel.test php artisan migrate
    ```

¡Y eso es todo! La API ahora estará disponible en `http://localhost`.

## Comandos Útiles de Docker Compose

-   **Iniciar el entorno:** `docker-compose up -d`
-   **Detener el entorno:** `docker-compose down`
-   **Ejecutar comandos de Artisan:** `docker-compose exec laravel.test php artisan <command>`
-   **Ejecutar Composer:** `docker-compose exec laravel.test composer <command>`
-   **Ejecutar NPM:** `docker-compose exec laravel.test npm <command>`
-   **Abrir una terminal en el contenedor de la aplicación:** `docker-compose exec laravel.test bash`

## Endpoints de la API

La API proporciona los siguientes endpoints:

- `GET /api/stores` - Lista todas las tiendas.
- `POST /api/stores` - Crea una nueva tienda.
- `GET /api/stores/{store}` - Muestra una tienda específica.
- `PUT /api/stores/{store}` - Actualiza una tienda específica.
- `DELETE /api/stores/{store}` - Elimina una tienda específica.

- `GET /api/products` - Lista todos los productos.
- `POST /api/products` - Crea un nuevo producto.
- `GET /api/products/{product}` - Muestra un producto específico.
- `PUT /api/products/{product}` - Actualiza un producto específico.
- `DELETE /api/products/{product}` - Elimina un producto específico.

- `GET /api/orders` - Lista todas las órdenes.
- `POST /api/orders` - Crea una nueva orden.
- `GET /api/orders/{order}` - Muestra una orden específica.
- `PUT /api/orders/{order}` - Actualiza una orden específica.
- `DELETE /api/orders/{order}` - Elimina una orden específica.

## Documentación de la API (Swagger)

Para generar y visualizar la documentación interactiva de la API utilizando Swagger (OpenAPI), sigue estos pasos:

1.  **Instalar el paquete `l5-swagger`:**
    ```bash
    docker-compose exec laravel.test composer require darkaonline/l5-swagger
    ```

2.  **Publicar la configuración y los assets de Swagger:**
    Esto creará el archivo `config/l5-swagger.php` para personalización.
    ```bash
    docker-compose exec laravel.test php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
    ```

3.  **Añadir anotaciones Swagger a tus controladores y modelos:**
    `l5-swagger` utiliza anotaciones en el código PHP para construir la especificación OpenAPI. Asegúrate de añadir las anotaciones `@OA\Info` en un controlador base (ej. `app/Http/Controllers/Controller.php`) y `@OA\Schema` en tus modelos (ej. `app/Models/User.php`) para definir la estructura de tus datos y endpoints.

4.  **Generar la documentación de Swagger:**
    ```bash
    docker-compose exec laravel.test php artisan l5-swagger:generate
    ```

5.  **Acceder a la interfaz de usuario de Swagger:**
    Una vez generada, la documentación estará disponible en tu navegador en:
    `http://localhost/api/documentation`

## Próximos Pasos

-   Implementar la lógica de integración con Shopify y WooCommerce.
-   Añadir autenticación y autorización a los endpoints de la API.
-   Escribir tests para la API.
