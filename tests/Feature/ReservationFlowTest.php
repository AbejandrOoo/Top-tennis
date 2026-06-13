<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\Reserva;
use App\Models\Tarifa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_configured_tariffs_to_preview_total(): void
    {
        $user = User::factory()->create();
        $cancha = $this->crearCancha();

        $this->crearTarifa($cancha->id, 'Mañana', 35);
        $this->crearTarifa($cancha->id, 'Tarde', 45);

        // Esta prueba revisa que una reserva de dos horas sume dos turnos distintos
        // Sirve para confirmar que el precio viene de tarifas y no de un numero fijo
        $response = $this->actingAs($user)->get(route('dashboard', [
            'fecha' => now()->addDay()->format('Y-m-d'),
            'hora' => '11:00',
            'duracion' => 2,
        ]));

        $response->assertOk();
        $response->assertSee('Total: S/. 80.00');
    }

    public function test_user_can_create_reservation_with_tariff_total(): void
    {
        $user = User::factory()->create();
        $cancha = $this->crearCancha();

        $this->crearTarifa($cancha->id, 'Noche', 70);

        // Se manda una reserva normal como si viniera desde el modal del cliente
        // Luego se revisa que el total guardado sea el precio de la tarifa nocturna
        $response = $this->actingAs($user)->post(route('reservas.store'), [
            'cancha_id' => $cancha->id,
            'fecha' => now()->addDay()->format('Y-m-d'),
            'hora' => '18:00',
            'duracion' => 1,
            'metodo_pago' => 'yape',
            'numero_operacion' => 'YP-123456',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('reservas', [
            'user_id' => $user->id,
            'cancha_id' => $cancha->id,
            'hora_inicio' => '18:00:00',
            'hora_fin' => '19:00:00',
            'total' => 70.00,
            'estado' => 'Pendiente',
        ]);
    }

    public function test_reserved_court_is_not_available_when_schedule_overlaps(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $cancha = $this->crearCancha(['nombre' => 'Cancha Ocupada']);
        $fecha = now()->addDay()->format('Y-m-d');

        // Dejamos una reserva creada para simular una cancha ocupada
        // Despues buscamos un horario cruzado y no deberia mostrarse libre
        Reserva::create([
            'user_id' => $otherUser->id,
            'cancha_id' => $cancha->id,
            'fecha' => $fecha,
            'hora_inicio' => '10:00:00',
            'hora_fin' => '12:00:00',
            'duracion' => 2,
            'estado' => 'Pendiente',
            'metodo_pago' => 'efectivo',
            'total' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', [
            'fecha' => $fecha,
            'hora' => '11:00',
            'duracion' => 1,
        ]));

        $response->assertOk();
        $response->assertDontSee('Cancha Ocupada');
    }

    public function test_user_cannot_have_more_than_three_active_reservations(): void
    {
        $user = User::factory()->create();
        $cancha = $this->crearCancha();
        $fecha = now()->addDay()->format('Y-m-d');

        // Creamos tres reservas activas para llegar al limite permitido
        // Con esto la siguiente solicitud deberia regresar con error
        foreach (['08:00:00', '10:00:00', '12:00:00'] as $horaInicio) {
            Reserva::create([
                'user_id' => $user->id,
                'cancha_id' => $cancha->id,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => substr_replace($horaInicio, sprintf('%02d', ((int) substr($horaInicio, 0, 2)) + 1), 0, 2),
                'duracion' => 1,
                'estado' => 'Pendiente',
                'metodo_pago' => 'efectivo',
                'total' => 50,
            ]);
        }

        // La cuarta reserva ya no deberia pasar aunque tenga un horario valido
        // Esta regla evita que un solo usuario bloquee demasiadas canchas
        $response = $this->actingAs($user)->post(route('reservas.store'), [
            'cancha_id' => $cancha->id,
            'fecha' => $fecha,
            'hora' => '14:00',
            'duracion' => 1,
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertSessionHas('error', 'Límite superado: No puedes tener más de 3 reservas activas simultáneamente.');
    }

    private function crearCancha(array $attributes = []): Cancha
    {
        // Crea una cancha simple para no repetir datos en cada prueba
        // Se pueden cambiar algunos campos cuando una prueba lo necesita
        return Cancha::create(array_merge([
            'nombre' => 'Cancha Central',
            'superficie' => 'Arcilla',
            'estado' => 'Disponible',
            'tipo_partido' => 'Ambos (Singles y Dobles)',
            'iluminacion' => 'Con iluminación',
            'descripcion' => 'Cancha de prueba',
        ], $attributes));
    }

    private function crearTarifa(int $canchaId, string $turno, float $precio): Tarifa
    {
        // Crea una tarifa rapida para la cancha usada en la prueba
        // Asi cada caso controla sus propios precios sin depender de seeders
        return Tarifa::create([
            'cancha_id' => $canchaId,
            'turno' => $turno,
            'precio_hora' => $precio,
        ]);
    }
}
