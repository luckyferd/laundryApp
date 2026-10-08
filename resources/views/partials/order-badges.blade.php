<span class="badge text-bg-{{ $order->status_color }}">{{ $order->status_label }}</span>
<span class="badge {{ $order->isPaid() ? 'text-bg-success' : 'text-bg-danger' }} bg-opacity-75">{{ $order->payment_label }}</span>
