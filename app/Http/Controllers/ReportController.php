<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function income(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();

        $orders = Order::with(['customer', 'cashier'])
            ->paidBetween($from, $to)
            ->orderBy('paid_at')
            ->get();

        $daily = $orders->groupBy(fn (Order $o) => $o->paid_at->format('Y-m-d'))
            ->map(fn ($group, $date) => [
                'date' => Carbon::parse($date),
                'count' => $group->count(),
                'total' => (int) $group->sum('total'),
            ])
            ->values();

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($orders, $from, $to);
        }

        return view('reports.income', [
            'orders' => $orders,
            'daily' => $daily,
            'from' => $from,
            'to' => $to,
            'total' => (int) $orders->sum('total'),
        ]);
    }

    private function exportCsv($orders, Carbon $from, Carbon $to)
    {
        $filename = 'laporan-pemasukan-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice', 'Tanggal Bayar', 'Pelanggan', 'Kasir', 'Total']);
            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->invoice,
                    $order->paid_at->format('Y-m-d H:i'),
                    $order->customer->name ?? '-',
                    $order->cashier->name ?? '-',
                    $order->total,
                ]);
            }
            fputcsv($out, ['', '', '', 'TOTAL', $orders->sum('total')]);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
