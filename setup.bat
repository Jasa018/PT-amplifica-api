@echo off

echo "Copiando .env.example a .env..."
copy .env.example .env

echo "Instalando dependencias de Composer para crear la carpeta vendor..."
docker run --rm -v "%cd%:/app" -w /app laravelsail/php83-composer:latest composer install

echo "Levantando los contenedores de Docker..."
docker-compose up -d

echo "Esperando a que la base de datos este lista (30 segundos)..."
timeout /t 30 /nobreak > nul

echo "Ejecutando migraciones de la base de datos..."
docker-compose exec laravel.test php artisan migrate

echo "Limpiando la cache de configuracion..."
docker-compose exec laravel.test php artisan config:clear

echo "Creando usuario de prueba..."
docker-compose exec laravel.test php artisan tinker --execute="App\Models\User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('password')])"

echo "Setup finalizado!"
echo "Puedes iniciar sesion con el usuario: test@example.com y la contrasena: password"