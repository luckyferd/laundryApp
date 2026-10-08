@extends('layouts.app')

@section('title', $customer->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')

@section('content')
<div class="card" style="max-width: 640px">
    <div class="card-body">
        <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
            @csrf
            @if ($customer->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="address" rows="3" class="form-control">{{ old('address', $customer->address) }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="{{ route('customers.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
