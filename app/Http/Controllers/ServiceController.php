<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('orderItems')->orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.form', ['service' => new Service(['unit' => 'kg', 'duration_days' => 2, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        Service::create($this->validated($request));

        return redirect()->route('services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('services.form', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $service->update($this->validated($request));

        return redirect()->route('services.index')->with('success', 'Layanan diperbarui.');
    }

    public function destroy(Service $service)
    {
        if ($service->orderItems()->exists()) {
            $service->update(['is_active' => false]);

            return back()->with('success', 'Layanan sudah dipakai di transaksi, jadi dinonaktifkan (bukan dihapus).');
        }

        $service->delete();

        return redirect()->route('services.index')->with('success', 'Layanan dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(array_keys(Service::UNITS))],
            'price' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:0', 'max:60'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
