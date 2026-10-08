@extends('layouts.app')

@section('title', 'Laporan Pemasukan')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label small text-muted">Dari tanggal</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label small text-muted">Sampai tanggal</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-sm-4 col-md-6 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel"></i> Tampilkan</button>
                <button class="btn btn-outline-success" name="export" value="csv"><i class="bi bi-filetype-csv"></i> Export CSV</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="card stat-card"><div class="card-body">
            <div class="stat-label">Total Pemasukan</div>
            <div class="stat-value text-success">{{ rupiah($total) }}</div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card"><div class="card-body">
            <div class="stat-label">Transaksi Lunas</div>
            <div class="stat-value">{{ $orders->count() }}</div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card"><div class="card-body">
            <div class="stat-label">Rata-rata per Transaksi</div>
            <div class="stat-value">{{ rupiah($orders->count() ? $total / $orders->count() : 0) }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Rekap Harian</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tanggal</th><th class="text-center">Trx</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    @forelse ($daily as $day)
                        <tr>
                            <td>{{ $day['date']->translatedFormat('d M Y') }}</td>
                            <td class="text-center">{{ $day['count'] }}</td>
                            <td class="text-end">{{ rupiah($day['total']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Rincian Transaksi Lunas</div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead><tr><th>Invoice</th><th>Dibayar</th><th>Pelanggan</th><th>Kasir</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('orders.show', $order) }}">{{ $order->invoice }}</a></td>
                            <td>{{ $order->paid_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ $order->cashier->name ?? '-' }}</td>
                            <td class="text-end">{{ rupiah($order->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada transaksi lunas pada periode ini.</td></tr>
                    @endforelse
                    </tbody>
                    @if ($orders->isNotEmpty())
                        <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end">{{ rupiah($total) }}</th></tr></tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
