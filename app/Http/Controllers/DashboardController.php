<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        $income = [
            'today' => (int) Order::paidBetween($now->copy()->startOfDay(), $now->copy()->endOfDay())->sum('total'),
            'week' => (int) Order::paidBetween($now->copy()->startOfWeek(), $now->copy()->endOfWeek())->sum('total'),
            'month' => (int) Order::paidBetween($now->copy()->startOfMonth(), $now->copy()->endOfMonth())->sum('total'),
            'year' => (int) Order::paidBetween($now->copy()->startOfYear(), $now->copy()->endOfYear())->sum('total'),
        ];

        $lastMonthIncome = (int) Order::paidBetween(
            $now->copy()->subMonthNoOverflow()->startOfMonth(),
            $now->copy()->subMonthNoOverflow()->endOfMonth()
        )->sum('total');

        $receivable = (int) Order::where('payment_status', Order::PAYMENT_UNPAID)->sum('total');

        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusSummary = collect(Order::STATUSES)->map(fn ($label, $key) => [
            'label' => $label,
            'color' => Order::STATUS_COLORS[$key],
            'count' => (int) ($statusCounts[$key] ?? 0),
        ]);

        $ordersToday = Order::whereBetween('received_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])->count();

        $overdue = Order::whereIn('status', [Order::STATUS_BARU, Order::STATUS_PROSES])
            ->whereNotNull('estimated_at')
            ->where('estimated_at', '<', $now)
            ->count();

        $recentOrders = Order::with('customer')->latest('received_at')->limit(8)->get();

        return view('dashboard.index', [
            'income' => $income,
            'lastMonthIncome' => $lastMonthIncome,
            'receivable' => $receivable,
            'statusSummary' => $statusSummary,
            'ordersToday' => $ordersToday,
            'overdue' => $overdue,
            'recentOrders' => $recentOrders,
            'dailyChart' => $this->dailyIncome(30),
            'monthlyChart' => $this->monthlyIncome(12),
        ]);
    }

    /**
     * Paid income per day for the last $days days (including today).
     */
    private function dailyIncome(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $totals = Order::paidBetween($start, now()->endOfDay())
            ->get(['paid_at', 'total'])
            ->groupBy(fn (Order $order) => $order->paid_at->format('Y-m-d'))
            ->map->sum('total');

        $labels = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->translatedFormat('d M');
            $data[] = (int) ($totals[$date->format('Y-m-d')] ?? 0);
        }

        return compact('labels', 'data');
    }

    /**
     * Paid income per month for the last $months months (including this month).
     */
    private function monthlyIncome(int $months): array
    {
        $start = now()->startOfMonth()->subMonthsNoOverflow($months - 1);

        $totals = Order::paidBetween($start, now()->endOfMonth())
            ->get(['paid_at', 'total'])
            ->groupBy(fn (Order $order) => $order->paid_at->format('Y-m'))
            ->map->sum('total');

        $labels = [];
        $data = [];
        for ($i = 0; $i < $months; $i++) {
            /** @var Carbon $date */
            $date = $start->copy()->addMonthsNoOverflow($i);
            $labels[] = $date->translatedFormat('M Y');
            $data[] = (int) ($totals[$date->format('Y-m')] ?? 0);
        }

        return compact('labels', 'data');
    }
}
