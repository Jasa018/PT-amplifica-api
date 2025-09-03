# Amplifica API

Esta es una API de Laravel diseñada para servir como backend para integraciones con plataformas de e-commerce como Shopify y WooCommerce.

El proyecto está configurado para ejecutarse en un entorno de Docker gestionado por Laravel Sail.

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
    ./vendor/bin/sail up -d
    ```
    *(En Windows, puedes ejecutar `vendor\bin\sail up -d`)*

3.  **Ejecutar las migraciones de la base de datos:**
    La primera vez que inicies el entorno, necesitarás crear la estructura de la base de datos ejecutando las migraciones de Laravel.

    ```bash
    ./vendor/bin/sail artisan migrate
    ```

¡Y eso es todo! La API ahora estará disponible en `http://localhost`.

## Comandos Útiles de Sail

-   **Iniciar el entorno:** `./vendor/bin/sail up -d`
-   **Detener el entorno:** `./vendor/bin/sail down`
-   **Ejecutar comandos de Artisan:** `./vendor/bin/sail artisan <command>`
-   **Ejecutar Composer:** `./vendor/bin/sail composer <command>`
-   **Ejecutar NPM:** `./vendor/bin/sail npm <command>`
-   **Abrir una terminal en el contenedor de la aplicación:** `./vendor/bin/sail shell`

## Configuración de la Base de Datos

El entorno de Sail incluye un contenedor de MySQL. La configuración por defecto es:

-   **Host:** `mysql`
-   **Puerto:** `3306`
-   **Base de datos:** `laravel`
-   **Usuario:** `sail`
-   **Contraseña:** `password`

Puedes conectarte a la base de datos desde tu cliente de SQL preferido usando el puerto `3306` en `localhost`.

## Próximos Pasos

-   Crear las migraciones para las tablas personalizadas (`stores`, `products`, `orders`, etc.).
-   Crear los Modelos de Eloquent para cada tabla.
-   Desarrollar los controladores y rutas de la API.
-   Implementar la lógica de integración con Shopify y WooCommerce.