<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_create_an_appointment()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'phone' => '+243900000000',
            'subject' => 'Demande de rendez-vous test',
            'message' => 'Ceci est un test.',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00',
            'priority' => 'normal',
        ];

        $response = $this->postJson('/appointments', $payload);
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('appointments', [
            'email' => 'testuser@example.com',
            'subject' => 'Demande de rendez-vous test',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/appointments', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'subject', 'preferred_date', 'preferred_time', 'priority']);
    }

    /** @test */
    public function a_guest_cannot_create_more_than_5_appointments_per_hour_per_ip()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'ratelimit@example.com',
            'phone' => '+243900000001',
            'subject' => 'Test rate limit',
            'message' => 'Test',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '11:00',
            'priority' => 'normal',
        ];
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/appointments', $payload);
        }
        $response = $this->postJson('/appointments', $payload);
        $response->assertStatus(429);
    }

    /** @test */
    public function a_guest_can_cancel_appointment_with_token()
    {
        $appointment = Appointment::factory()->create([
            'status' => 'pending',
            'secure_token' => Str::random(32),
        ]);

        $response = $this->postJson('/appointments/' . $appointment->secure_token . '/cancel');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'canceled_by_requester',
        ]);
    }

    /** @test */
    public function cannot_access_tracking_for_expired_or_canceled_appointment()
    {
        $appointment = Appointment::factory()->create([
            'status' => 'expired',
            'secure_token' => Str::random(32),
        ]);
        $response = $this->get('/tracking/' . $appointment->secure_token);
        $response->assertStatus(403);
    }
}
