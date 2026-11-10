@extends('layouts.admin')

@section('title', 'Kelola Akun Admin - Dashboard Admin UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kelola Akun Admin</h1>
            <p class="text-sm text-slate-700">
                Admin hanya dapat mengakses Ringkasan Tugas, Informasi Kampus, Pengingat Mahasiswa, dan Live Chat.
                Mahasiswa, Pengaturan, dan menu ini khusus Super Admin.
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-slate-800 self-start sm:self-auto shadow-sm">
            + Tambah Admin
        </a>
    </div>

    <div class="bg-white border border-slate-300 rounded-lg overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                        <th class="py-3.5 px-4 font-bold">Nama</th>
                        <th class="py-3.5 px-4 font-bold">Email</th>
                        <th class="py-3.5 px-4 font-bold">Peran</th>
                        <th class="py-3.5 px-4 font-bold">Dibuat</th>
                        <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $u->name }}
                                @if($u->id === auth()->id())
                                    <span class="ml-1 text-[11px] font-semibold text-slate-500">(Anda)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-800">{{ $u->email }}</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block text-xs font-bold px-2.5 py-1 rounded {{ $u->isSuperAdmin() ? 'bg-unpam-gold text-slate-950 border border-amber-300' : 'bg-slate-100 text-slate-800 border border-slate-300' }}">
                                    {{ $u->role_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">{{ $u->created_at?->format('d M Y') }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.users.edit', $u) }}"
                                       class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded border border-slate-300 bg-white text-xs font-semibold text-slate-800 hover:bg-slate-100">
                                        Ubah
                                    </a>
                                    @if(! $u->isSuperAdmin() && $u->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $u) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus akun admin {{ $u->name }}? Percakapan yang sedang ditanganinya kembali ke antrean.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="min-h-[38px] px-3 py-1.5 rounded bg-red-700 text-white text-xs font-semibold hover:bg-red-800">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

