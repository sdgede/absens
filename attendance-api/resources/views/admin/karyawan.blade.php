<x-layouts.admin title="Karyawan" subtitle="{{ $totalKaryawan }} karyawan terdaftar">

    <x-slot:actions>
        <a href="{{ route('admin.karyawan.create') }}" class="btn-secondary" style="text-decoration:none;">+ Tambah
            Karyawan</a>
    </x-slot:actions>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.karyawan') }}"
        style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem; flex-wrap:wrap;">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder=" Cari nama / email / NIP..."
            class="input-field" style="width:14rem;">
        <select name="cabang_id" class="input-field" style="width:auto;">
            <option value="">Semua Cabang</option>
            @foreach ($cabangList as $c)
                <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="dept_id" class="input-field" style="width:auto;">
            <option value="">Semua Dept</option>
            @foreach ($deptList as $d)
                <option value="{{ $d->id }}" {{ request('dept_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
            @endforeach
        </select>
        <select name="role" class="input-field" style="width:auto;">
            <option value="">Semua Role</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Karyawan</option>
        </select>
        <button type="submit" class="btn-primary" style="width:auto; padding:0.5rem 1rem;">Terapkan</button>
        @if (request()->hasAny(['cari', 'cabang_id', 'dept_id', 'role']))
            <a href="{{ route('admin.karyawan') }}" class="btn-secondary" style="text-decoration:none;">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
            <thead>
                <tr>
                    <th class="th">Karyawan</th>
                    <th class="th">NIP</th>
                    <th class="th">Dept / Cabang</th>
                    <th class="th">Role</th>
                    <th class="th">Wajah</th>
                    <th class="th">Status</th>
                    <th class="th">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($karyawanList as $user)
                    <tr class="tr">
                        <td class="td">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                @if ($user->avatar_path)
                                    <img src="{{ asset('storage/' . $user->avatar_path) }}"
                                        style="width:1.75rem; height:1.75rem; border-radius:0.4375rem; object-fit:cover; flex-shrink:0;">
                                @else
                                    <div
                                        style="width:1.75rem; height:1.75rem; border-radius:0.4375rem; background-color:var(--color-indigo); display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700; color:white; flex-shrink:0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' '), 1, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:600; color:var(--color-dark-50);">{{ $user->name }}</div>
                                    <div style="font-size:0.625rem; color:var(--color-dark-300);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="td" style="font-family:var(--font-mono); font-size:0.625rem;">
                            {{ $user->employee_id ?? '—' }}</td>
                        <td class="td">
                            {{ $user->department->name ?? '—' }}<br>
                            <span
                                style="font-size:0.625rem; color:var(--color-dark-300);">{{ $user->branch->name ?? '—' }}</span>
                        </td>
                        <td class="td">
                            @foreach ($user->roles as $role)
                                <span
                                    style="font-size:0.625rem; font-weight:700; padding:0.1875rem 0.5rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-indigo) 15%, transparent); color:#818cf8;">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="td">
                            <span
                                style="font-size:0.5625rem; font-weight:700; padding:0.125rem 0.4375rem; border-radius:9999px; {{ $user->faceEmbedding ? 'background-color:color-mix(in srgb,var(--color-emerald) 10%,transparent);color:var(--color-emerald);' : 'background-color:color-mix(in srgb,var(--color-rose) 8%,transparent);color:var(--color-rose);' }}">
                                {{ $user->faceEmbedding ? '✓ Terdaftar' : '✗ Belum' }}
                            </span>
                        </td>
                        <td class="td">
                            <x-admin.badge :tipe="$user->is_active ? 'aktif' : 'nonaktif'" />
                        </td>
                        <td class="td">
                            <div style="display:flex; gap:0.375rem;">
                                <a href="{{ route('admin.karyawan.edit', $user->id) }}" class="btn-secondary"
                                    style="font-size:0.625rem; padding:0.1875rem 0.5rem; text-decoration:none;">Edit</a>
                                <form method="POST" action="{{ route('admin.karyawan.destroy', $user->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        style="font-size:0.625rem; padding:0.1875rem 0.5rem; border-radius:0.375rem; border:1px solid color-mix(in srgb, var(--color-rose) 30%, transparent); color:var(--color-rose); background:none; cursor:pointer;"
                                        onclick="return confirm('Nonaktifkan karyawan ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:2rem; text-align:center; color:var(--color-dark-300);">Belum ada
                            karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($karyawanList->hasPages())
            <div style="padding:0.75rem 1rem; border-top:1px solid var(--color-dark-500);">
                {{ $karyawanList->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-layouts.admin>
