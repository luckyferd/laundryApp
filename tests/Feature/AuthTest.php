<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_admin_logs_in_and_lands_on_dashboard()
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/home');

        $this->get('/home')->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk()->assertSee('Pemasukan Hari Ini');
    }

    public function test_kasir_lands_on_orders_and_cannot_open_admin_pages()
    {
        $kasir = User::factory()->create();

        $this->post('/login', ['email' => $kasir->email, 'password' => 'password']);

        $this->get('/home')->assertRedirect(route('orders.index'));
        $this->get(route('orders.index'))->assertOk();
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('reports.income'))->assertForbidden();
        $this->get(route('services.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_wrong_password_and_inactive_user_are_rejected()
    {
        $user = User::factory()->admin()->create();
        $inactive = User::factory()->admin()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $inactive->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_user_is_logged_out_on_next_request()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
