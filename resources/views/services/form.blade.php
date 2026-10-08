@extends('layouts.app')

@section('title', $service->exists ? 'Edit Layanan' : 'Tambah Layanan')

@section('content')
<div class="card" style="max-width: 640px">
    <div class="card-body">
        <form method="POST" action="{{ $service->exists ? route('services.update', $service) : route('services.store') }}">
            @csrf
            @if ($service->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Nama Layanan</label>
                <input type="text" name="name" value="{{ old('name', $service->name) }}" class="form-control" placeholder="Mis. Cuci Kering Setrika" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-5">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" min="0" value="{{ old('price', $service->price) }}" class="form-control" required>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Satuan</label>
                    <select name="unit" class="form-select">
                        @foreach (\App\Models\Service::UNITS as $key => $label)
                            <option value="{{ $key }}" @selected(old('unit', $service->unit) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label">Durasi (hari)</label>
                    <input type="number" name="duration_days" min="0" max="60" value="{{ old('duration_days', $service->duration_days) }}" class="form-control" required>
                </div>
            </div>
            <div class="form-check form-switch mb-3">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $service->is_active))>
                <label class="form-check-label" for="is_active">Aktif (bisa dipilih kasir)</label>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="{{ route('services.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
