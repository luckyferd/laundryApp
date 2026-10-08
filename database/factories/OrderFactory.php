<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $received = now()->subDays(fake()->numberBetween(0, 5));

        return [
            'invoice' => 'LDR-'.$received->format('Ymd').'-'.Str::upper(Str::random(6)),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'status' => Order::STATUS_BARU,
            'payment_status' => Order::PAYMENT_UNPAID,
            'total' => fake()->numberBetween(10, 100) * 1000,
            'received_at' => $received,
            'estimated_at' => $received->copy()->addDays(2),
        ];
    }

    /**
     * Mark the order as paid at the given time (default: now).
     *
     * @return static
     */
    public function paid($at = null)
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => $at ?? now(),
        ]);
    }
}
