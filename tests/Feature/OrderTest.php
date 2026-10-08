<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_creates_order_with_new_customer_and_total_is_computed()
    {
        $kasir = User::factory()->create();
        $kiloan = Service::factory()->create(['price' => 7000, 'unit' => 'kg', 'duration_days' => 2]);
        $bedcover = Service::factory()->create(['price' => 25000, 'unit' => 'pcs', 'duration_days' => 3]);

        $response = $this->actingAs($kasir)->post(route('orders.store'), [
            'new_customer_name' => 'Budi',
            'new_customer_phone' => '08123',
            'items' => [
                ['service_id' => $kiloan->id, 'qty' => 2.5],
                ['service_id' => $bedcover->id, 'qty' => 1],
            ],
            'paid_now' => 1,
        ]);

        $order = Order::first();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(17500 + 25000, $order->total);
        $this->assertSame('Budi', $order->customer->name);
        $this->assertSame($kasir->id, $order->user_id);
        $this->assertSame(Order::STATUS_BARU, $order->status);
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        $this->assertSame(3, (int) $order->received_at->diffInDays($order->estimated_at));
        $this->assertCount(2, $order->items);
        $this->assertStringStartsWith('LDR-'.now()->format('Ymd').'-0001', $order->invoice);
    }

    public function test_order_requires_items_and_customer()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('orders.store'), [])
            ->assertSessionHasErrors(['customer_id', 'new_customer_name', 'items']);
    }

    public function test_inactive_service_cannot_be_ordered()
    {
        $service = Service::factory()->create(['is_active' => false]);

        $this->actingAs(User::factory()->create())
            ->post(route('orders.store'), [
                'customer_id' => Customer::factory()->create()->id,
                'items' => [['service_id' => $service->id, 'qty' => 1]],
            ])
            ->assertSessionHasErrors('items.0.service_id');
    }

    public function test_status_flow_and_payment()
    {
        $kasir = User::factory()->create();
        $order = Order::factory()->create();

        $this->actingAs($kasir)->patch(route('orders.status', $order), ['status' => 'proses']);
        $this->assertSame('proses', $order->fresh()->status);

        // Cannot hand over unpaid laundry
        $this->patch(route('orders.status', $order), ['status' => 'diambil'])->assertSessionHas('error');
        $this->assertSame('proses', $order->fresh()->status);

        $this->patch(route('orders.pay', $order))->assertSessionHas('success');
        $this->assertTrue($order->fresh()->isPaid());

        $this->patch(route('orders.status', $order), ['status' => 'diambil']);
        $this->assertSame('diambil', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->picked_up_at);
    }

    public function test_only_admin_can_delete_order()
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('orders.destroy', $order))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.index'));

        $this->assertModelMissing($order);
    }

    public function test_order_pages_render()
    {
        $service = Service::factory()->create();
        $order = Order::factory()->create();
        $order->items()->create(['service_id' => $service->id, 'qty' => 1, 'price' => 5000, 'subtotal' => 5000]);

        $this->actingAs(User::factory()->create());
        $this->get(route('orders.index', ['status' => 'baru']))->assertOk()->assertSee($order->invoice);
        $this->get(route('orders.create'))->assertOk();
        $this->get(route('orders.show', $order))->assertOk()->assertSee($service->name);
        $this->get(route('orders.print', $order))->assertOk();
    }
}
