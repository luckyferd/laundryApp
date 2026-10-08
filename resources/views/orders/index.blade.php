@extends('layouts.app')

@section('title', 'Status Laundry')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Invoice / nama / no. HP">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted">Status Cucian</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Order::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted">Pembayaran</label>
                <select name="payment" class="form-select">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Order::PAYMENT_STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(request('payment') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('orders.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Transaksi <span class="text-muted fw-normal">({{ $orders->total() }})</span></span>
        <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Transaksi Baru</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Masuk</th>
                <th>Estimasi</th>
                <th>Status</th>
                <th class="text-end">Total</th>
                <th class="text-end">Ubah Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td><a href="{{ route('orders.show', $order) }}" class="fw-semibold">{{ $order->invoice }}</a></td>
                    <td>
                        {{ $order->customer->name }}
                        @if ($order->customer->phone)<div class="small text-muted">{{ $order->customer->phone }}</div>@endif
                    </td>
                    <td class="small">{{ $order->received_at->translatedFormat('d M Y H:i') }}</td>
                    <td class="small">
                        @if ($order->estimated_at)
                            <span class="{{ in_array($order->status, ['baru', 'proses']) && $order->estimated_at->isPast() ? 'text-danger fw-semibold' : '' }}">
                                {{ $order->estimated_at->translatedFormat('d M Y') }}
                            </span>
                        @else - @endif
                    </td>
                    <td>@include('partials.order-badges')</td>
                    <td class="text-end">{{ rupiah($order->total) }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('orders.status', $order) }}" class="d-inline-flex gap-1">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="width: 120px" onchange="this.form.submit()">
                                @foreach (\App\Models\Order::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
        <div class="card-footer bg-white">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
