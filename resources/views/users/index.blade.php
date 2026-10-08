@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Admin & Kasir</span>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-person-plus"></i> Tambah Pengguna</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td class="fw-semibold">
                        {{ $user->name }}
                        @if (auth()->user()->is($user)) <span class="badge text-bg-light">Anda</span> @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->isAdmin() ? 'text-bg-primary' : 'text-bg-info' }}">{{ \App\Models\User::ROLES[$user->role] }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        @unless (auth()->user()->is($user))
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="card-footer bg-white">{{ $users->links() }}</div>
    @endif
</div>
@endsection
