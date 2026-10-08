<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_sums_only_paid_income_in_each_period()
    {
        $this->travelTo(now()->setDate(2025, 6, 18)->setTime(12, 0));

        Order::factory()->paid(now()->subHour())->create(['total' => 10000]);       // today
        Order::factory()->paid(now()->subDays(20))->create(['total' => 30000]);     // last month (29 May)
        Order::factory()->paid(now()->subDays(5))->create(['total' => 20000]);      // this month
        Order::factory()->create(['total' => 99000]);                               // unpaid

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('income', fn ($income) => $income['today'] === 10000
            && $income['month'] === 30000
            && $income['year'] === 60000);
        $response->assertViewHas('receivable', 99000);
        $response->assertViewHas('lastMonthIncome', 30000);
        $response->assertViewHas('dailyChart', fn ($chart) => count($chart['data']) === 30 && end($chart['data']) === 10000);
        $response->assertViewHas('monthlyChart', fn ($chart) => count($chart['data']) === 12 && end($chart['data']) === 30000);
    }

    public function test_status_summary_counts_orders()
    {
        Order::factory()->count(2)->create(['status' => 'proses']);
        Order::factory()->create(['status' => 'selesai']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertViewHas('statusSummary', fn ($s) => $s['proses']['count'] === 2
                && $s['selesai']['count'] === 1
                && $s['baru']['count'] === 0);
    }

    public function test_income_report_filters_by_date_and_exports_csv()
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->paid(now()->setDate(2025, 1, 10))->create(['total' => 15000]);
        Order::factory()->paid(now()->setDate(2025, 2, 10))->create(['total' => 50000]);

        $this->actingAs($admin)
            ->get(route('reports.income', ['from' => '2025-01-01', 'to' => '2025-01-31']))
            ->assertOk()
            ->assertViewHas('total', 15000);

        $csv = $this->get(route('reports.income', ['from' => '2025-01-01', 'to' => '2025-12-31', 'export' => 'csv']));
        $csv->assertOk();
        $this->assertStringContainsString('65000', $csv->streamedContent());
    }
}
