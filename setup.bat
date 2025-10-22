@echo off

echo "Copiando .env.example a .env..."
copy .env.example .env

echo "Instalando dependencias de Composer para crear la carpeta vendor..."
docker run --rm -v "%cd%:/app" -w /app laravelsail/php83-composer:latest composer install

echo "Levantando los contenedores de Docker..."
docker-compose up -d

echo "Esperando a que la base de datos este lista..."
:loop
FOR /f "tokens=*" %%g IN ('docker inspect --format "{{.State.Health.Status}}" pt-amplifica-api-mysql-1') do (SET status=%%g)
IF NOT "%status%"=="healthy" (
    timeout /t 5 /nobreak > nul
    GOTO loop
)
echo "La base de datos esta lista!"

echo "Ejecutando migraciones de la base de datos..."
docker-compose exec laravel.test php artisan migrate:fresh --seed

echo "Limpiando la cache de configuracion..."
docker-compose exec laravel.test php artisan config:clear



echo "Setup finalizado!"
echo "Puedes iniciar sesion con el usuario: test@example.com y la contrasena: password"