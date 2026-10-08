@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span>{{ $order->invoice }}</span>
                <div>@include('partials.order-badges')</div>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <div class="text-muted">Pelanggan</div>
                        <div class="fw-semibold">{{ $order->customer->name }}</div>
                        <div>{{ $order->customer->phone ?: '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted">Kasir</div>
                        <div class="fw-semibold">{{ $order->cashier->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted">Diterima</div>
                        <div>{{ $order->received_at->translatedFormat('d M Y H:i') }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted">Estimasi Selesai</div>
                        <div>{{ optional($order->estimated_at)->translatedFormat('d M Y') ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted">Dibayar</div>
                        <div>{{ optional($order->paid_at)->translatedFormat('d M Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted">Diambil</div>
                        <div>{{ optional($order->picked_up_at)->translatedFormat('d M Y H:i') ?? '-' }}</div>
                    </div>
                    @if ($order->notes)
                        <div class="col-12">
                            <div class="text-muted">Catatan</div>
                            <div>{{ $order->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr><th>Layanan</th><th class="text-end">Jumlah</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->service->name }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }} {{ $item->service->unit }}</td>
                            <td class="text-end">{{ rupiah($item->price) }}</td>
                            <td class="text-end">{{ rupiah($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr><th colspan="3" class="text-end">Total</th><th class="text-end fs-5">{{ rupiah($order->total) }}</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Perbarui Status</div>
            <div class="card-body">
                <form method="POST" action="{{ route('orders.status', $order) }}" class="d-grid gap-2">
                    @csrf @method('PATCH')
                    @foreach (\App\Models\Order::STATUSES as $key => $label)
                        <button name="status" value="{{ $key }}"
                                class="btn {{ $order->status === $key ? 'btn-'.\App\Models\Order::STATUS_COLORS[$key] : 'btn-outline-'.\App\Models\Order::STATUS_COLORS[$key] }}"
                                @disabled($order->status === $key)>
                            {{ $label }}
                        </button>
                    @endforeach
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Pembayaran</div>
            <div class="card-body">
                @if ($order->isPaid())
                    <div class="text-success"><i class="bi bi-check-circle-fill"></i> Lunas pada {{ $order->paid_at->translatedFormat('d M Y H:i') }}</div>
                @else
                    <form method="POST" action="{{ route('orders.pay', $order) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-success w-100" onclick="return confirm('Tandai transaksi ini lunas ({{ rupiah($order->total) }})?')">
                            <i class="bi bi-cash"></i> Tandai Lunas
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('orders.print', $order) }}" target="_blank" class="btn btn-light"><i class="bi bi-printer"></i> Cetak Nota</a>
            <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Hapus transaksi ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100"><i class="bi bi-trash"></i> Hapus Transaksi</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
