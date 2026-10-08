<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_kasir_account()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('users.store'), [
                'name' => 'Siti',
                'email' => 'siti@laundry.test',
                'role' => 'kasir',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'is_active' => 1,
            ])
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'siti@laundry.test')->first();
        $this->assertTrue($user->isKasir());
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_role_must_be_admin_or_kasir()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('users.store'), [
                'name' => 'X', 'email' => 'x@x.test', 'role' => 'owner',
                'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_admin_cannot_demote_or_delete_self()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->put(route('users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'kasir', 'is_active' => 1,
        ])->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->isAdmin());

        $this->delete(route('users.destroy', $admin))->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_used_service_is_deactivated_instead_of_deleted()
    {
        $admin = User::factory()->admin()->create();
        $used = Service::factory()->create();
        $unused = Service::factory()->create();
        Order::factory()->create()->items()->create([
            'service_id' => $used->id, 'qty' => 1, 'price' => 1000, 'subtotal' => 1000,
        ]);

        $this->actingAs($admin)->delete(route('services.destroy', $used));
        $this->delete(route('services.destroy', $unused));

        $this->assertFalse($used->fresh()->is_active);
        $this->assertModelMissing($unused);
    }

    public function test_admin_pages_render()
    {
        Service::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        foreach (['services.index', 'services.create', 'users.index', 'users.create', 'customers.index', 'customers.create', 'reports.income'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
