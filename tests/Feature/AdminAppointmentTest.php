<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Créer les rôles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'assistant']);
    }

    protected function createAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    protected function createAssistantUser()
    {
        $user = User::factory()->create();
        $user->assignRole('assistant');
        return $user;
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $admin = $this->createAdminUser();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function assistant_can_access_dashboard()
    {
        $assistant = $this->createAssistantUser();
        $response = $this->actingAs($assistant)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_accept_appointment()
    {
        $admin = $this->createAdminUser();
        $appointment = Appointment::factory()->create(['status' => 'pending']);
        $response = $this->actingAs($admin)->post('/admin/appointments/' . $appointment->id . '/accept');
        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'accepted',
        ]);
    }

    /** @test */
    public function admin_can_reject_appointment()
    {
        $admin = $this->createAdminUser();
        $appointment = Appointment::factory()->create(['status' => 'pending']);
        $response = $this->actingAs($admin)->post('/admin/appointments/' . $appointment->id . '/reject', [
            'rejection_reason' => 'Non conforme',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'rejected',
            'rejection_reason' => 'Non conforme',
        ]);
    }

    /** @test */
    public function admin_can_cancel_appointment()
    {
        $admin = $this->createAdminUser();
        $appointment = Appointment::factory()->create(['status' => 'pending']);
        $response = $this->actingAs($admin)->post('/admin/appointments/' . $appointment->id . '/cancel', [
            'admin_notes' => 'Annulation test',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'canceled',
            'admin_notes' => 'Annulation test',
        ]);
    }

    /** @test */
    public function admin_can_update_appointment()
    {
        $admin = $this->createAdminUser();
        $appointment = Appointment::factory()->create(['status' => 'pending']);
        $response = $this->actingAs($admin)->put('/admin/appointments/' . $appointment->id, [
            'admin_notes' => 'Note modifiée',
            'preferred_date' => now()->addDays(5)->format('Y-m-d'),
            'preferred_time' => '15:00',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'admin_notes' => 'Note modifiée',
            'preferred_time' => '15:00',
        ]);
    }

    /** @test */
    public function assistant_cannot_delete_appointment()
    {
        $assistant = $this->createAssistantUser();
        $appointment = Appointment::factory()->create();
        $response = $this->actingAs($assistant)->delete('/admin/appointments/' . $appointment->id);
        $response->assertStatus(403);
    }
}
