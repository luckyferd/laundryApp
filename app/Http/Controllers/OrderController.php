<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['customer', 'cashier'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->input('q');
                $query->where(function ($query) use ($q) {
                    $query->where('invoice', 'like', "%{$q}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('payment'), fn ($q) => $q->where('payment_status', $request->input('payment')))
            ->latest('received_at')
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        return view('orders.create', [
            'customers' => Customer::orderBy('name')->get(),
            'services' => Service::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'required_without:new_customer_name', 'exists:customers,id'],
            'new_customer_name' => ['nullable', 'required_without:customer_id', 'string', 'max:255'],
            'new_customer_phone' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'items.*.qty' => ['required', 'numeric', 'min:0.1', 'max:9999'],
            'paid_now' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_id.required_without' => 'Pilih pelanggan atau isi nama pelanggan baru.',
            'new_customer_name.required_without' => 'Pilih pelanggan atau isi nama pelanggan baru.',
            'items.required' => 'Tambahkan minimal satu layanan.',
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $customerId = $data['customer_id'] ?? Customer::create([
                'name' => $data['new_customer_name'],
                'phone' => $data['new_customer_phone'] ?? null,
            ])->id;

            $services = Service::whereIn('id', collect($data['items'])->pluck('service_id'))->get()->keyBy('id');

            $items = collect($data['items'])->map(function ($item) use ($services) {
                $service = $services[$item['service_id']];
                $qty = round((float) $item['qty'], 2);

                return [
                    'service_id' => $service->id,
                    'qty' => $qty,
                    'price' => $service->price,
                    'subtotal' => (int) round($qty * $service->price),
                ];
            });

            $maxDuration = $items->map(fn ($i) => $services[$i['service_id']]->duration_days)->max();
            $paidNow = $request->boolean('paid_now');
            $now = now();

            $order = Order::create([
                'invoice' => Order::generateInvoice($now),
                'customer_id' => $customerId,
                'user_id' => $request->user()->id,
                'status' => Order::STATUS_BARU,
                'payment_status' => $paidNow ? Order::PAYMENT_PAID : Order::PAYMENT_UNPAID,
                'total' => $items->sum('subtotal'),
                'received_at' => $now,
                'estimated_at' => $now->copy()->addDays($maxDuration),
                'paid_at' => $paidNow ? $now : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($items->all());

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', "Transaksi {$order->invoice} berhasil dibuat.");
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'cashier', 'items.service']);

        return view('orders.show', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load(['customer', 'cashier', 'items.service']);

        return view('orders.print', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        if ($data['status'] === Order::STATUS_DIAMBIL && ! $order->isPaid()) {
            return back()->with('error', 'Cucian belum lunas. Selesaikan pembayaran sebelum diambil.');
        }

        $order->status = $data['status'];
        $order->picked_up_at = $data['status'] === Order::STATUS_DIAMBIL ? now() : null;
        $order->save();

        return back()->with('success', "Status {$order->invoice} diubah menjadi {$order->status_label}.");
    }

    public function pay(Order $order)
    {
        if ($order->isPaid()) {
            return back()->with('error', 'Transaksi ini sudah lunas.');
        }

        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        return back()->with('success', "Pembayaran {$order->invoice} berhasil dicatat.");
    }

    public function destroy(Order $order)
    {
        $invoice = $order->invoice;
        $order->delete();

        return redirect()->route('orders.index')->with('success', "Transaksi {$invoice} dihapus.");
    }
}
