<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota {{ $order->invoice }}</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 280px; margin: 10px auto; color: #000; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
        .center { text-align: center; }
        .row { display: flex; justify-content: space-between; }
        hr { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ config('app.name') }}</h1>
    <div class="center">Nota Laundry</div>
    <hr>
    <div class="row"><span>No</span><span>{{ $order->invoice }}</span></div>
    <div class="row"><span>Tanggal</span><span>{{ $order->received_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Estimasi</span><span>{{ optional($order->estimated_at)->format('d/m/Y') ?? '-' }}</span></div>
    <div class="row"><span>Pelanggan</span><span>{{ $order->customer->name }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $order->cashier->name ?? '-' }}</span></div>
    <hr>
    @foreach ($order->items as $item)
        <div>{{ $item->service->name }}</div>
        <div class="row">
            <span>{{ rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }} {{ $item->service->unit }} x {{ number_format($item->price, 0, ',', '.') }}</span>
            <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    @endforeach
    <hr>
    <div class="row"><strong>TOTAL</strong><strong>{{ rupiah($order->total) }}</strong></div>
    <div class="row"><span>Status Bayar</span><span>{{ $order->payment_label }}</span></div>
    <hr>
    <div class="center">Terima kasih!</div>
    <p class="center no-print"><button onclick="window.print()">Cetak</button></p>
</body>
</html>
