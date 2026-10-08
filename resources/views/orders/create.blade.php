@extends('layouts.app')

@section('title', 'Transaksi Baru')

@section('content')
<form method="POST" action="{{ route('orders.store') }}" id="orderForm">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">Pelanggan</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih pelanggan terdaftar</label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">— Pelanggan baru —</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->name }}{{ $customer->phone ? ' · '.$customer->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2" id="newCustomerFields">
                        <div class="col-md-6">
                            <label class="form-label">Nama pelanggan baru</label>
                            <input type="text" name="new_customer_name" value="{{ old('new_customer_name') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="new_customer_phone" value="{{ old('new_customer_phone') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Layanan</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItem"><i class="bi bi-plus"></i> Tambah</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Layanan</th>
                            <th style="width: 130px">Jumlah</th>
                            <th class="text-end" style="width: 150px">Subtotal</th>
                            <th style="width: 50px"></th>
                        </tr>
                        </thead>
                        <tbody id="items"></tbody>
                    </table>
                </div>
                @if ($services->isEmpty())
                    <div class="card-body text-muted">Belum ada layanan aktif. Admin perlu menambahkan layanan terlebih dahulu.</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Ringkasan</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total</span>
                        <span class="fs-4 fw-bold" id="grandTotal">Rp 0</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Mis. jangan pakai pewangi">{{ old('notes') }}</textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="paid_now" name="paid_now" value="1" @checked(old('paid_now'))>
                        <label class="form-check-label" for="paid_now">Bayar sekarang (lunas)</label>
                    </div>
                    <button class="btn btn-primary w-100" @disabled($services->isEmpty())><i class="bi bi-save me-1"></i> Simpan Transaksi</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="itemRow">
    <tr>
        <td>
            <select class="form-select form-select-sm service" required>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit }}">
                        {{ $service->name }} — {{ rupiah($service->price) }}/{{ $service->unit }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" class="form-control qty" min="0.1" step="0.1" value="1" required>
                <span class="input-group-text unit"></span>
            </div>
        </td>
        <td class="text-end subtotal">Rp 0</td>
        <td><button type="button" class="btn btn-sm btn-light text-danger remove"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
(() => {
    const rupiah = (v) => 'Rp ' + Math.round(v).toLocaleString('id-ID');
    const tbody = document.getElementById('items');
    const tpl = document.getElementById('itemRow');
    const oldItems = @json(old('items', []));

    function reindex() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            const service = tr.querySelector('.service');
            const qty = tr.querySelector('.qty');
            service.name = `items[${i}][service_id]`;
            qty.name = `items[${i}][qty]`;
            const opt = service.selectedOptions[0];
            const price = opt ? Number(opt.dataset.price) : 0;
            tr.querySelector('.unit').textContent = opt ? opt.dataset.unit : '';
            const sub = price * (parseFloat(qty.value) || 0);
            tr.querySelector('.subtotal').textContent = rupiah(sub);
            total += Math.round(sub);
        });
        document.getElementById('grandTotal').textContent = rupiah(total);
    }

    function addRow(item = {}) {
        const row = tpl.content.firstElementChild.cloneNode(true);
        if (item.service_id) row.querySelector('.service').value = item.service_id;
        if (item.qty) row.querySelector('.qty').value = item.qty;
        tbody.appendChild(row);
        reindex();
    }

    document.getElementById('addItem').addEventListener('click', () => addRow());
    tbody.addEventListener('input', reindex);
    tbody.addEventListener('change', reindex);
    tbody.addEventListener('click', (e) => {
        if (e.target.closest('.remove') && tbody.children.length > 1) {
            e.target.closest('tr').remove();
            reindex();
        }
    });

    const customerSelect = document.getElementById('customer_id');
    const toggleNewCustomer = () => {
        document.getElementById('newCustomerFields').style.display = customerSelect.value ? 'none' : '';
    };
    customerSelect.addEventListener('change', toggleNewCustomer);
    toggleNewCustomer();

    if (tpl.content.querySelector('option')) {
        (oldItems.length ? oldItems : [{}]).forEach(addRow);
    }
})();
</script>
@endpush
