@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $growth = $lastMonthIncome > 0 ? (($income['month'] - $lastMonthIncome) / $lastMonthIncome) * 100 : null;
@endphp

{{-- Ringkasan pemasukan --}}
<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'Pemasukan Hari Ini', 'value' => $income['today'], 'icon' => 'bi-cash-coin', 'color' => 'primary'],
        ['label' => 'Pemasukan Minggu Ini', 'value' => $income['week'], 'icon' => 'bi-calendar-week', 'color' => 'info'],
        ['label' => 'Pemasukan Bulan Ini', 'value' => $income['month'], 'icon' => 'bi-calendar3', 'color' => 'success'],
        ['label' => 'Pemasukan Tahun Ini', 'value' => $income['year'], 'icon' => 'bi-bar-chart-line', 'color' => 'warning'],
    ] as $stat)
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-{{ $stat['color'] }} bg-opacity-10 text-{{ $stat['color'] }}">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </span>
                    <div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                        <div class="stat-value">{{ rupiah($stat['value']) }}</div>
                        @if ($loop->index === 2 && $growth !== null)
                            <small class="{{ $growth >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi {{ $growth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                {{ number_format(abs($growth), 1, ',', '.') }}% vs bulan lalu
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Transaksi Masuk Hari Ini</div>
                <div class="stat-value">{{ $ordersToday }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Piutang (Belum Lunas)</div>
                <div class="stat-value text-danger">{{ rupiah($receivable) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-label">Melewati Estimasi Selesai</div>
                <div class="stat-value {{ $overdue ? 'text-warning' : '' }}">{{ $overdue }} cucian</div>
            </div>
        </div>
    </div>
</div>

{{-- Status laundry --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-basket me-1"></i> Status Laundry</span>
        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">Kelola</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach ($statusSummary as $key => $status)
                <div class="col-6 col-md-3">
                    <a href="{{ route('orders.index', ['status' => $key]) }}" class="text-decoration-none text-reset">
                        <div class="p-3 rounded bg-light status-tile border-{{ $status['color'] }}">
                            <div class="text-muted small">{{ $status['label'] }}</div>
                            <div class="fs-3 fw-bold">{{ $status['count'] }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Grafik --}}
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">Pemasukan 30 Hari Terakhir</div>
            <div class="card-body"><canvas id="dailyChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">Komposisi Status</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" style="max-height: 260px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">Pemasukan per Bulan (12 Bulan)</div>
            <div class="card-body"><canvas id="monthlyChart" height="90"></canvas></div>
        </div>
    </div>
</div>

{{-- Transaksi terbaru --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Transaksi Terbaru</span>
        <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Transaksi Baru</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Masuk</th>
                <th>Status</th>
                <th class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($recentOrders as $order)
                <tr>
                    <td><a href="{{ route('orders.show', $order) }}">{{ $order->invoice }}</a></td>
                    <td>{{ $order->customer->name }}</td>
                    <td>{{ $order->received_at->translatedFormat('d M Y H:i') }}</td>
                    <td>@include('partials.order-badges')</td>
                    <td class="text-end">{{ rupiah($order->total) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
    const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
    const moneyAxis = { beginAtZero: true, ticks: { callback: (v) => rupiah(v) } };
    const moneyTooltip = { callbacks: { label: (ctx) => rupiah(ctx.parsed.y) } };

    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: @json($dailyChart['labels']),
            datasets: [{
                label: 'Pemasukan',
                data: @json($dailyChart['data']),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,.12)',
                fill: true,
                tension: .3,
                pointRadius: 2,
            }]
        },
        options: { plugins: { legend: { display: false }, tooltip: moneyTooltip }, scales: { y: moneyAxis } }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyChart['labels']),
            datasets: [{ label: 'Pemasukan', data: @json($monthlyChart['data']), backgroundColor: '#198754', borderRadius: 6 }]
        },
        options: { plugins: { legend: { display: false }, tooltip: moneyTooltip }, scales: { y: moneyAxis } }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($statusSummary->pluck('label')->values()),
            datasets: [{
                data: @json($statusSummary->pluck('count')->values()),
                backgroundColor: ['#6c757d', '#ffc107', '#0dcaf0', '#198754'],
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
</script>
@endpush
