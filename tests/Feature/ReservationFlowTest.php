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

        // 11:00 por 2 horas cruza de Mañana a Tarde: 35 + 45 = 80.
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

        // 11:00 se cruza con 10:00-12:00, por eso la cancha no debe listarse.
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

        // La cuarta reserva activa debe rechazarse antes de revisar disponibilidad.
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
        return Tarifa::create([
            'cancha_id' => $canchaId,
            'turno' => $turno,
            'precio_hora' => $precio,
        ]);
    }
}
