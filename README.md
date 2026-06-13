# Top Tennis

Sistema web para administrar reservas de canchas de tenis. El proyecto esta construido con Laravel, Breeze, Tailwind CSS y Vite.

## Objetivo

Top Tennis permite que los clientes reserven canchas disponibles y que el administrador controle canchas, tarifas, pagos y asistencia.

## Funciones principales

- Registro e inicio de sesion de usuarios.
- Panel de cliente para buscar canchas por fecha, hora y duracion.
- Reserva de canchas con pago por Yape o efectivo en caja.
- Control de choques de horario para evitar dobles reservas.
- Limite de 3 reservas activas por usuario.
- Cancelacion y reprogramacion con reglas de tiempo.
- Panel administrador para aprobar, rechazar y hacer check-in de reservas.
- CRUD de canchas.
- CRUD de tarifas por cancha y turno.
- Limpieza automatica de reservas expiradas y marcacion de no-shows.
- Historial automatico de cambios de reservas.

## Requisitos

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- MySQL.
- Extension PHP para la base usada en pruebas. Para PHPUnit, el archivo `phpunit.xml` usa SQLite en memoria, por eso se necesita `pdo_sqlite`.

## Instalacion

1. Instalar dependencias PHP:

```bash
composer install
```

2. Instalar dependencias frontend:

```bash
npm install
```

3. Copiar el archivo de entorno:

```bash
copy .env.example .env
```

4. Generar la clave de Laravel:

```bash
php artisan key:generate
```

5. Configurar la base de datos en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=top_tennis_digital
DB_USERNAME=root
DB_PASSWORD=
```

6. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

7. Crear el enlace de almacenamiento para fotos de canchas:

```bash
php artisan storage:link
```

## Usuarios de prueba

El seeder crea dos usuarios iniciales:

| Rol | Correo | Password |
| --- | --- | --- |
| Admin | `admin@toptennis.test` | `password` |
| Cliente | `cliente@toptennis.test` | `password` |

## Ejecucion

Levantar Laravel:

```bash
php artisan serve
```

Levantar Vite:

```bash
npm run dev
```

En PowerShell, si `npm run dev` falla por politica de ejecucion, usar:

```bash
npm.cmd run dev
```

Tambien se puede compilar frontend para produccion:

```bash
npm.cmd run build
```

## Rutas importantes

- Cliente: `/dashboard`
- Admin: `/admin/dashboard`
- Canchas admin: `/admin/canchas`
- Tarifas admin: `/admin/tarifas`
- Login: `/login`
- Registro: `/register`

## Tarifas y reservas

Las tarifas se registran por cancha y turno:

- `Mañana`: antes de 12:00
- `Tarde`: desde 12:00 hasta 17:59
- `Noche`: desde 18:00

Cuando un cliente reserva, el sistema calcula el precio usando la tarifa configurada para la cancha. Si la reserva dura 2 horas, suma cada hora por separado. Esto permite que una reserva que cruza de mañana a tarde tenga un total correcto.

Si una tarifa no existe, el sistema usa un precio de respaldo para no bloquear la reserva:

- Antes de 18:00: S/. 50.00
- Desde 18:00: S/. 60.00

## Limpieza automatica

El comando `reservas:limpiar` marca:

- Reservas Yape pendientes como `Expirado` si pasaron 30 minutos.
- Reservas verificadas como `No_Show` si termino el horario y el cliente no hizo check-in.

El comando esta programado en `routes/console.php` para ejecutarse cada minuto:

```bash
php artisan schedule:work
```

## Pruebas

Ejecutar pruebas:

```bash
php artisan test
```

O con PHPUnit:

```bash
vendor\bin\phpunit.bat
```

Nota: en este proyecto las pruebas usan SQLite en memoria segun `phpunit.xml`. Si aparece `could not find driver`, falta habilitar `pdo_sqlite` en PHP.

## Pruebas agregadas al flujo de reservas

Archivo: `tests/Feature/ReservationFlowTest.php`

Cubren:

- El dashboard muestra el total usando tarifas configuradas.
- Una reserva se crea con el total calculado por tarifas.
- Una cancha ocupada no aparece si el horario se cruza.
- Un usuario no puede tener mas de 3 reservas activas.

## Puntos para exposicion

- `Route::resource('tarifas')->except(['show'])` evita crear una ruta que no se usa.
- El campo `rol` permite separar clientes y administradores.
- `updateOrCreate` en el seeder evita duplicar usuarios al ejecutar varias veces.
- El precio de reserva ahora viene del modulo de tarifas, no de valores fijos.
- `lockForUpdate` ayuda a evitar que dos usuarios tomen el mismo horario al mismo tiempo.
- El historial de reservas se registra automaticamente desde el modelo `Reserva`.
