@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / no. HP">
            <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
        </form>
        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Tambah Pelanggan</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr><th>Nama</th><th>No. HP</th><th>Alamat</th><th class="text-center">Transaksi</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td class="fw-semibold">{{ $customer->name }}</td>
                    <td>{{ $customer->phone ?: '-' }}</td>
                    <td class="small text-muted">{{ \Illuminate\Support\Str::limit($customer->address, 50) ?: '-' }}</td>
                    <td class="text-center">
                        <a href="{{ route('orders.index', ['q' => $customer->phone ?: $customer->name]) }}">{{ $customer->orders_count }}</a>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pelanggan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($customers->hasPages())
        <div class="card-footer bg-white">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
