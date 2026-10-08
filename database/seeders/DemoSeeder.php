<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Sample customers and ~90 days of transactions so the dashboard has data to show.
     *
     * @return void
     */
    public function run()
    {
        $services = Service::active()->get();
        $cashiers = User::where('is_active', true)->get();

        if ($services->isEmpty() || $cashiers->isEmpty()) {
            return;
        }

        $customers = Customer::factory()->count(25)->create();

        for ($daysAgo = 90; $daysAgo >= 0; $daysAgo--) {
            $count = random_int(1, 6);

            for ($n = 0; $n < $count; $n++) {
                $received = now()->subDays($daysAgo)->setTime(random_int(8, 19), random_int(0, 59));
                if ($received->isFuture()) {
                    $received = now()->subMinutes(random_int(1, 60));
                }

                $picked = $services->random(random_int(1, 2));
                $items = $picked->map(function (Service $service) {
                    $qty = $service->unit === 'kg' ? random_int(10, 80) / 10 : random_int(1, 3);

                    return [
                        'service_id' => $service->id,
                        'qty' => $qty,
                        'price' => $service->price,
                        'subtotal' => (int) round($qty * $service->price),
                    ];
                });

                $estimated = $received->copy()->addDays($picked->max('duration_days'));
                [$status, $paid] = $this->statusFor($daysAgo);

                $order = Order::create([
                    'invoice' => Order::generateInvoice($received),
                    'customer_id' => $customers->random()->id,
                    'user_id' => $cashiers->random()->id,
                    'status' => $status,
                    'payment_status' => $paid ? Order::PAYMENT_PAID : Order::PAYMENT_UNPAID,
                    'total' => $items->sum('subtotal'),
                    'received_at' => $received,
                    'estimated_at' => $estimated,
                    'paid_at' => $paid ? $this->paidAt($received, $estimated, $status) : null,
                    'picked_up_at' => $status === Order::STATUS_DIAMBIL ? min($estimated, now()) : null,
                ]);

                $order->items()->createMany($items->all());
            }
        }
    }

    /**
     * Older orders are mostly picked up; recent ones are still in progress.
     */
    private function statusFor(int $daysAgo): array
    {
        if ($daysAgo > 5) {
            return random_int(1, 20) === 1 ? [Order::STATUS_SELESAI, false] : [Order::STATUS_DIAMBIL, true];
        }

        $status = collect([Order::STATUS_BARU, Order::STATUS_PROSES, Order::STATUS_SELESAI, Order::STATUS_DIAMBIL])
            ->slice(0, min(4, 6 - $daysAgo))
            ->random();

        return [$status, $status === Order::STATUS_DIAMBIL || random_int(0, 1) === 1];
    }

    private function paidAt($received, $estimated, string $status)
    {
        $at = random_int(0, 1) ? $received : $estimated;

        return $at->isFuture() ? $received : $at;
    }
}
