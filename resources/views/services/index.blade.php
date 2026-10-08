@extends('layouts.app')

@section('title', 'Layanan & Harga')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Layanan</span>
        <a href="{{ route('services.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Tambah Layanan</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr><th>Nama</th><th class="text-end">Harga</th><th>Satuan</th><th class="text-center">Durasi</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse ($services as $service)
                <tr class="{{ $service->is_active ? '' : 'text-muted' }}">
                    <td class="fw-semibold">{{ $service->name }}</td>
                    <td class="text-end">{{ rupiah($service->price) }}</td>
                    <td>{{ \App\Models\Service::UNITS[$service->unit] ?? $service->unit }}</td>
                    <td class="text-center">{{ $service->duration_days }} hari</td>
                    <td>
                        <span class="badge {{ $service->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('services.destroy', $service) }}" class="d-inline" onsubmit="return confirm('Hapus layanan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada layanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
